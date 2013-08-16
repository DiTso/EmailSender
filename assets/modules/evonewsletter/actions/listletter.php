<?php
if ($_GET['action'] == 1) {

			if (!isset($_GET['sortorder'])) {
				$sortorder = 'date';
			} else {
				$sortorder = $modx->db->escape($_GET['sortorder']);
			}
			$sql = "SELECT * FROM `$newsletter_table` ORDER BY `".$sortorder."` ASC";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);
			if ($num > 0) {
				$list = '<script type="text/javascript">
				<!--
				function delete_newsletter(a,b)	{
					answer = confirm("'.$lang_newsletter_delete_alert.'\n"+b)
					if (answer !=0)	{
						location = "index.php?a=112&id='.$modId.'&p=1&action=6&nid="+a
					}
				}
				function send_newsletter(a,b) {
					var g = document.getElementById("groups_"+a).value;
					answer = confirm("'.$lang_newsletter_send_alert1.'\n"+b)
					if (answer !=0)	{
						location = "index.php?a=112&id='.$modId.'&p=1&action=9&nid="+a+"&group="+g
					}
				} 
				//-->
				</script>';
        $list .= '<ul class="actionButtons">
        						<li><a href="index.php?a=112&id='.$modId.'&p=1&action=3">'.$lang_newsletter_create.'</a></li>
        					</ul>
        					<table class="table table-striped table-bordered table-condensed">
        					<thead><tr>
        						<th><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=date"><strong>'.$lang_newsletter_date.'</strong></a></th>
        						<th width="40%"><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=subject"><strong>'.$lang_newsletter_subject.'</strong></a></th>
        						<td><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=status"><strong>'.$lang_newsletter_status.'</strong></a></td>
        						<td><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=sent"><strong>'.$lang_newsletter_sent.'</strong></a></td>
        						<th><strong>'.$lang_newsletter_action.'</strong></th>
        						<th><strong>Отправка</strong></th>
        					</tr></thead>';
				$i=0;	
				while($i < $num){		
					$countQueue = $modx->db->getValue($modx->db->select("count(*)", $queue_table, "`status` = 0 AND message_id = '".(int)mysql_result($result,$i,"id")."'"));
					$newsStatus = ($countQueue > 0) ? $countQueue : '&nbsp;-';
					$row = $modx->db->getRow($result);	
					$list .='<tr>
									<td>'.mysql_result($result,$i,"date").'</td>
									<td>'.mysql_result($result,$i,"subject").'</td>
									<td>'.$newsStatus.'</td>
									<td>'.mysql_result($result,$i,"sent").'</td>
									<td>
										<a href="index.php?a=112&id='.$modId.'&p=1&action=3&nid='.mysql_result($result,$i,"id").'" title="'.$lang_newsletter_edit.'"><img src="media/style/'.$theme.'/images/icons/logging.gif"></a>  
										<a href="index.php?a=112&id='.$modId.'&p=1&action=6&nid='.mysql_result($result,$i,"id").'" onclick=" delete_newsletter(\''.mysql_result($result,$i,"id").'\',\''.mysql_result($result,$i,"subject").'\'); return false;" title="'.$lang_newsletter_delete.'"><img src="media/style/'.$theme.'/images/icons/delete.gif"></a>  
										<a href="index.php?a=112&id='.$modId.'&p=1&action=7&nid='.mysql_result($result,$i,"id").'" title="'.$lang_newsletter_testmail.'"><img src="media/style/'.$theme.'/images/icons/reply.gif"></a>
									</td> 
									<td>
									<select name="group" id="groups_'.mysql_result($result,$i,"id").'">
										<!-- <option value="all">Всем</option> -->'.
										$class->getGroupRows($tplGroupsRow)
										.'</select>
										<a href="index.php?a=112&id='.$modId.'&p=1&action=9&nid='.mysql_result($result,$i,"id").'" onclick=" send_newsletter(\''.mysql_result($result,$i,"id").'\',\''.mysql_result($result,$i,"subject").'\'); return false;"><img src="media/style/'.$theme.'/images/icons/forward.gif"></a>
									</td>
									</tr>';
					$i++;
				}
				$list .= '</table>';
				echo $list;
				
				echo ($countQueue = $modx->db->getValue($modx->db->query('SELECT count(`recipients`) FROM '.$queue_table.' WHERE status = 0'))) ? '<br>В очереди: ' . $countQueue : '<br>В очереди отправки писем нет.';
				
			} else {
				echo $lang_newsletter_noposts.'<ul class="actionButtons"><li><a href="index.php?a=112&id='.$modId.'&p=1&action=3">'.$lang_newsletter_create.'</a></li></ul>';
			}
		} elseif ($_GET['action'] == 2) {
			// Send newsletter 

			$sql = "SELECT * FROM `".$config_table."` WHERE `id` = 1";
			$result = $modx->db->query($sql);
      $row = $modx->db->getRow($result);
      $mailmethod = $row['mailmethod']; 
			$smtp = $row['smtp'];
			$fromname = ($row['sendername']!='') ? $row['sendername'] : $modx->config['site_name'];
			$from = ($row['senderemail']!='') ? $row['senderemail'] : $modx->config['emailsender'];;
			$auth = $row['auth'];
			$authuser = $row['authuser'];
			$authpassword = $row['authpassword'];
      unset($result, $row);

			$nid = (int)$_GET['nid'];
			$sql = "SELECT * FROM `".$newsletter_table."` WHERE `id` = '".$nid."'";
			$result = $modx->db->query($sql);
      $row = $modx->db->getRow($result);
      $newsletter_subject = $row['subject'];
      $newsletter_newsletter = $row['newsletter'];
      unset($result, $row);

			include_once "../manager/includes/controls/class.phpmailer.php";
			
			$sql = "SELECT * FROM `".$subscribers_table."` WHERE `blocked`=0";
			$result = $modx->db->query($sql);
			$num = $modx->db->getRecordCount($result);
      
			$i = (isset($_GET['starti'])) ? (int)$_GET['starti'] : 0;
			$sentsuccess=0;
			echo $lang_newsletter_sending;
			while($i < $num){
        // Created unsubscribe link.
        	$unsbscrb = '';
	        if ($unsbscrbPage && (int)$unsbscrbPage>0) {
	          $email = mysql_result($result,$i,"email");
	          $created = mysql_result($result,$i,"created");
	          $control = md5($email.$created);
	          $unsbscrbUrl = $modx->makeUrl($unsbscrbPage,'','&option=unsbscrb&ctr='.$control.'&ml='.urlencode($email), 'full');
	          $unsbscrb = '<p style="font-size: 10px; color: #cccccc;"> Что бы отказаться от получения писем перейдите по <a href="'.$unsbscrbUrl.'" style="font-size: 10px; color: #cccccc;">этой ссылке</a></p>';
	        }
        
				$mail = new PHPMailer();
				$mail->SMTPDebug = 1;
				if ($mailmethod == 'IsMail') {$mail->IsMail();}
				if ($mailmethod == 'IsSMTP') {
					$mail->IsSMTP();
					$mail->Host = $smtp;
					//$mail->SMTPSecure = "ssl";		// for smtp.gmail.com
					//$mail->Port='465';		// for smtp.gmail.com
					if ($auth == 'true') {
						$mail->SMTPAuth = true;
						$mail->Username = $authuser;
						$mail->Password = $authpassword;
					} else {
						$mail->SMTPAuth = false;
					}
				}
				if ($mailmethod == 'IsSendmail') {$mail->IsSendmail();}
				if ($mailmethod == 'IsQmail') {$mail->IsQmail();}
				$mail->CharSet = $modx->config['modx_charset'];
        		$mail->IsHTML(1);
				$mail->From		= $from;
				$mail->FromName	= $fromname;
				$mail->Subject	= $newsletter_subject;
				$mail->Body		= $newsletter_newsletter.$unsbscrb;
				$mail->AddAddress(mysql_result($result,$i,"email"));
				if(!$mail->send()) {
					echo $lang_newsletter_sending_done4;
					return 'Main mail: ' . $_lang['ef_mail_error'] . $mail->ErrorInfo;
				} else {
					$sentsuccess++;
				}
        unset ($mail);
				$i++;
        if ($sentsuccess == $maillimit) {
        	echo $lang_newsletter_sending_done1 . $i . $lang_newsletter_sending_done2 . $num . $lang_newsletter_sending_done3;
          echo '<meta http-equiv="refresh" content="0; url='.$site_url.'manager/index.php?a=112&id='.$modId.'&p=1&action=2&nid='.$nid.'&starti='.$i.'">';
          exit;
        }
			}
			if ($i > 0) {
				$upd = $modx->db->query("UPDATE `".$newsletter_table."` SET `sent` = `sent` + '".$i."' WHERE `id`='".$nid."'");
			}

			echo $lang_newsletter_sending_done1 . $i . $lang_newsletter_sending_done2 . $num . $lang_newsletter_sending_done3;
			
		} elseif ($_GET['action'] == 3) {
			// Newsletter Rich Text Editor
			$action = 4;
			$nid = $newsletter = $subject = '';
			
			$nid=(isset($_GET['nid']) && (int)$_GET['nid']>0)?(int)$_GET['nid']:0;
			if ($nid > 0) {
				$sql = "SELECT * FROM `$newsletter_table` WHERE `id` = $nid";
				$result = $modx->db->query($sql);
        $row = $modx->db->getRow($result);
        $subject = $row['subject'];
        $newsletter = $row['newsletter'];
				$action = 5;
			}
			
			echo '<div class="content_">
					<h3>'.$lang_newsletter_edit_header.'</h3>
					<form action="index.php?a=112&id='.$modId.'&p=1&action='.$action.'" method="post"><b>
					'.$lang_newsletter_edit_subject.'</b><br /><input type="hidden" name="xid" value="'.$nid.'"><input type="text" size="50" maxlength="50" name="subject" value="'.$subject.'"></input><br /><br /><b>'.$lang_newsletter_edit_content.'</b>';
			
			// Get access to template variable function (to output the RTE)
			include_once($modx->config['base_path'].'manager/includes/tmplvars.inc.php');
		  
			$event_output = $modx->invokeEvent("OnRichTextEditorInit", array('editor'=>$modx->config['which_editor'], 'elements'=>array('tvmailMessage')));
		
			if(is_array($event_output)) {
				$editor_html = implode("",$event_output);
			}
			// Get HTML for the textarea, last parameters are default_value, elements, value
			$rte_html = renderFormElement('richtext', 'mailMessage', '', '', $newsletter);
			
			echo $rte_html;
			
			echo $editor_html;
			echo  '<br />
			      <ul class="actionButtons">
			          <li><input type="submit" value="'.$lang_newsletter_edit_save.'" class="inputbutton"></input></li>
			          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">Отмена</a></li>
			          </ul>
						</div>';
	} elseif ($_GET['action'] == 4) {
		// insert correct path for images
		$testo = str_replace('src="assets/images/','src="'.$site_url.'assets/images/',$_POST['tvmailMessage']);
    $testo = $modx->db->escape($testo);
		// Insert newsletter into database
		$sql = "INSERT INTO $newsletter_table VALUES('', now(), '','', '', '".$modx->db->escape($_POST['subject'])."', '".$testo."', '') ";
		$result = $modx->db->query($sql);
		echo '<h3>'.$lang_newsletter_edit_create.'<h3>';
    echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">К списку новостей</a></li>
          </ul>';
	} elseif ($_GET['action'] == 5) {
		// Update existing newsletter
				// insert correct path for images
		$testo = str_replace('src="assets/images/','src="'.$site_url.'assets/images/',$_POST['tvmailMessage']);
    $testo = $modx->db->escape($testo);
		$sql = "UPDATE $newsletter_table SET subject='".$modx->db->escape($_POST['subject'])."', newsletter='".$testo."' WHERE id='".(int)$_POST['xid']."'";
		$result = $modx->db->query($sql);
		echo '<h3>'.$lang_newsletter_edit_update.'<h3>';
    echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">&nbsp; К списку новостей</a></li>
          </ul>';
	} elseif ($_GET['action'] == 6) {
		// Delete newsletter
		$sql = "DELETE FROM $newsletter_table WHERE id='".(int)$_GET['nid']."'";
		$result = $modx->db->query($sql);
		$modx->db->delete($queue_table, 'message_id = "'.(int)$_GET['nid'].'"');
		echo '<h3>'.$lang_newsletter_edit_delete.'<h3>';
    echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">&nbsp;К списку новостей</a></li>
          </ul>';
    echo '<meta http-equiv="refresh" content="2; url='.$site_url.'manager/index.php?a=112&id='.$modId.'&p=1&action=1">';
		} elseif ($_GET['action'] == 7) {
			// Send test newsletter
			$nid = (int)$_GET['nid'];
			$sql = "SELECT * FROM `$newsletter_table` WHERE `id` = $nid";
			$result = $modx->db->query($sql);
      $row = $modx->db->getRow($result);
      $newsletter_header = $row['header'];
      $newsletter_subject = $row['subject'];
      $newsletter_newsletter = $row['newsletter'];
      $newsletter_footer = $row['footer'];
			
			$sql = "SELECT * FROM `$config_table` WHERE `id` = 1";
			$result = $modx->db->query($sql);
      $row = $modx->db->getRow($result);

      $mailmethod = $row['mailmethod']; 
			$smtp = $row['smtp'];
			$fromname = $row['sendername'];
			$from = $row['senderemail'];
			$auth = $row['auth'];
			$authuser = $row['authuser'];
			$authpassword = $row['authpassword'];
			
			include_once "../manager/includes/controls/class.phpmailer.php";
			$sql = "SELECT * FROM `$subscribers_table`";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);

			$mail = new PHPMailer();
			if ($mailmethod == 'IsMail') {$mail->IsMail();}
			if ($mailmethod == 'IsSMTP') {
				$mail->IsSMTP();
				$mail->Host = $smtp;
				if ($auth == 'true') {
					$mail->SMTPAuth = true;
					$mail->Username = $authuser;
					$mail->Password = $authpassword;
				} else {
					$mail->SMTPAuth = false;
				}
			}
			if ($mailmethod == 'IsSMTP') {$mail->Host = $smtp;}
			if ($mailmethod == 'IsSendmail') {$mail->IsSendmail();}
			if ($mailmethod == 'IsQmail') {$mail->IsQmail();}
			$mail->CharSet = $modx->config['modx_charset'];
      $mail->IsHTML(1);
			$mail->From		= $from;
			$mail->FromName	= $fromname;
			$mail->Subject	= $newsletter_subject;
			$mail->Body		= $newsletter_newsletter;
			$mail->AddAddress($from);
			if(!$mail->send()) {
				echo $lang_newsletter_sending_done4;
				return 'Main mail: ' . $_lang['ef_mail_error'] . $mail->ErrorInfo;
			} else echo $lang_newsletter_sending_done5;
      echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">&nbsp; К списку новостей</a></li>
          </ul>';
		} elseif ($_GET['action'] == 8) {
			// Send news to queue

			$nid = (int)$_GET['nid'];
			$result = $class->send_to_queue($nid, true);

			echo '<p>Поставлено в очередь '.$result.' писем.</p>';
			echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">К списку новостей</a></li>
          </ul>';

			
		} 
		elseif ($_GET['action'] == 9) {
			// Send group newsletter in queue
			$nid = (int)$_GET['nid'];
			$row = $modx->db->getRow( $modx->db->query("SELECT * FROM `$newsletter_table` WHERE `id` = $nid") );
			
			$newsletter_header 		= $row['header'];
			$newsletter_subject 	= $row['subject'];
			$newsletter_newsletter	= $row['newsletter'];
			$newsletter_footer 		= $row['footer'];
			unset($result, $row);

			$group = $_GET['group'];

			if ($group == 'all') {
				$result = $modx->db->query("SELECT `email`, `id` FROM $subscribers_table");
			} elseif ((int)$group > 0) {
				$RESULT = $modx->db->query("SELECT `webusers` FROM `".$webgroup_table."` WHERE `webgroupid`='".$group."'");
				$row2 = $modx->db->getRow($RESULT);
				$result = $modx->db->query("SELECT `email`, `id` FROM $subscribers_table WHERE `id` IN (".$row2['webusers'].")");
				if ($modx->db->getRecordCount($result) < 1) {
					echo 'No subscribers in the selected group. Messages will not be sent.';
					return;
				}
			} else {
				echo 'A group name is not valid. Messages will not be sent.';
				return;
			}

			$countIns = 0;
			while ($row = $modx->db->getRow($result)) {
				if (trim($row['email']) != '') {
					// Created unsubscribe link.
        	$control = '';
	        if ($unsbscrbPage && (int)$unsbscrbPage>0) {
	          $email = $row['email'];
	          $webuserid = $row['id'];
	          $control = md5($email.$webuserid);
	        }
			$fields = array(
              'recipients'	=> trim($row['email']), 
              'message_id'	=> $nid, 
              'create_time'	=> time(),
              'control'		=> $control,
			  'groupid'		=> $group
            ); 
          $modx->db->insert($fields, $queue_table);
          $countIns++;
				}
			}
			echo '<p>Поставлено в очередь '.$countIns.' писем "'.$newsletter_subject.'".</p>';
			echo '<ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&p=1&action=1">К списку новостей</a></li>
          </ul>';
		}

?>