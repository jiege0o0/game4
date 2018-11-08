<?php 
	require_once($filePath."cache/base.php");
	do{
		$sql = "select * from ".getSQLTable('prop_shop')." where gameid='".$userData->gameid."'";
		$result = $conne->getRowsRst($sql);
		if($result && isSameDate($result['time']))
		{
			$returnData->shop = json_decode($result['shop']);
			break;
		}
		
		if($result)
		{
			$shopBase = json_decode($result['shop_base']);
		}

		//����shop����	
		$arr = array();
		$level = $userData->hang->level;
		foreach($prop_base as $key=>$value)
		{
			if($value['hanglevel'] && $value['hanglevel']<=$level)//��Դ����
			{
				$ownnum = $userData->getPropNum($key);
				$sellRate = 1000 + $value['hanglevel'];
				if($shopBase && $shopBase->{$key})
					$sellRate = $shopBase->{$key};
				$sellNum = 0;
				$sellType = 0;
				if($ownnum > 100)//����
				{
					if($userData->level < 6 || rand(0,15*$level)<$ownnum)//��
					{
						$total = floor($ownnum/100/2);
						$sellNum = rand(1,$total)*100;
						$sellType = 1;
					}
					else//��
					{
						$total = max(1,floor((20*$level - $ownnum)/100/2));
						$sellNum = rand(1,$total)*100;
						$sellType = 2;
					}
				}
				else//����
				{
					$sellNum = 100;
					$sellType = 2;
				}
				
				if($sellType == 1)
					$diamond = floor($sellRate * $sellNum);
				else
					$diamond = floor($sellRate * $sellNum * 1.5);
					
				array_push($arr,array(
					'id'=>$key,
					'num'=>$sellNum,
					'type'=>$sellType,
					'diamond'=>$diamond
				));
			}
		}
		
		$tempNum = floor($userData->level/3 + 3);
		if(count($arr) > $tempNum)//���ȡ6��
		{
			usort($arr,randomSortFun);
			$arr = array_slice($arr,0,$tempNum);
		}

		
		$returnData->shop = $arr;
		if($result)
			$sql = "update ".getSQLTable('prop_shop')." set shop='".json_encode($arr)."',time=".time()." where gameid='".$userData->gameid."'";
		else
			$sql = "insert into ".getSQLTable('prop_shop')."(gameid,shop,time) values('".$userData->gameid."','".json_encode($arr)."',".time().")";
		$conne->uidRst($sql);

		
		
	}while(false);
	
?> 