<?php 
require_once($filePath."tool/conn.php");
$nick=$msg->nick;
$type=$msg->type;
$gameid = $serverID.'_'.$msg->id;

do{
	//这个号在这个服有没有注册过
	$sql = "select nick from ".getSQLTable('user_data')." where gameid='".$gameid."'";
	$result = $conne->getRowsRst($sql);
	if($result)
	{
		$returnData -> fail = 2;
		$returnData ->nick = $result['nick'];
		// addToUser($msg->id,$serverID);
		break;
	}

	//有没有重名
	$sql = "select * from ".getSQLTable('user_data')." where nick='".$nick."'";
	if($conne->getRowsNum($sql))
	{
		$returnData -> fail = 3;
		$returnData -> stopLog = true;
		break;
	}

	//可以注册
	$time = time();
	$baseHead = array(2,3,6,31,41,64,65,76);
	$head = $baseHead[rand(0,7)];
	$sql = "insert into ".getSQLTable('user_data')."(gameid,nick,head,last_land,regtime,land_key) values('".$gameid."','".$nick."','".$head."',".$time.",".$time.",'".$time."')";
	$num = $conne->uidRst($sql);
	if($num == 1){//注册成功
		$returnData->data = 'success';
		// $sql = "insert into ".getSQLTable('user_open')."(gameid,masterstep) values('".$gameid."','0|0')";
		// $conne->uidRst($sql);
		// addToUser($msg->id,$serverID);
	}
	else
	{
		$returnData -> fail = 4;
		errorLog('register_server:'.json_encode($msg));
	}

}while(false)


?> 