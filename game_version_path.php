<?php
	//��ͬ�汾����ͬ�ķ�����
	if($_POST['game_version'] == 3)
		$filePath = dirname(__FILE__).'/version_3/';
	else if($_POST['debug_server'] == 1)
		$filePath = dirname(__FILE__).'/version_1/';
	else
		$filePath = dirname(__FILE__).'/version_1/';
		
	$dataFilePath = dirname(__FILE__).'/';	
	
	require_once(dirname(__FILE__).'/_config.php');
?>