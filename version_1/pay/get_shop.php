<?php 
	require_once($filePath."cache/base.php");
	//��ǰ�ȼ��£�������λ���ļ��
	function getPropCD($clv,$slv,$tlv){
		$hourEarn = (floor(min($clv-$slv,100)/5) + 1)*(1 + $tlv*5/100);
		if($hourEarn <= 0)
			return 0;
		return 3600/$hourEarn;
	}


	do{
		$sql = "select * from ".getSQLTable('shop')." where gameid='".$userData->gameid."'";
		$result = $conne->getRowsRst($sql);
		if($result && isSameDate($result['time']))
		{
			$returnData->shop = json_decode($result['shop']);
			if($returnData->shop[0]->key)
			{
				break;
			}
		}

		//{id,num,diamond},
		//����shop����	
		$arr = array();
		$level = $userData->hang->level;
		$key=1;
		/*foreach($prop_base as $key=>$value)
		{
			if($value['hanglevel'] && $value['hanglevel']<=$level)//��Դ����
			{
				$propCD = getPropCD($level,$value['hanglevel'],$userData->getTecLevel(300 + $key));
				array_push($arr,array(
					'id'=>$key,
					'num'=>round(24*3600/$propCD),
					'times'=>0,
					'diamond'=>60
				));
			}
			else if($value['diamond'] && $key != 101)
			{
				//������...
				$num = rand(1,5);
				array_push($arr,array(
					'id'=>$key,
					'num'=>$num,
					'times'=>0,
					'diamond'=>$num * $value['diamond']
				));
			}
		}*/
		
		//��Դ����
		for($i=1;$i<=24;$i++)
		{
			array_push($arr,array(
					'id'=>'box_resource',
					'num'=>$i,
					'times'=>0,
					'key'=>$key++,
					'diamond'=>floor(pow($i,0.95) * 16)
				));
		}
		
		
	
				
		//Ǯ
		/*$coinLevel = 0;
		for($i=1;$i<=22;$i++)
		{
			$coinLevel += $userData->getTecLevel(300 + $i);
		}
		$coin = floor(3600/10*0.3*pow($level,0.8)*(1+$coinLevel*0.002)*1.1 + $userData->hourcoin*1.1)*12;
		array_push($arr,array(
					'id'=>'coin',
					'num'=>$coin,
					'times'=>0,
					'diamond'=>60
				));*/
				
				
		//������
		if($level >= 10 && rand(1,50) == 1)
		{
			$need = 88;
			array_push($arr,array(
				'id'=>101,
				'num'=>1,
				'times'=>0,
				'key'=>$key++,
				'level'=>$nextLevel,
				'diamond'=>$need
			));
		}
		
		//����1������
		$tecLevel = $userData->level;
		$skillAble = false;
		foreach($skill_base as $key=>$value)
		{
			if($value['level'] <= $tecLevel && $userData->getSkill($key) < 999)//@skill
			{
				$skillAble = true;
				break;
			}
		}
		
		$heroAble = false;
		if($level >= 50)
		{
			foreach($monster_base as $key=>$value)
			{
				if($value['id'] > 100 && $value['id'] < 130 && $value['level']-1000 <= $tecLevel && $userData->getMaxHeroLevel($key) < 5)//@hero
				{
					$heroAble = true;
					break;
				}
			}
		}
		
		
		

		$tempNum = 5;
		if($skillAble)
			$tempNum --;
		if($heroAble)
			$tempNum --;
		
		if(count($arr) > $tempNum)//���ȡ6��
		{
			usort($arr,randomSortFun);
			$arr = array_slice($arr,0,$tempNum);
		}
		//����(����)
		$num = rand(10,20);
		array_push($arr,array(
					'id'=>'energy',
					'num'=>$num,
					'times'=>0,
					'diamond'=>$num*5
				));
				
	
		
		if($skillAble)
		{
			$num = rand(1,3);
			array_push($arr,array(
					'id'=>'box_skill',
					'num'=>$num,
					'times'=>0,
					'key'=>$key++,
					'diamond'=>floor(pow($num,0.95) * 200)
				));
		}
		
		if($heroAble)
		{
			$num = rand(1,3);
			array_push($arr,array(
				'id'=>'box_hero',
				'num'=>$num,
				'times'=>0,
				'key'=>$key++,
				'diamond'=>floor(pow($num,0.95) * 250)
			));
		}
		
		
		
		
		$returnData->shop = $arr;
		if($result)
			$sql = "update ".getSQLTable('shop')." set shop='".json_encode($arr)."',time=".time()." where gameid='".$userData->gameid."'";
		else
			$sql = "insert into ".getSQLTable('shop')."(gameid,shop,time) values('".$userData->gameid."','".json_encode($arr)."',".time().")";
		$conne->uidRst($sql);

		
		
	}while(false);
	
?> 