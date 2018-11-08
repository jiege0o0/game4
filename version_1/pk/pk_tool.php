<?php 

	//�õ���Ҷ�ս�����ݽṹ
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
		$file  = $dataFilePath.'log/use_'.$type.'.txt';//Ҫд���ļ����ļ����������������ļ�����������ļ������ڣ����ᴴ��һ��
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

    //�����MP����ʱ��
    function getMPTime($mp){
        //30+40+60*3 = 250
        $step0 = 15;//��ʼֵ
        $step1 = 30;//��һ���Ӳ���
        $step2 = 40;//�ڶ����Ӳ���
        $step3 = 60;//֮��ÿ���ӵĲ���

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
	
	//���Լ��ܿ�����
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
	
	//�������ܿ���
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
	
	
	//�õ�����PK�����ݽṹ step#id,step#id,
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
		if($serverKey != $key)//У�鲻ͨ��
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

				if($mpList[$mpCost] > $time)//MP����
				{
					$result->fail = 101;
					break;
				}	
				if($id < 500 && !$player->isRandom)//�Ǳ�����
				{
					$isHero = $id >100 && $id < 130;
					if($isHero)
					{
						if($hero[0] != $id)//ʹ���˲��Ϸ��Ŀ�
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
						$isOK = $index === 0 || ($index>0 && $index <6);//ֻ������ǰ6��
						if(!$isOK)//ʹ���˲��Ϸ��Ŀ�
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
		
		
		//��¼δʹ�õļ��ܿ�
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