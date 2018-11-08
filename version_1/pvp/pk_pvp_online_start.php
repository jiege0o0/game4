<?php 
$seed=$msg->seed;
require_once($filePath."pk/pk_tool.php");
require_once($filePath."pvp/pvp_tool.php");

do{		

	$myScore = $userData->pvp->score;
	$myLevel = getPVPLevel($myScore);
	$winNum = $offlineData->cwin;
	$userData->pvp->pknum ++;

	$preSubScore = min(20,$myScore);
	$userData->pvp->subscore = $preSubScore;
	$userData->pvp->score = $myScore - $preSubScore;
	
	$userData->pk_common->pkdata->pkstarttime = time();
	$userData->pk_common->pkdata->seed = $seed;
	$userData->setChangeKey('pk_common');
	$userData->setChangeKey('pvp');

}while(false);

?> 