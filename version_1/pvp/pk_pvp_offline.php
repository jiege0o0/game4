<?php 
$myScore=$userData->pvp->score;
$cwin=$userData->pvp->cwin;
$force1 = min(floor($myScore * (0.9 + $cwin/50)),$myScore - 20);
$force2 = max(floor($myScore * (1.1 + $cwin/50)),$myScore + 20);
do{		
	$time = time();
	$sql = "select * from ".getSQLTable('off_line')." where score between ".$force1." and ".$force2." and gameid!='".$msg->gameid."' and time>0 ORDER BY time limit 5";
	$result = $conne->getRowsArray($sql);
	if(!$result)
	{
		$force1 = min(floor($myScore * (0.7 + $cwin/50)),$myScore - 50);
		$force2 = max(floor($myScore * (1.3 + $cwin/50)),$myScore + 50);
		$sql = "select * from ".getSQLTable('off_line')." where score between ".$force1." and ".$force2." and gameid!='".$msg->gameid."' and time>0 ORDER BY time limit 5";
		$result = $conne->getRowsArray($sql);
	}
	
	if($result)
	{
		foreach($result as $key=>$value)
		{
			if(!$enemy || abs($enemy['score']- $myScore) > abs($value['score']- $myScore))
			{
				$enemy = $value;
			}
		}
		$returnData->enemy = $enemy;
	}

}while(false);

?> 