<?php 
	require_once($filePath."active/current_active.php");
	do{
		if(!$currentActive || $currentActive['type'] != 4)
		{
			$returnData->fail = 1;
			break;
		}
		
		$sql = "select * from ".getSQLTable('choose')." where gameid='".$userData->gameid."'";
		$result = $conne->getRowsRst($sql);
		debug($result['time'].'_'.$currentActive['start']);
		if($result && $result['time'] > $currentActive['start'])
		{
			$returnData->info = json_decode($result['info']);
			break;
		}
		
		$awardNum = 1;
		require($filePath."active/init_award.php");
		require($filePath."choosecard/random_choosecard.php");
		
		$info = new stdClass();
		$info->index = 0;//��ǰ����
		$info->num = 5;//��Ѵ���
		$info->win_award = $award;//����
		$info->final_award = $currentActive['faward'] ;//����
		$info->choose = $skillArr;
		$info->cardlist = '';
		$returnData->info = $info;
		
		if($result)
			$sql = "update ".getSQLTable('choose')." set info='".json_encode($info)."',time=".time()." where gameid='".$userData->gameid."'";
		else
			$sql = "insert into ".getSQLTable('choose')."(gameid,info,time) values('".$userData->gameid."','".json_encode($info)."',".time().")";
		$conne->uidRst($sql);

		
	}while(false);
	
?> 