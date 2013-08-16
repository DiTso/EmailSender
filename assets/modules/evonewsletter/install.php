<?php


$sql = "SHOW TABLES LIKE '$config_table'";
$rs = $modx->db->query($sql);
$count = $modx->db->getRecordCount($rs);

if($count < 1) {
  $sql = "CREATE TABLE IF NOT EXISTS ".$config_table." (
	  `id` int(11) NOT NULL default '0',
	  `mailmethod` varchar(20) NOT NULL default '',
	  `port` int(11) NOT NULL default '0',
	  `smtp` varchar(200) NOT NULL default '',
	  `auth` varchar(5) NOT NULL default '',
	  `authuser` varchar(100) NOT NULL default '',
	  `authpassword` varchar(100) NOT NULL default '',
	  `sendername` varchar(200) NOT NULL default '',
	  `senderemail` varchar(200) NOT NULL default '',
	  `lang_frontend` varchar(100) NOT NULL default '',
	  `lang_backend` varchar(100) NOT NULL default '',
	  PRIMARY KEY  (`id`)
	)";
	$modx->db->query($sql);
	$sql = "INSERT INTO ".$config_table." VALUES (1, 'IsSMTP', 0, '', 'false', '', '', '', '', 'english', 'english')";
	$modx->db->query($sql);
}



$rs 	= $modx->db->query("SHOW TABLES LIKE '$newsletter_table'");
$count = $modx->db->getRecordCount($rs);
if($count < 1) {
  $sql = "CREATE TABLE IF NOT EXISTS ".$newsletter_table." (
	  `id` int(11) NOT NULL auto_increment,
	  `date` date NOT NULL default '0000-00-00',
	  `status` int(11) NOT NULL default '0',
	  `sent` int(11) NOT NULL default '0',
	  `header` longtext,
	  `subject` text NOT NULL,
	  `newsletter` longtext,
	  `footer` longtext,
	  PRIMARY KEY  (`id`)
	)";
	$modx->db->query($sql);
}



$rs 	= $modx->db->query("SHOW TABLES LIKE '$subscribers_table'");
$count  = $modx->db->getRecordCount($rs);
if($count < 1) {
	$sql = "CREATE TABLE IF NOT EXISTS ".$subscribers_table." (
	  `id` int(11) NOT NULL auto_increment,
	  `firstname` varchar(50) NOT NULL default '',
	  `lastname` varchar(50) NOT NULL default '',
	  `email` varchar(50) NOT NULL default '',
	  `status` int(11) NOT NULL default '1',
	  `blocked` int(11) NOT NULL default '0',
	  `lastnewsletter` varchar(50) NOT NULL default '',
	  `created` date NOT NULL default '0000-00-00',
		`lang` varchar(2) NOT NULL default 'ru',
	  PRIMARY KEY  (`id`)
	)";
	$modx->db->query($sql);
}


$rs 	= $modx->db->query("SHOW TABLES LIKE '$queue_table'");
$count  = $modx->db->getRecordCount($rs);
if($count < 1) {
  $sql = "
	CREATE TABLE IF NOT EXISTS `".$queue_table."` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `recipients` text NOT NULL,
	  `control` varchar(100) NOT NULL DEFAULT '',
	  `message_id` text NOT NULL,
	  `status` int(11) NOT NULL DEFAULT '0',
	  `create_time` int(20) NOT NULL DEFAULT '0',
	  `change_time` int(20) NOT NULL DEFAULT '0',
	  `groupid` varchar(10) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`)
	)";
	$modx->db->query($sql);
}

$rs 	= $modx->db->query("SHOW TABLES LIKE '$groups_table'");
$count  = $modx->db->getRecordCount($rs);
if($count < 1) {
	$sql = "
		CREATE TABLE IF NOT EXISTS `".$groups_table."` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`webusers` text NOT NULL,
		`webgroup` text NOT NULL,
		`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`webgroupid` varchar(11) NOT NULL,
		PRIMARY KEY (`id`)
	)";
	$modx->db->query($sql);
}

?>