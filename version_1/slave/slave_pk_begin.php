<?php 
$id=$msg->id;
$otherid=$msg->otherid;
$master=$msg->master;
require_once($filePath."pk/pk_tool.php");
require_once($filePath."cache/base.php");

if(!$userData->active->slave_open)//�ǵñ�־������ÿ�ζ����ݿ�
{
	$sql = "select * from ".getSQLTable('slave')." where gameid='".$msg->gameid."'";
	$result = $conne->getRowsRst($sql);
	if(!$result)
	{
		$sql = "insert into ".getSQLTable('slave')."(gameid,nick,type,head,hourcoin,tec_force,level,master,protime,logintime) values('".$msg->gameid."','".$userData->nick."',".$userData->type.",'".$userData->head."',".$userData->hourcoin.",".$userData->tec_force.",".$userData->level.",'".$msg->gameid."',0,".time().")";
		$conne->uidRst($sql);		
	}
	$userData->active->slave_open=true;	
	$userData->setChangeKey('active');	
	
}


do{	
	if($otherid != $msg->gameid)//�����˲����ж����
	{
		$maxNum = $userData->getMaxSlave();
		$sql = "select count(*) as num from ".getSQLTable('slave')." where master='".$msg->gameid."' and gameid!='".$msg->gameid."'";
		$result = $conne->getRowsRst($sql);
		if($result['num'] >= $maxNum)//ū����������
		{
			$returnData -> fail = 8;
			break;
		}	
	}
	
	
	$sql = "select * from ".getSQLTable('slave')." where gameid='".$otherid."'";
	$result = $conne->getRowsRst($sql);
	if(!$result)//�Ҳ������
	{
		$returnData -> fail = 5;
		break;
	}
	
	if($result['master'] != $master)//���˲�һ��
	{
		$returnData -> fail = 6;
		break;
	}
	
	if($result['protime'] > time())//������
	{
		$returnData -> fail = 7;
		$returnData ->p = $result['protime'];
		break;
	}
	
	if(!$userData->testEnergy(1))//û����
	{
		$returnData -> fail = 1;
		break;
	}
	
	foreach($userData->atk_list->list as $key=>$value)
	{
		if($value->id == $id)
		{
			$list = $value->list;
			$hero = $value->hero;
			break;
		}
	}

	if(!$list)
	{
		$returnData -> fail = 2;
		break;
	}
	
	if(!deleteSkillCard($list))//���ܿ���������
	{
		$returnData -> fail = 9;
		break;
	}
	
	$sql = "select * from ".getSQLTable('user_data')." where gameid='".$master."'";
	$otherData = $conne->getRowsRst($sql);
	if(!$otherData)//û��������
	{
		$returnData -> fail = 3;
		break;
	}
	$otherData = new GameUser($otherData,null,1);

	
	$defPosList = array();
	foreach($otherData->def_list->list as $key=>$value)
	{
		if(!$value->close)
		{
			array_push($defPosList,$value);
		}
	}
	
	$len = count($defPosList);
	if($len == 0)//û������
	{
		$returnData -> fail = 4;
		break;
	}
	$defIndex = rand(0,$len-1);
	$defList = $defPosList[$defIndex]->list;
	$defHero = $defPosList[$defIndex]->hero;
	
	
	$sql = "select * from ".getSQLTable('slave')." where gameid='".$msg->gameid."'";
	$result = $conne->getRowsRst($sql);
	if(!$result)//�Ҳ����Լ�
	{
		$sql = "insert into ".getSQLTable('slave')."(gameid,nick,type,head,hourcoin,tec_force,level,master) values('".$userData->gameid."','".$userData->nick."',".$userData->type.",'".$userData->head."',".$userData->hourcoin.",".$userData->tec_force.",".$userData->level.",'".$userData->gameid."')";
		$conne->uidRst($sql);	
	}

	
	$pkData = new stdClass();
	$pkData->seed = time();
	$pkData->players = array();
	$pkData->check = true;
	array_push($pkData->players,createUserPlayer(1,1,$userData,$list,$hero));
	array_push($pkData->players,createDefPlayer(2,2,$otherData,$defList,$defHero));
	
	$returnData -> pkdata = $pkData;
	$userData->addEnergy(-1);
	$userData->pk_common->pktype = 'slave';
	$userData->pk_common->pkdata = $pkData;
	$userData->pk_common->pkcard = $list;
	$userData->pk_common->hero = $hero;
	$userData->pk_common->otherid = $otherid;
	$userData->pk_common->master = $master;
	$userData->pk_common->nick = base64_encode($otherData->nick);
	$userData->pk_common->time = time();
	$userData->setChangeKey('pk_common');

}while(false)


?> 