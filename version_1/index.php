<?php
	header('Access-Control-Allow-Origin:*');
	ini_set('date.timezone','Asia/Shanghai');

	// error_reporting(1|2|4|E_COMPILE_ERROR);
	error_reporting(E_ALL^(E_NOTICE|8192));
	ini_set('display_errors', '1');


	set_error_handler("customError");
	register_shutdown_function('fatalErrorHandler');
	require_once($filePath."_config_version.php");
	require_once($filePath."tool/tool.php");
	
	
	
	$head = $_POST['head'];
	$msg = json_decode($_POST['msg']);
	$debugC = $_POST['debug_client'];//客户端发起的DEBUG
	
	global $returnData,$mySendData;
	
	$returnData = new stdClass();
	$mySendData = new stdClass();
	$mySendData->head = $head;
	$mySendData->msg = $returnData;
	$runSqlArr = array();
	//开启了会倒计时切服务器
	// $returnData-> close_version = $game_version;
	// $returnData-> close_time = 1532569624+10*60;
	
	if($debugC){
		$startT = microtime(true);
		$debugArr = array();
	}
	
	
	
	//error1:版本号,2登陆状态,3出错,4写用户失败
	
	 function fatalErrorHandler(){
	 global $_POST,$mySendData,$debugC,$debugArr;
             $e = error_get_last();
             switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                     customError($e['type'],$e['message'],$e['file'],$e['line']);
                     break;         
            }
    }
	
	function  customError($errno, $errstr, $errfile, $errline)
	{ 
		global $_POST,$mySendData,$debugC,$debugArr;
		if($errno == 8192)
			return;
		if($errno == 8)
			return;
		errorLog($_POST['msg_index']."#".$_POST['head'].$_POST['msg']."=>code:".$errstr."=>code:".$errno.'=>file:'.$errfile."=>line:".$errline);//.$errstr."=>code:".$errno'=>file:'.$errfile."=>line:".$errline
		if($debugC)
			echo "=>code:".$errstr."=>code:".$errno.'=>file:'.$errfile."=>line:".$errline;
		sendErroClient();	
	}
	
	
	function sendErroClient(){
		global $_POST,$mySendData,$debugC,$debugArr;
		$mySendData = new stdClass();
		$mySendData->head = $_POST['head'];
		$mySendData->error = 3;
		$mySendData->debug = $debugArr;
		$mySendData->key = 'sendErroClient';
		sendToClient($mySendData);		
	}

	try{	
		do{
			if($returnData-> close_version && $returnData-> close_version <= $game_version && time() - $returnData-> close_time > 0)
			{
				$mySendData->error = 1;
				break;
			}
			// $mySendData->error = 99;
			// $mySendData->error_str = '15时间改回正常';
			// break;
			//测试版本号
			if($_POST['version'] < $game_version){
				$mySendData->error = 1;
				break;
			}
			if($_POST['version'] > $game_version){
				$mySendData->error = 5;
				$mySendData->version = $game_version;
				break;
			}
			
			//测试登陆状态,并设定用户数据
			if(isset($msg->landid) && isset($msg->gameid))
			{
				require_once($filePath."tool/conn.php");
				require_once($filePath."object/game_user.php");
				$sql = "select * from ".getSQLTable('user_data')." where gameid='".$msg->gameid."' and land_key=".$msg->landid;
				$userData = $conne->getRowsRst($sql);
				if(!$userData)//登录失效
				{
					$mySendData->error = 2;
					break;
				}
				$userData = new GameUser($userData);
				
				
				//垮天处理，登录接口不进这(没landid)
				require_once($filePath."sys/pass_day.php");
			}	
			
			//登录的特殊处理
			if($head == 'sys.login_server')// || $head == 'sys.login_server'
			{
				if($msg->h5)
				{	
					$loginOK = false;
					require_once($filePath."platform/login_".$msg->h5.".php");
					if(!$loginOK)
					{
						$returnData->fail = 1;
						break;
					}
				}
				else if($msg->cdkey != 'hange0o0' && !testCDKey($msg->id,$msg->cdkey))
				{
					$returnData->fail = 1;
						break;
				}		
			}
			else if($head == 'sys.client_error')
			{
				clientLog($msg->msg);
				break;
			}
			$headArr = explode(".",$head);
			$phpFilePath = $filePath.$headArr[0].'/'.$headArr[1].".php";
			if(is_file($phpFilePath))
			{
				require($phpFilePath);
			}
			else
			{
				$mySendData->result = 'fail';
				$mySendData->msg = 'fun not found:'.$head;
			}		
		}while(false);
	}
	catch(Exception $e){
		errorLog($_POST['msg_index']."#".$_POST['head'].$_POST['msg'].$e->getMessage()."=>code:".$e->getCode().'=>file:'.$e->getFile()."=>line:".$e->getLine());
		$mySendData->error = 3;
		if($debugC)
			echo $e->__toString(); 			
	}
	
	if(!$returnData->stopLog)
	{
		if($returnData->fail)
		{
			errorLog($_POST['msg_index']."#".$_POST['head'].$_POST['msg'].'__'.json_encode($returnData));
		}
		else if(isset($msg->landid) && isset($msg->gameid))
		{
			userLog($msg->gameid,$_POST['msg_index']."#".$_POST['head'].$_POST['msg'].'__'.json_encode($returnData));
		}
	}
	
	
	
	
	
	
	
	unset($returnData->stopLog);	
	if(isset($msg->landid) && isset($msg->gameid) && !$returnData->fail && !$mySendData->error)
		$userData->write2DB();
		
	if(!$returnData->fail && !$mySendData->error)
	{
		foreach($runSqlArr as $key=>$value)
		{
			$conne->uidRst($value);
		}
	}
		
	if($debugC)
	{
		$mySendData->runtime = microtime(true) - $startT;
		$mySendData->debug = $debugArr;
	}
	
	if(!$gameid)
		$gameid = $msg->gameid;
	if($gameid && $_POST['head'] != 'sys.login_server2' && $mySendData->error != 2)
	{
		if($userData && time()-$userData->regtime < 7*24*3600)//7天内才记
		{
			if($returnData->fail)
				userLog2($gameid,$_POST['head']." |fail:".$returnData->fail);
			else if($mySendData->error)
				userLog2($gameid,$_POST['head']." |error:".$mySendData->error);
			else
				userLog2($gameid,$_POST['head']);
		}
		
	}
	
	sendToClient($mySendData);
?>