//<?php
/**
 * Email Sender MODx Evolution
 *  
 * @category 	   snippet
 * @version 	   0.1.0
 * @license 	   http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	   @modx_category add
 */

if (is_file($modx->config['base_path'] . 'assets/modules/yams/class/yams.class.inc.php')){
		require_once( $modx->config['base_path'] . 'assets/modules/yams/class/yams.class.inc.php' );
		require_once( $modx->config['base_path'] . 'assets/modules/yams/yams.module.funcs.inc.php' );
		$evt = &$modx->Event;
		$yams = YAMS::GetInstance();
		$langId = $yams->DetermineCurrentLangId();  
}

$langId = $langId==''?'ru':$langId;

switch($_GET['action']){
    case'subscribe1':   $id='1'.$langId;$group="Новости (".$langId.")";  			break;
	//case'unsubscribe1': $id='1'.$langId;$group="Новости (".$langId.")";  			break;
    case'subscribe2':   $id='2'.$langId;$group="Пресс-релизы (".$langId.")"; 	break;
	//case'unsubscribe2': $id='2'.$langId;$group="Пресс-релизы (".$langId.")";  break;
}


$email = $modx->db->escape($_GET['email']);
$lang = 'ru';

if ($email!= ''){
switch($_GET['action']){
  case 'subscribe1':
  case 'subscribe2':
  $RESULT = $modx->db->query("SELECT * FROM modx_easynewsletter_subscribers WHERE email='{$email}' AND lang = '{$lang}'");
  	if ($modx->db->getRecordCount($RESULT)==0) {
    	$modx->db->query("INSERT into modx_easynewsletter_subscribers SET email='{$email}', lang = '{$lang}'");
      $user = $modx->db->getInsertId();
  	} else {
      $row=$modx->db->GetRow($RESULT);
      $user = $row['id'];
    }
  
  
  
  $RESULT =$modx->db->query("SELECT * FROM modx_easynewsletter_group WHERE webgroupid='{$id}'");
  $row=$modx->db->GetRow($RESULT);
  $webusers=explode(',',$row['webusers']);

  if ($modx->db->getRecordCount($RESULT)==0) {
     $field['webgroupid'] =	$id;
     $field['webgroup'] 	= $group;
     $modx->db->insert($field,'modx_easynewsletter_group');
  }
  
  foreach($webusers as $key=>$value) {
    if ($value=='') unset($webusers[$key]);
  }

  if (!in_array($user,$webusers) ){
    $webusers[]=$user;
    $modx->db->update(array('webusers'=>implode(',',$webusers)),'modx_easynewsletter_group',' webgroupid="'.$id.'"');
  }
  $act =  <<<ACT
  <script>$.fancybox('[[YAMS? &get=`text` &from=`ru::Вы подписаны на рассылку||en::You are subscribed to our newsletter`]]');</script>
ACT;
	
  break;
  
  case 'unsubscribe':
	$id = $modx->db->escape($_GET['group']);
	$RESULT = $modx->db->query("SELECT * FROM modx_easynewsletter_subscribers WHERE email='{$email}' AND lang = '{$lang}'");
    $row=$modx->db->GetRow($RESULT);
    $user = $row['id'];

	
  $RESULT =$modx->db->query("SELECT * FROM modx_easynewsletter_group WHERE webgroupid='{$id}'");
  $row=$modx->db->GetRow($RESULT);
  $webusers=explode(',',$row['webusers']);
  
  foreach($webusers as $key=>$value) {
    if ($value=='') unset($webusers[$key]);
   	if ($value==$user) unset($webusers[$key]);
  }

  $modx->db->update(array('webusers'=>implode(',',$webusers)),'modx_easynewsletter_group',' id='.$row['id']);
  $act =  <<<ACT
  <script>$.fancybox('[[YAMS? &get=`text` &from=`ru::Вы отписаны от рассылки||en::You are unsubscribed from the mailing list`]]');</script>
ACT;
  break; 
  
}
}
/*
$RESULT =$modx->db->query("SELECT * FROM modx_easynewsletter_group WHERE webgroupid=1");
$row=$modx->db->GetRow($RESULT);
$webusers=explode(',',$row['webusers']);

if (in_array($user,$webusers) ){
  $links.= '<a href="javascript:send_s(\'unsubscribe1\')"   class="butt">Отписаться от рассылки новостей</a><br />';
} else {
  $links.=  '<a href="javascript:send_s(\'subscribe1\')"   class="butt">Подписаться на рассылку новостей</a><br />';
}

$RESULT =$modx->db->query("SELECT * FROM modx_easynewsletter_group WHERE webgroupid=2");
$row=$modx->db->GetRow($RESULT);
$webusers=explode(',',$row['webusers']);
  echo $row['webusers'];;
if (in_array($user,$webusers) ){
  $links.=  '<a href="javascript:send_s(\'unsubscribe2\')"   class="butt">Отписаться от рассылки пресс-релизов</a><br />';
} else {
  $links.=  '<a href="javascript:send_s(\'subscribe2\')"   class="butt">Подписаться на рассылку пресс-релизов</a><br />';
}*/

$links.=  '<a href="javascript:send_s(\'subscribe1\')"   class="butt">Подписаться на рассылку новостей</a><br />';
$links.=  '<a href="javascript:send_s(\'subscribe2\')"   class="butt">Подписаться на рассылку пресс-релизов</a><br />';

$OUT = <<<OUT
{$act}
<div class="subscribe">
<form action="" name="subscribe" method="get">
Email:<br />
<input class="subsc" type="text" name="email"/><br />
<input type="hidden" name="action" value="" />
{$links}
<script>function send_s(text){document.subscribe.action.value = text;document.subscribe.submit();}</script>
</form>
</div>
OUT;

echo $OUT;
?>

