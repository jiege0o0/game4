<?php
	date_default_timezone_set("PRC"); 
	//��ӡDebug���ͻ���
	function debug($str){
		global $debugC,$debugArr;
		if(!$debugC)
			return;
		array_push($debugArr,$str);
	}
	
	$testTime = array();
	function startTestTime($key){
		global $testTime;
		if(!$testTime[$key])
		{
			$testTime[$key] = array('start'=>0,'total'=>0,'count'=>0);
		}
		$testTime[$key]['start'] = microtime(true);
	}
	function stopTestTime($key){
		global $testTime;
		if(!$testTime[$key])
		{
			return;
		}
		
		$testTime[$key]['total'] += microtime(true) - $testTime[$key]['start'];
		$testTime[$key]['count'] ++;
	}
	
	//ֱ���������ҳ
	function trace($v,$isSimple=false){
		//return;

		echo '<br/>';
		if($isSimple)
			echo ($v);
		else
			echo json_encode($v);
			// echo json_encode(var_dump($v);
		echo '<br/>';
	}
	
	//д������־
	function errorLog($str){
		global $dataFilePath,$serverID;
		$file  = $dataFilePath.'log/server'.$serverID.'/log'.date('Ymd', time()).'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
		file_put_contents($file, date('h:i:sa', time()).'|'.time()." : ".$str.PHP_EOL,FILE_APPEND);
	}
	
	//��ҵ���Ϣ��־
	function userLog($gameid,$str){
		global $dataFilePath,$serverID;
		$file  = $dataFilePath.'userlog/log_'.$gameid."#".date('Ymd', time()).'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
		file_put_contents($file, date('Y-m-d h:i:sa', time())." : ".$str.PHP_EOL,FILE_APPEND);
	}
	//��ҵ���Ϊ��־
	function userLog2($gameid,$str){
		global $dataFilePath,$serverID;
		$file  = $dataFilePath.'userlog2/'.$gameid.'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
		file_put_contents($file, date('Y-m-d h:i:sa', time())." : ".$str.PHP_EOL,FILE_APPEND);
	}
	function clientLog($str){
		global $dataFilePath,$serverID;
		$file  = $dataFilePath.'log/client/log'.date('Ymd', time()).'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
		file_put_contents($file, date('h:i:sa', time()).'|'.time()." : ".$str.PHP_EOL,FILE_APPEND);
	}
	//д������־
	function payLog($str){
		global $dataFilePath,$serverID;
		$file  = $dataFilePath.'log/server'.$serverID.'/pay_log'.date('Ymd', time()).'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
		file_put_contents($file, date('h:i:sa', time())." : ".$str.PHP_EOL,FILE_APPEND);
	}
	
	//���ص��ͻ��˵����ݴ���
	function sendToClient($returnData){
		$returnData->server_time = time();
		echo json_encode($returnData);
	}
	
	//�����ֱ��1λ���ַ�(���ֵΪ9+26+26 = 61)
	function numToStr($num){
		if(!$num)
			return '0';
		$str = '';
		while($num)
		{
			$str = _numToStr($num%62).$str;
			$num = (int)($num/62);
		}
		return $str;
	}
	
	function _numToStr($num){
		if($num<10)
			return chr(48 + $num);
		$num -= 10;
		if($num<26)	
			return chr(65 + $num);
		$num -= 26;
		return chr(97 + $num);
	}
	
	//��1λ���ַ��������(���ֵΪ9+26+26 = 61)
	function strToNum($str){
		$num = 0;
		$arr = str_split($str);
		$len = count($arr);
		for($i=0;$i<$len;$i++)
		{
			$num += pow(62,$len-$i-1)*_strToNum($arr[$i]);
		}
		return $num;
	}
	
	function _strToNum($str){
		$num = ord($str);
		if($num >= 97)
			return $num - 97 +26 + 10;
		if($num >= 65)
			return $num - 65 + 10;
		return $num - 48;
	}
	
	//cdKey���============================
	function testCDKey($id,$key){
		
		$time = (int)substr($key,16) + 1453027182;
		if(abs($time - time())>3600)
		{
			return false;
		}
		return getCDKey($id,$time) == $key;
	}
	function getCDKey($id,$time){
		return substr(md5('hange0o0_'.$time.$id),16).($time - 1453027182);
	}
	
	//�������
	function randomSortFun($a,$b){
		return lcg_value()>0.5?1:-1;
	}
	
	//ʱ�����============================
	function isSameDate($t1,$t2=null){
		if(!$t2)
			$t2 = time();
		return date('Ymd', $t1) == date('Ymd', $t2);
	}
	
	//����0��
	function todayZero(){
		//��ȡ��������
		$y = date("Y");
		 
		//��ȡ������·�
		$m = date("m");
		 
		//��ȡ����ĺ���
		$d = date("d");
 
		//�����쿪ʼ��������ʱ���룬ת����unixʱ���(��ʼʾ����2015-10-12 00:00:00)
		return mktime(0,0,0,$m,$d,$y);
	}

?>