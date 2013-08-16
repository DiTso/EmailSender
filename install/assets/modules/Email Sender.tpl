// <?php 
/**
 * Email Sender
 * 
 * быстрый поиск и замена в админке
 * 
 * @category	module
 * @version 	0.1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &modId=Module ID;int;7 &path=Path;text;../assets/modules/evonewsletter/ &unsbscrbPage=Unsubscribe page;int;0
 * @internal	@guid docfind3453245kl3245saasdfasd
 * @internal	@shareparams 1
 * @internal	@modx_category add
 * @internal    @installset base, sample
 */

//<?
$lang_backend 		  = $modx->config['manager_language'];
$dbname 						= $modx->db->config['dbase']; // Database name
$table_prefix 			= $modx->db->config['table_prefix']; // Database table prefix
$config_table 			= $table_prefix."easynewsletter_config";
$subscribers_table 	= $table_prefix."easynewsletter_subscribers";
$newsletter_table 	= $table_prefix."easynewsletter_newsletter";
$queue_table 				= $table_prefix."easynewsletter_queue";
$groups_table 			= $table_prefix."easynewsletter_group";
$theme 							= $modx->config['manager_theme'];
$path 							= '../assets/modules/evonewsletter/';


$webgroup_table 	= $table_prefix."easynewsletter_group";
$display 			= isset($display) ? intval($display) : 30; 
$maillimit 			= isset($maillimit) ? intval($maillimit) : 30; 
$sendTpl 			= '';
$_lang 				= '';
$site_url 			= $modx->config['site_url'];
$site_name 			= $modx->config['site_name'];
$tplGroupsRow 		= '<option value="[+webgroupid+]">[+webgroup+] ([+countwebusers+])</option>';



include_once($path.'install.php');
include_once($path.'languages/'.$lang_backend.'.php');



if(!isset($_GET['p'])) { $_GET['p'] = ''; }
$selected1 = $selected2 = $selected0 = '';
switch($_GET['p']) {
  case "1":
    $selected1 = ' selected';
  break;
  case "2":
    $selected2 = ' selected';
  break;
  default:
  $selected0 = ' selected';
}
$notifier = (!$unsbscrbPage || (int)$unsbscrbPage<1) ? '<b>'.$lang_unsubscribenotifier.'</b>' : '';
echo '
<html>
<head>
	<title>MODx</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="media/style/'.$theme.'/style.css?" />
	<link rel="stylesheet" type="text/css" href="'.$path.'enstyle.css?" />
</head>
<body>
<br />
<div class="sectionHeader">Управление рассылкой</div><div class="sectionBody">
<div class="dynamic-tab-pane-control tab-pane">
'.$notifier.'
<div class="tab-row" style="margin-bottom:28px">
<a class="tab'.$selected0.'" href="index.php?a=112&id='.$modId.'&action=1"><span>'.$lang_links_subscribers.'</span></a>
<a class="tab'.$selected1.'" href="index.php?a=112&id='.$modId.'&p=1&action=1"><span>'.$lang_links_newsletter.'</span></a> 
<a class="tab'.$selected2.'" href="index.php?a=112&id='.$modId.'&p=2&action=1"><span>'.$lang_links_configuration.'</span></a>
</div>
</div>
<div class="sectContent"><br />';
include($path.'backend.php');
echo '
</div>
</div>
</body>
</html>
';
return;

