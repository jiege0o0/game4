<?php 
require_once($filePath."pk/pk_tool.php");
require_once($filePath."pvp/pvp_tool.php");
require_once($filePath."cache/base.php");

do{		
	
	$pkData = new stdClass();
	$pkData->score = $userData->pvp->score;
	$pkData->gameid = $userData->gameid;
	$pkData->pktype = 'pvp';
	$pkData->pkdata = getMyPKData();


	
	$returnData->pkdata = $pkData;
	$userData->pk_common->pktype = 'pvp_online';
	$userData->pk_common->pkdata = $pkData;
	$userData->pk_common->time = time();
	$userData->pk_common->pkstarttime = 0;
	$userData->setChangeKey('pk_common');
}while(false);

?> 