<?php

if ($_GET['action'] == 1) {
			// Show Configuration
			$sql = "SELECT *  FROM `$config_table` WHERE `id` = 1";
			$result = $modx->db->query($sql);
      $i=0;
			$mailmethod = mysql_result($result,$i,"mailmethod");
			$auth = mysql_result($result,$i,"auth");
			$list = '<div class="content_">
					<h3>'.$lang_config_header.'</h3>
					<form action="index.php?a=112&id='.$modId.'&p=2&action=2" method="post"><b>';
			$list .= '<table class="table table-striped table-bordered table-condensed">';
			
			$list .= '<tr><td><strong>'.$lang_config_sendername.'</strong>:</td><td> <input type="text" size="100" maxlength="100" name="sendername" value="'.stripslashes(mysql_result($result,$i,"sendername")).'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_sendername_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_senderemail.'</strong>:</td><td> <input type="text" size="100" maxlength="100" name="senderemail" value="'.mysql_result($result,$i,"senderemail").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_senderemail_description.'</td></tr>';
			
			$list .= '<tr><td><strong>'.$lang_config_mail.'</strong>:</td><td> <select name="mailmethod">';

			if($mailmethod == 'IsMail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsMail"'.$dropdown.'>PHP mail</option>';

			if($mailmethod == 'IsSMTP'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsSMTP"'.$dropdown.'>SMTP</option>';

			if($mailmethod == 'IsSendmail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsSendmail"'.$dropdown.'>Sendmail</option>';

			if($mailmethod == 'IsQmail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsQmail"'.$dropdown.'>Qmail MTA</option>';
	
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_mail_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_auth.'</strong>:</td><td> <select name="auth">';

			if($auth == 'true'){$dropdown3 = ' selected="selected"';} else {$dropdown3 = '';}
			$list .= '<option value="true"'.$dropdown3.'>'.$lang_config_true.'</option>';

			if($auth == 'false'){$dropdown3 = ' selected="selected"';} else {$dropdown3 = '';}
			$list .= '<option value="false"'.$dropdown3.'>'.$lang_config_false.'</option>';
			
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_auth_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_smtp.'</strong>:</td><td> <input type="text" size="100" maxlength="100" name="smtp" value="'.mysql_result($result,$i,"smtp").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_smtp_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_authuser.'</strong>:</td><td> <input type="text" size="100" maxlength="100" name="authuser" value="'.mysql_result($result,$i,"authuser").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_authuser_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_authpassword.'</strong>:</td><td> <input type="password" size="100" maxlength="100" name="authpassword" value="'.mysql_result($result,$i,"authpassword").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_authpassword_description.'</td></tr>';
// -------------------------------------------------		
			$list .= '<tr><td><strong>'.$lang_config_lang_website.'</strong>:</td><td> <select name="lang_frontend">';
			if(mysql_result($result,$i,"lang_frontend") == 'english'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="english"'.$dropdown2.'>English</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'russian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="russian"'.$dropdown2.'>Русский</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'danish'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="danish"'.$dropdown2.'>Dansk</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'italian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="italian"'.$dropdown2.'>Italiano</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'german'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="german"'.$dropdown2.'>Deutsch</option>';
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_lang_website_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_lang_manager.'</strong>:</td><td> <select name="lang_backend">';			
			if(mysql_result($result,$i,"lang_backend") == 'english'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="english"'.$dropdown2.'>English</option>';
			if(mysql_result($result,$i,"lang_backend") == 'russian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="russian"'.$dropdown2.'>Русский</option>';
			if(mysql_result($result,$i,"lang_backend") == 'danish'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="danish"'.$dropdown2.'>Dansk</option>';
			if(mysql_result($result,$i,"lang_backend") == 'italian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="italian"'.$dropdown2.'>Italiano</option>';
			if(mysql_result($result,$i,"lang_backend") == 'german'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="german"'.$dropdown2.'>Deutsch</option>';
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_lang_manager_description.'</td></tr>';
// -------------------------------------------------
			$list .= '</table>';
			$list .= '<ul class="actionButtons"><li><input type="submit" value="'.$lang_config_save.'" class="inputbutton"></input></li></ul>';
			echo $list;
		} elseif ($_GET['action'] == 2) {
			// Update configuration
			$sql = "UPDATE $config_table 
							SET mailmethod='".$modx->db->escape($_POST['mailmethod'])."', 
									smtp='".$modx->db->escape($_POST['smtp'])."', 
									auth='".$modx->db->escape($_POST['auth'])."', 
									authuser='".$modx->db->escape($_POST['authuser'])."', 
									authpassword='".$_POST['authpassword']."', 
									sendername='".addslashes($_POST['sendername'])."', 
									senderemail='".$modx->db->escape($_POST['senderemail'])."', 
									lang_frontend='".$modx->db->escape($_POST['lang_frontend'])."', 
									lang_backend='".$modx->db->escape($_POST['lang_backend'])."' 
							WHERE id='1'";	
			$result = $modx->db->query($sql);
			echo $lang_config_update;	
		}

?>