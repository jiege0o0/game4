<?php 
	require_once($filePath."cache/base.php");
	$pvpEndTime = 1535904000;
	$pvpCD = 24*3600*28;
	$round = ceil(max(0,time() - $pvpEndTime) /$pvpCD)  + 1;
	$roundChange = false;
	do{
		// $sql = "select * from ".getSQLTable('pvp_offline')." where gameid='".$userData->gameid."'";
		// $returnData->offline = $conne->getRowsRst($sql);
		// $conne->close_rst();
		
		
		$sql = "select * from ".getSQLTable('pvp')." where gameid='".$userData->gameid."'";
		$result = $conne->getRowsRst($sql);
		if($result)
		{
			$task = json_decode($result['task']);
			$online = json_decode($result['online']);
			$offline = json_decode($result['offline']);
			
				
				
			if(isSameDate($result['time']))
			{
				$returnData->task = $task;
				$returnData->online = $online;
				$returnData->offline = $offline;
				break;
			}
			
			if(!$online->round)//�ݴ������
			{
				$online->round = $round;
				$offline->round = $round;
				$roundChange = true;
			}
			
			if($online->round != $round)//��������
			{	
				$online->round = $round;
				$offline->round = $round;
				
				$online->award = $online->score;
				if($online->score && $online->score > 3000)
					$online->score = 3000 + ceil(($online->score - 3000)/2);
					
				$offline->award = $offline->score;
				if($offline->score && $offline->score > 3000)
					$offline->score = 3000 + ceil(($offline->score - 3000)/2);
					
				require_once($filePath."pvp/pvp_round_change.php");
				$roundChange = true;
			}
			
		}
		else
		{
			$task = new stdClass();
			$online = new stdClass();
			$offline = new stdClass();
			$task->total = 0;
			$online->round = $round;
			$offline->round = $round;
			
			require_once($filePath."pvp/pvp_round_change.php");
		}

		$task->list = array();
		$list = array(1,2,3,4,5,3);
		shuffle($list);
		array_unshift($list,0);
		for($i=0;$i<4;$i++)
		{
			$type = $list[$i];
			$oo = new stdClass();
			$oo->type = $type;
			$oo->current = 0;
			switch($type)
			{
				case 0://�����������
					$oo->num = 3;
					$rd = rand(1,100);
					if($rd <= 20)
						$oo->hero = 1;
					else if($rd <= 50)
						$oo->skill = 1;
					else 
						$oo->box = 3;
					break;
				case 1://����5������
					$oo->num = rand(5,10);
					$oo->box = $oo->num > 7.5?2:1;
					break;
				case 2://���3ʤ
					$oo->num = rand(3,5);
					$oo->box = $oo->num > 4?2:1;
					break;
				case 3://ʹ��XX N��
					$temp = array();
					foreach($monster_base as $key=>$value)
					{
						$skillID = (int)$key;
						if($skillID < 200 && $value['level'] == 0 || in_array($skillID,$userData->card->monster,true))//@skill
							array_push($temp,$skillID);
					}
					debug($temp);
					$oo->mid = $temp[rand(0,count($temp)-1)];
					$oo->num = rand(9,15);
					$oo->box = $oo->num > 12?2:1;
					break;
				case 4://10�Ѽ�����
					$oo->num = rand(15,25);
					$oo->box = $oo->num > 20?2:1;
					break;
				case 5://5�Ѽ�����
					$oo->num = rand(30,50);
					$oo->box = $oo->num > 40?2:1;
					break;
			}
			array_push($task->list,$oo);
		}

		$returnData->task = $task;
		$returnData->online = $online;
		$returnData->offline = $offline;
		if($result)
		{
			$sql = "update ".getSQLTable('pvp')." set task='".json_encode($task)."',time=".time()." where gameid='".$userData->gameid."'";
			if($roundChange)
				$sql = "update ".getSQLTable('pvp')." set task='".json_encode($task)."',online='".json_encode($online)."',offline='".json_encode($offline)."',time=".time()." where gameid='".$userData->gameid."'";
		}
		else
		{	
			$sql = "insert into ".getSQLTable('pvp')."(gameid,task,online,offline,time) values('".$userData->gameid."','".json_encode($task)."','".json_encode($online)."','".json_encode($offline)."',".time().")";
		}
		
		$conne->uidRst($sql);
		
	}while(false);
	
?> 