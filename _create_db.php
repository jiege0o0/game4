<?php 
$serverID = $_GET["serverid"];
if(!$serverID)
	die('no serverID');
	
$filePath = dirname(__FILE__).'/';
require_once($filePath."_config.php");


	
	
$connect=mysql_connect($sql_url,$sql_user,$sql_password)or die('message=F,Could not connect: ' . mysql_error()); 
mysql_select_db($sql_db,$connect)or die('Could not select database'); 
mysql_query("set names utf8");


//自己的数据
mysql_query("
Create TABLE g4_".$sql_table."user_data(
gameid varchar(32) NOT NULL Unique Key,
uid INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
nick varchar(30),
head varchar(255),
rmb INT UNSIGNED default 0,
diamond INT UNSIGNED default 100,
level TINYINT UNSIGNED default 1,
coin INT UNSIGNED default 1000,
card Text,
prop Text,
tec Text,
pvp Text,
pk_common Text,
use_card Text,
active Text,
opendata Text,
land_key varchar(63),
last_land INT UNSIGNED,
regtime INT UNSIGNED
)",$connect)or die("message=F,Invalid query: " . mysql_error()); 


//日志（邮件）
mysql_query("
Create TABLE g4_".$sql_table."mail(
id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
from_gameid varchar(16),
to_gameid varchar(16),
type TINYINT UNSIGNED,
content varchar(8138),
stat TINYINT UNSIGNED,
time INT UNSIGNED
)",$connect)or die("message=F,Invalid query: " . mysql_error()); 

mysql_query("
Create TABLE g4_".$sql_table."pay_log(
id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
orderno varchar(32),
orderno2 varchar(8),
goodsid varchar(32),
gameid varchar(32),
time INT UNSIGNED,
INDEX(orderno,gameid,orderno2)
)",$connect)or die("message=F,Invalid query: " . mysql_error()); 

mysql_query("
Create TABLE g4_".$sql_table."off_line(
id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
gameid varchar(32),
score INT UNSIGNED,
content varchar(1500),
time INT UNSIGNED
)",$connect)or die("message=F,Invalid query: " . mysql_error()); 

//排行榜
$rankName = array('pk');
foreach($rankName as $key=>$value)
{
	mysql_query("
	Create TABLE g4_".$sql_table."rank_".$value."(
	gameid varchar(32) NOT NULL Unique Key,
	nick varchar(30),
	head varchar(255),
	type TINYINT UNSIGNED,
	score INT UNSIGNED,
	time INT UNSIGNED
	)",$connect)or die("message=F,Invalid query: " . mysql_error());

	//往表插入数据
	$sql = "insert into g4_".$sql_table."rank_".$value."(gameid,score,time) values";
	$arr = array();
	for($i=1;$i<=100;$i++)
	{
		array_push($arr,"('_".$i."',0,0)");
	}
	$sql2 = implode(',',$arr);
	mysql_query($sql.$sql2,
	$connect)or die("message=F,Invalid query: " . mysql_error()); 
}


echo "成功".time();
?>