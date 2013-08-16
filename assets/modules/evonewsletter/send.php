<?php
error_reporting(E_ALL ^ E_NOTICE);
// Установка режима MODx API
define('MODX_API_MODE', true);
define('MGR_DIR','manager');
require_once('../../../manager/includes/protect.inc.php');
include_once('../../../manager/includes/config.inc.php');
require_once('../../../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();

include_once "../../../manager/includes/controls/class.phpmailer.php";

$table_prefix 		= $modx->db->config['table_prefix']; // Database table prefix
$config_table 		= $table_prefix . "easynewsletter_config";
$newsletter_table 	= $table_prefix . "easynewsletter_newsletter";
$queue_table 		= $table_prefix . "easynewsletter_queue";
$countQueue 		= $modx->db->getValue($modx->db->select('count(*)', $queue_table, '`status` = 0'));

if (!$countQueue) exit();

$mailLimit			= 10;
$unsbscrbPage 		= 15;
$mailLimit 			= $mailLimit > $countQueue? $countQueue : $mailLimit ;
// Get module config
$row 				= $modx->db->getRow( $modx->db->query( "SELECT * FROM `$config_table` WHERE `id` = 1" ) );
$mailmethod 		= $row['mailmethod'];
$smtp 				= $row['smtp'];
$fromname 			= $row['sendername'];
$from 				= $row['senderemail'];
$auth 				= $row['auth'];
$authuser 			= $row['authuser'];
$authpassword 		= $row['authpassword'];
$success 			= 0;
unset($sql, $result, $row);

echo 'В очереди:'.$countQueue.'<br/>';

for ($i = 0; $i < $mailLimit; $i++) {
    // Get one row from queue
    $sql = "SELECT que.groupid, que.id queueId, que.recipients email, que.control control, new.subject subject, new.newsletter newsletter
            FROM $queue_table que, $newsletter_table new
            WHERE que.status = 0
            AND que.message_id = new.id
            LIMIT 1";
			
    $result = $modx->db->query($sql);

    if (!$modx->db->getRecordCount($result)) exit();
    $row 				= $modx->db->getRow($result);
    $queueId 			= intval($row['queueId']);
	
    $email 				= $row['email'];
    $control 			= $row['control'];
    $newsletter_subject = $row['subject'];
    $newsletter_body 	= $row['newsletter'];
    $fields 			= array('status' => 1, 'change_time' => time());
    $upd = $modx->db->update($fields, $queue_table, 'id="' . $queueId . '" AND status = 0');
    if (!$upd) continue;

    // Created unsubscribe link.
    //if ($control && $unsbscrbPage) {

      $unsbscrbUrl = str_replace('/assets/modules/evonewsletter','',$modx->makeUrl($unsbscrbPage));//, '', '&option=unsbscrb&ctr=' . $control . '&ml=' . urlencode($email), 'full');
      $unsbscrb = '<p> <a href="http://' . $_SERVER['HTTP_HOST'].$unsbscrbUrl . '?action=unsubscribe&group='.$row['groupid'].'&email='.$row['email'].'"> unsubscribe / отписаться</a> </p>';
   /* } else {
      $unsbscrb = '';
    }*/

    // Trying to send message
    try {
      $mail = new PHPMailer();
      switch ($mailmethod) {
        case 'IsMail':
          $mail->IsMail();
        break;
        case 'IsSendmail':
          $mail->IsSendmail();
        break;
        case 'IsQmail':
          $mail->IsQmail();
        break;
        case 'IsSMTP':
          $mail->IsSMTP();
          $mail->Host = $smtp;
		  $mail->SMTPDebug = 2;
          //$mail->SMTPSecure = "ssl";            // for smtp.gmail.com
          //$mail->Port='465';            // for smtp.gmail.com
          if ($auth == 'true') {
            $mail->SMTPAuth = true;
            $mail->Username = $authuser;
            $mail->Password = $authpassword;
          } else {
            $mail->SMTPAuth = false;
            }
        break;
        default: throw new Exception('Not support send method');
      }
        $mail->CharSet 	= 'utf-8';
        $mail->IsHTML(1);
        $mail->From 	= $from;
        $mail->FromName = $fromname;
        $mail->Subject 	= $newsletter_subject;
        $mail->Body 	= $newsletter_body . $unsbscrb;
        $mail->AddAddress($email);
		
        if (!$mail->send()) {
          throw new Exception ($mail->ErrorInfo());
        }
        $fields3 = array('status' => 3, 'change_time' => time());
        $upd3 = $modx->db->update($fields3, $queue_table, 'id="' . $queueId . '"');
        $success++;
    } catch (Exception $e) {
        $sendError .= 'Mail Error ' . $e->getMessage();
        error_log($sendError, 0);
        $fields2 = array('status' => 2, 'change_time' => time());
        $upd2 = $modx->db->update($fields2, $queue_table, 'id="' . $queueId . '"');
    }

}

$queueNews = $modx->db->select('message_id', $queue_table, 'status = 3 GROUP BY message_id');

while( $row = $modx->db->getRow( $queueNews ) ) { 
	$delSuccess = $modx->db->delete($queue_table, 'message_id = "'.$row['message_id'].'" AND status = 3');
	$countSuccess = mysql_affected_rows(); 
	if ($delSuccess && $countSuccess > 0) {
		$upd4 = $modx->db->query("UPDATE `".$newsletter_table."` SET `sent` = `sent` + '".$countSuccess."' WHERE `id`='".$row['message_id']."'");
	}
}
echo $success;
 
 ?>