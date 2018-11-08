<?php	
class GameUser{

	public $gameid;
	public $uid;
	public $nick;
	public $head;
	public $level;
	public $tec;
	public $last_land;
	public $land_key;
	public $coin;
	public $prop;
	public $diamond;
	public $rmb;
	public $active;
	public $card;
	public $use_card;
	public $pk_common;
	public $opendata;
	public $regtime;
	
	
	private $changeKey = array();

	//初始化类
	function __construct($data,$isOther=null){
		$this->gameid = $data['gameid'];
		$this->uid = $data['uid'];
		$this->nick = $data['nick'];
		$this->head = $data['head'];
		$this->level = (int)$data['level'];
		$this->coin = (int)$data['coin'];
		$this->last_land = $data['last_land'];
		$this->pk_common = $this->decode($data['pk_common'],'{"pktype":"","pkdata":null}');
		$this->tec = $this->decode($data['tec'],'{}');
		$this->pvp = $this->decode($data['pvp'],'{"score":0,"cwin":0,"pknum":0,"win":0}');
		$this->use_card = $this->decode($data['use_card'],'[]');
		
		
		if($isOther)
			return;
		$this->rmb = (int)$data['rmb'];
		$this->regtime = (int)$data['regtime'];
		$this->diamond = (int)$data['diamond'];
		$this->land_key = $data['land_key'];
		$this->prop = $this->decode($data['prop']);
		$this->opendata = $this->decode($data['opendata'],'{}');
		$this->active = $this->decode($data['active'],'{"task":{}}');//活动
		$this->card = $this->decode($data['card'],'{}');
		
	}
	
	function decode($v,$default = null){
		if(!$v)
		{
			if($default)
				$v = $default;
			else
				$v = '{}';
		}
		return json_decode($v);
	}
	
	function addTaskStat($key){
		global $returnData;
		if(!$this->active->task->stat)
			$this->active->task->stat = new stdClass();
		if(!$this->active->task->stat->{$key})
		{
			$this->active->task->stat->{$key} = 1;
			$this->setChangeKey('active');
			if(!$returnData->sync_task)
				$returnData->sync_task = array();
			$returnData->sync_task['stat'] = $this->active->task->stat;
		}
	}

	
	function setChangeKey($key){
		$this->changeKey[$key] = 1;
	}
	function setOpenDataChange(){
		$this->openDataChange = true;
	}
	

	
	//==============================================   end
	
	function addDiamond($v){
		if(!$v)
			return;
		global $returnData;
		$this->diamond += $v;
		$this->setChangeKey('diamond');
		$returnData->sync_diamond = $this->diamond;
	}
	
	
	//加钱
	function addCoin($v){
		if(!$v)
			return;
		global $returnData;
		$this->coin += $v;
		$this->setChangeKey('coin');
		$returnData->sync_coin = $this->coin;
	}	
	
	function getHp(){
		return 2 + $this->level;
	}
	
	//取道具数量
	function getPropNum($propID){
		if($this->prop->{$propID})
			return $this->prop->{$propID};
		return 0;
	}
	
	//改变道具数量
	function addProp($propID,$num){
		global $returnData;
		if(!$this->prop->{$propID})
		{
			$this->prop->{$propID} = 0;
		}
			
		$this->prop->{$propID} += $num;
		$this->setChangeKey('prop');	
		
		if(!$returnData->sync_prop)
		{
			$returnData->sync_prop = new stdClass();
		}
		$returnData->sync_prop->{$propID} = $this->prop->{$propID};
	}
	
	//把结果写回数据库
	function write2DB($fromLogin = false){
		//return false;
		function addKey($key,$value,$needEncode=false){
			if($needEncode)
				return $key."='".json_encode($value)."'";
			else 
				return $key."=".$value;
		}
		
		global $conne,$msg,$mySendData,$sql_table,$returnData;
		
		if(!$fromLogin)
		{
			$returnData->sync_opendata = $this->openData;
		}
		
		$arr = array();
		
		if($this->changeKey['rmb'])
			array_push($arr,addKey('rmb',$this->rmb));
		if($this->changeKey['level'])
			array_push($arr,addKey('level',$this->level));
		if($this->changeKey['coin'])
			array_push($arr,addKey('coin',$this->coin));
		if($this->changeKey['diamond'])
			array_push($arr,addKey('diamond',$this->diamond));
		if($this->changeKey['head'])
			array_push($arr,addKey('head',$this->head));
		if($this->changeKey['land_key'])
			array_push($arr,addKey('land_key',"'".$this->land_key."'"));
			
		if($this->changeKey['prop'])
			array_push($arr,addKey('prop',$this->prop,true));
		if($this->changeKey['active'])
			array_push($arr,addKey('active',$this->active,true));		
		if($this->changeKey['card'])
			array_push($arr,addKey('card',$this->card,true));
		if($this->changeKey['card'])
			array_push($arr,addKey('card',$this->card,true));
		if($this->changeKey['pk_common'])
			array_push($arr,addKey('pk_common',$this->pk_common,true));
		if($this->changeKey['opendata'])
			array_push($arr,addKey('opendata',$this->opendata,true));
		if($this->changeKey['use_card'])
			array_push($arr,addKey('use_card',$this->use_card,true));	
				
		debug($this->changeKey);
			
		if(count($arr) > 0 || $this->changeKey['last_land'])
		{
			$this->last_land = time();
			array_push($arr,addKey('last_land',$this->last_land));	
			$sql = "update ".getSQLTable('user_data')." set ".join(",",$arr)." where gameid='".$this->gameid."'";
			  debug($sql);
			if(!$conne->uidRst($sql))//写用户数据失败
			{
				$mySendData->error = 4;
				return false;
			}
		}		

		$this->changeKey = array();
		return true;
			
	}
}

//获取其它玩家的数据
function getUser($gameid){
	global $conne;
	$sql = "select * from ".$sql_table."user_data where id='".$gameid."'";
	$result = $conne->getRowsRst($sql);
	if($result)
		return new GameUser($result);
	return null;
}
?>