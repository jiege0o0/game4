<?php 
	//ȡ�����б�
	$time = time() - 24*3600;
	$now = time();
	do{
		$sql = "select * from ".getSQLTable('view')." where gameid='".$userData->gameid."'";
		$result = $conne->getRowsRst($sql);
		if(!$result)	
		{
			$returnData->list = new stdClass();
			break;
		}
		
		//ȡҪ���µ�
		$needRenew = array();
		$obj = json_decode($result['viewlist']);
		foreach($obj as $key=>$value)
		{
			if($value->time < $time)
				array_push($needRenew,$key);
		}
		
		if(count($needRenew))//���º�����Ϣ
		{
			$sql = "select gameid,nick,head,tec_force,hourcoin,type,level from ".getSQLTable('user_data')." where gameid in('".join($needRenew,"','")."')";
			$result2 = $conne->getRowsArray($sql);
			
			foreach($result2 as $key=>$value)
			{
				$otherid = $value['gameid'];
				$obj->{$otherid} = new stdClass();
				$obj->{$otherid}->nick = base64_encode($value['nick']);
				$obj->{$otherid}->head = $value['head'];
				$obj->{$otherid}->tec_force = $value['tec_force'];
				$obj->{$otherid}->hourcoin = $value['hourcoin'];
				$obj->{$otherid}->type = $value['type'];
				$obj->{$otherid}->level = $value['level'];
				$obj->{$otherid}->time = $now;
			}
			$sql = "update ".getSQLTable('view')." set viewlist='".json_encode($obj)."' where gameid='".$userData->gameid."'";
			$conne->uidRst($sql);
		}
		
		//��������
		$returnData->list = $obj;	
	}
	while(false);	
	
?> 