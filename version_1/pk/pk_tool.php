<?php 

	//得到玩家对战的数据结构
	function getMyPKData(){
		global $userData,$monster_base;
		
		
		$list = array(); 
		foreach($monster_base as $key=>$value)
		{
			if($value['level']<=$userData->level)
			{
				array_push($list,$key);
				array_push($list,$key);
				array_push($list,$key);
			}
		}
		shuffle($list);
		if(count($list) > 80)
			$list = array_slice($list,0,80);
		
		$data = new stdClass();
		$data->card = $userData->card;
		$data->list = $list;
		$data->head = $userData->head;
		$data->nick = base64_encode($userData->nick);
		return $data;
		
	}
	
	
	function recordPKData($type,$str,$add=''){
		global $dataFilePath,$userData;
		$file  = $dataFilePath.'log/use_'.$type.'.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
		file_put_contents($file, $userData->level.'|'.$str.'|'.$add.PHP_EOL,FILE_APPEND);
	}

    function getGroupMp($group){
		global $monster_base,$skill_base;
        $mp = 0;
		$len = count($group);
        for($j=0;$j<$len;$j++)
        {
            $id = floor($group[$j]);
            if($id < 0)
            {
                $mp += -$id;
                continue;
            }
			else if($id < 200)//@skillID
				$mp += $monster_base['cost'];
			else
				$mp += $skill_base['cost'];
        }
        return $mp;
    }

    //到这个MP量的时间
    function getMPTime($mp){
        //30+40+60*3 = 250
        $step0 = 15;//初始值
        $step1 = 30;//第一分钟产量
        $step2 = 40;//第二分钟产量
        $step3 = 60;//之后每分钟的产量

        if($mp <= $step0)
            return 0;
        $mp -= $step0;

        if($mp <= $step1)
            return $mp/$step1 * 60*1000;

        $mp -= $step1;
        if($mp <= $step2 )
            return $mp/$step2 * 60*1000 + 60*1000;

        $mp -= $step2;
        return $mp/$step3 * 60*1000 + 60*1000*2;

    }
	
	function getMPList(){
		$mpList = array(0);
		$max = 250;
		for($i=1;$i<=$max;$i++)
		{
			$mpList[$i] = getMPTime($i);
		}
		return $mpList; 
	}
	
	function addMPTime(&$arr,$time,$mp){
		$len = count($arr);
		for($i=0;$i<$len;$i++)
		{
			if($arr[$i] >= $time)
			{
				while($mp>0)
				{
					array_splice($arr,$i,0,$time);
					$mp--;
				}
				break;
			}
		}
    }	
	
	function deleteSkillCard($card){
		global $userData,$returnData;
		$arr = explode(",",$card);
		$len = count($arr);
		$orginSkillNum = new stdClass();
		for($i=0;$i<$len;$i++)
		{
			$skillID = (int)$arr[$i];
			if($skillID >= 200)//@skillID
			{
				$num = $userData->getSkill($skillID);
				if(!$orginSkillNum->{$skillID})
					$orginSkillNum->{$skillID} = $num;
				if($num>0)
				{
					if($num < 999)//@skillID  
						$userData->addSkill($skillID,-1);
				}
				else
				{
					$returnData->sync_skill = new stdClass();
					$returnData->sync_skill->{$skillID} = $orginSkillNum->{$skillID};
					return false;
				}
			}
		}
		return true;
    }
	
	//测试技能卡数量
	function testSkillCard($card){
		global $userData,$returnData;
		$arr = explode(",",$card);
		$len = count($arr);
		$useSkillNum = new stdClass();
		for($i=0;$i<$len;$i++)
		{
			$skillID = (int)$arr[$i];
			if($skillID >= 200)//@skillID
			{
				if(!$useSkillNum->{$skillID})
					$useSkillNum->{$skillID} = 0;
				$num = $userData->getSkill($skillID) - $useSkillNum->{$skillID};
				
				if($num>0)
				{
					$useSkillNum->{$skillID} ++;
				}
				else
				{
					$returnData->sync_skill->{$skillID} = $userData->getSkill($skillID);
					return false;
				}
			}
		}
		return true;
    }
	
	//返还技能卡牌
	function backSkillCard($list){
		global $userData;
		foreach($list as $key=>$skillID)
		{
			$num = $userData->getSkill($skillID);
			if($num < 999)
				$userData->addSkill($skillID,1);
		}
	}
	
	function testPVPServerKey($key,$playerData){
		// md5.incode(this.gameid + '_' + this.pkData.card + '_hange0o0_server1')
		
		return $key == substr(md5($playerData->gameid.'_'.$playerData->card.'_hange0o0_server'),-8);
	}
	
	
	//得到用于PK的数据结构 step#id,step#id,
	function getUserPKData($list,$player,$cd,$key,$seed){
		global $monster_base,$skill_base;
		
		
		$result = new stdClass();
		$result->list = array();
		$result->hp = $player->hp;
		$result->type = $player->type;
		$result->force = $player->force;
		$result->id = $player->id;
		$result->team = $player->team;
		$result->isauto = !$player->card;
		$result->skill = array();
		
		
		$card = $player->card;
		if(!$card)
			$card = $player->autolist;
		if($player->hero)
		{
			$hero = explode(",",$player->hero);
			foreach($hero as $k=>$value)
			{
				$temp = explode("|",$value);
				$hero[$k] = $temp[0];
			}
		}
		else
			$hero = array();
		$orgin = explode(",",$card);
			
        $serverKey = substr(md5($cd.$card.$list.$seed),-8);
		if($serverKey != $key)//校验不通过
		{
			$result->fail = 103;
			debug($seed);
			debug($serverKey);
			return $result;
		}	

		
		
		
		$mpList = getMPList();
		$stepCD = 50;
		$mpCost = 0;
		if($list)
		{
			$list = explode(",",$list);
			$len = count($list);
			for($i=0;$i<$len;$i++)
			{
				$group = explode("#",$list[$i]);
				$time = $group[0]*$stepCD; 
				$id = $group[1];
				if($id < 200)//@skillID
				{
					$mpCost += $monster_base[$id]['cost'];
				}
				else
				{
					$mpCost += $skill_base[$id]['cost'];
					if($skill_base[$id]['sv4'] == -10001)
					{
						addMPTime($mpList,$time + 3000 + $skill_base[$id]['cd']*1000,$skill_base[$id]['sv1']+ $skill_base[$id]['cost']);
					}
				}

				if($mpList[$mpCost] > $time)//MP不够
				{
					$result->fail = 101;
					break;
				}	
				if($id < 500 && !$player->isRandom)//非报警卡
				{
					$isHero = $id >100 && $id < 130;
					if($isHero)
					{
						if($hero[0] != $id)//使用了不合法的卡
						{
						$result->aaa = 1;
							$result->fail = 104;
							debug($id);
							debug($hero);
							break;
						}
						array_shift($hero);
					}
					else 
					{
						$index = array_search($id, $orgin);
						$isOK = $index === 0 || ($index>0 && $index <6);//只可以用前6张
						if(!$isOK)//使用了不合法的卡
						{
							$result->fail = 102;
							break;
						}
						array_splice($orgin,$index,1);
					}

				}
				
				array_push($result->list,array(
					"mid"=>$id,
					"time"=>$time,
					"id"=>$i,
					));
			}
		}
		
		
		//记录未使用的技能卡
		$len = count($orgin);
		for($i=0;$i<$len;$i++)
		{
			$id = $orgin[$i];
			if($id > 200)//@skillID
				array_push($result->skill,$id);
		}
		return $result;
	}
	
	
?> 