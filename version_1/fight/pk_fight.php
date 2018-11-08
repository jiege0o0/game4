<?php 
$id=$msg->id;
require_once($filePath."pk/pk_tool.php");


do{		
	if(!$userData->testEnergy(1))//没体力
	{
		$returnData -> fail = 1;
		break;
	}
	
	$sql = "select * from ".getSQLTable('fight')." where gameid='".$userData->gameid."'";
	$result = $conne->getRowsRst($sql);
	$info = json_decode($result['info']);
	$list = $info->card;
	$hero = $info->hero;
	
	//产生敌人
	if(!$info->enemy)
	{
		require_once($filePath."cache/base.php");
		//计算关卡战力
		$force= $userData->tec_force + max(round($info->index/120*$userData->tec_force),2*$info->index);
		$enemy = array();
		$enemy['force'] = $force;
		
		$tecLevel = $userData->level;
		$skillArr = array();
		$heroArr = array();
		foreach($monster_base as $key=>$value)
		{
			if($value['level'] <= $tecLevel)
			{
				array_push($skillArr,$value);
			}
			else if($value['id'] > 100 && $value['id'] < 130 && $value['level']-1000 <= $tecLevel)
			{
				array_push($heroArr,$value);
			}
		}
		shuffle($skillArr);
		$skillArr = array_slice($skillArr,0,4);
		usort($skillArr,"my_fight_sort");
		array_splice($skillArr,rand(2,3),1);
		$arr = array();
		array_push($arr,$skillArr[0]['id']);
		array_push($arr,$skillArr[1]['id']);
		array_push($arr,$skillArr[2]['id']);
		shuffle($arr);
		$len = $info->index+3;//*ceil($userData->maxCardNum()*0.05);//$userData->maxCardNum() + 3;
		for($i=0;$i<$len;$i++)
		{
			array_push($arr,$skillArr[rand(0,2)]['id']);
		}
		
		$enemy['list'] = join(",",$arr);
		$addMp = floor($info->index/4);
		if($addMp)
			$enemy['list'] = '40'.$addMp.','.$enemy['list'] ;
		

		
		
		$enemy['type'] = $skillArr[0]['type'];
		$enemy['hp'] = $userData->getHp();
		if($enemy['hp'] < $info->index*0.7)
			$enemy['hp'] = ceil($info->index*0.7);
		
		if($info->index > 3 && $userData->hang->level >= 50)//加入英雄
		{
			$heroLevel = max(1,min(5,floor(pow($userData->hang->level/100,0.8))));
			shuffle($heroArr);
			$skillArr = array_slice($heroArr,0,min(5,$info->index - 5));
			foreach($skillArr as $key=>$value)
			{
				$skillArr[$key] = $value['id'].'|'.$heroLevel;
			}
			$enemy['hero'] = join(",",$skillArr);
		}
		
		
		$player = createNpcPlayer(2,2,$enemy);
		$nick = '远征'.($info->index + 1);
		$player->nick = base64_encode($nick);
		
		$info->enemy = $player;
		
		$sql = "update ".getSQLTable('fight')." set info='".json_encode($info)."' where gameid='".$userData->gameid."'";
		$conne->uidRst($sql);
	}
	
	
	$pkData = new stdClass();
	$pkData->check = true;
	$pkData->seed = time();
	$pkData->players = array();
	array_push($pkData->players,createUserPlayer(1,1,$userData,$list,$hero));
	array_push($pkData->players,$info->enemy);

	
	
	$returnData -> pkdata = $pkData;
	$userData->addEnergy(-1);
	$userData->pk_common->pktype = 'fight';
	$userData->pk_common->pkdata = $pkData;
	$userData->pk_common->level = $info->index;
	$userData->pk_common->time = time();
	$userData->setChangeKey('pk_common');
	
	

}while(false);

function my_fight_sort($a,$b)
{
	if ($a['cost'] < $b['cost'])
		return -1;
	if ($a['cost'] > $b['cost'])
		return 1;
	return 0;
}

?> 