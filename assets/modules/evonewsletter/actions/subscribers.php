<?php

switch($_GET['action']){
	case '1':
		$sortorder = !isset($_GET['sortorder']) ? 'id' : $_GET['sortorder'] ;
		
		// Pagination
		$page = ( isset($_GET['page'])  && (int)$_GET['page'] > 0 ) ? (int)$_GET['page'] : 1;
		$rescount = $modx->db->getValue( $modx->db->select( 'count(*)', $subscribers_table ) );
		$totalPages = ceil($rescount / $display);
		if ($totalPages < 1) $totalPages = 1;
		if ($page > $totalPages) $page = $totalPages;
		$start = $page * $display - $display;

		if ($totalPages > 1) {
			$pagestext = $lang_subscriber_pages1 . $page . $lang_subscriber_pages2 . $totalPages;
			$pages = '<p>'.$pagestext.'</p>';
			$pages .= '<ul class="actionButtons">';
			if ($page == 1) {
			  $pages .= '<li><a href="index.php?a=112&id='.$modId.'&action=1&sortorder='.$sortorder.'&page='.($page+1).'" class="next">Далее &gt;</a></li>';
			}
			if ($page == $totalPages) {
			  $pages .= '<li><a href="index.php?a=112&id='.$modId.'&action=1&sortorder='.$sortorder.'&page='.($page-1).'" rel="nofollow">&lt; Назад</a></li>';
			  } else {
				$pages .= '
					  <li><a href="index.php?a=112&id='.$modId.'&action=1&sortorder='.$sortorder.'&page='.($page-1).'"  rel="nofollow">&lt; Назад</a></li>
					  <li><a href="index.php?a=112&id='.$modId.'&action=1&sortorder='.$sortorder.'&page='.($page+1).'" class="next">Далее &gt;</a></li>
					';
				}
			$pages .= '</ul>';
		} else $pages = '';
         
		#end paginate
		
		
		$result = $modx->db->select('*', $webgroup_table, '', $sortorder. ' ASC', $start.','.$display);
		if( $modx->db->getRecordCount($result) >= 1 ) {
		while ($row = $modx->db->getRow($result)) {
			$groupRow .= '<tr><td>'.$row['webgroup'].'</td><td><a href="index.php?a=112&id='.$modId.'&action=6&webgroupid='.$row['webgroupid'].'">'.count(explode(',',$row['webusers'])).'</a></td></tr>';
		}
		  $groupThead = '<table class="table table-striped table-bordered table-condensed">
						<thead><tr>
						<th>Событие</th>
						<th>Подписчиков</th>
						</tr></thead><tbody>'.$groupRow.'</tbody></table>';
		}
		echo $groupThead;
	break;


	case '2':
		// Update existing subscriber form
		$sql = "SELECT * FROM `$subscribers_table` WHERE id = '".$_GET['nid']."'";
		$result = $modx->db->query($sql);
		$blocked = mysql_result($result,$i,"blocked")==1 ? ' checked="checked"' : '';
		$i = 0;
			echo '<div class="content_">
					<h3>'.$lang_subscriber_edit_header.'</h3>
					<form action="index.php?a=112&id='.$modId.'&action=3&nid='.$_GET['nid'].'" method="post">
					<b>'.$lang_subscriber_firstname.'</b><br /><input type="text" size="50" maxlength="50" name="firstname" value="'.mysql_result($result,$i,"firstname").'"><br />
					<b>'.$lang_subscriber_lastname.'</b><br /><input type="text" size="50" maxlength="50" name="lastname" value="'.mysql_result($result,$i,"lastname").'"><br />
					<b>'.$lang_subscriber_email.'</b><br /><input type="text" size="50" maxlength="50" name="email" value="'.mysql_result($result,$i,"email").'"><br /><br />
					<div>
					<b>'.$lang_subscriber_blocked.'</b> <input type="checkbox" name="blocked" '.$blocked.'><br /><br />
					</div>
					<ul class="actionButtons">
          <li><input type="submit" value="'.$lang_subscriber_edit_save.'" class="inputbutton"></input></li>
          <li><a href="index.php?a=112&id='.$modId.'&action=1">Назад</a></li>
          </ul>
          </div>';
	break;

	case '3':
		// Update existing subscriber
		$blocked = ($_POST['blocked'] == 'on') ? 1 : 0;
		$sql = "UPDATE $subscribers_table SET 
						firstname='".$modx->db->escape($_POST['firstname'])."', 
						lastname='".$modx->db->escape($_POST['lastname'])."', 
						email='".$modx->db->escape($_POST['email'])."',
						blocked='".$blocked."' 
						WHERE id='".(int)$_GET['nid']."'";
		$result = $modx->db->query($sql);
		//$class->set_user_groups($_GET['nid'], $_POST['user_groups']);
		echo '<h3>'.$lang_subscriber_edit_update.'<h3>';
		echo '<ul class="actionButtons">
		<li><a href="index.php?a=112&id='.$modId.'&action=1">К списку подписчиков</a></li>
		</ul>';
	break;

	case '4':
		// Delete subscriber
		$sql = "DELETE FROM $subscribers_table WHERE id='".(int)$_GET['nid']."'";
		$result = $modx->db->query($sql);

		$sql = "DELETE FROM `".$groups_table."` WHERE `subscriber`=".(int)$_GET['nid'].";";
		$rs = $modx->db->query($sql);
		if(!$rs) {
			echo "Something went wrong while trying to delete the web user's access permissions...";
			return;
		}
		echo '<h3>'.$lang_subscriber_edit_delete.'<h3>';
		echo '<ul class="actionButtons">
		<li><a href="index.php?a=112&id='.$modId.'&action=1">К списку подписчиков</a></li>
		</ul>';
	break;
	
	case '5':
		// Add subscriber
		$msg = $email = $firstname = $lastname = '';    

		if (isset($_POST['subscribe'])){
			$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $modx->db->escape($_POST['email']) : '';
			$firstname = isset($_POST['firstname']) ? $modx->db->escape($_POST['firstname']) : '';
			$lastname = isset($_POST['lastname']) ? $modx->db->escape($_POST['lastname']) : '';
			$date = date("Y-m-d"); 

			if ($email) {
			$num = $modx->db->getRecordCount($modx->db->select('id', $subscribers_table, "email = '$email'"));
			if ($num < 1) {
				$fields = array(
				  'firstname'	=> $firstname, 
				  'lastname'	=> $lastname, 
				  'email'	=> $email,
				  'created' => $date
				); 
				$modx->db->insert($fields, $subscribers_table);
				unset ($firstname, $lastname, $email);
				$msg = $lang_subscriber_edit_update;
			} else {
			  $msg = $lang_alreadysubscribed;
			}
			} else {
				$msg = $lang_notvalidemail;
			}
		}

		$list = '
		<script type="text/javascript">
		function validate_email(field,alerttxt)	{	
		with (field){
		apos=value.indexOf("@")
		dotpos=value.lastIndexOf(".")
		if (apos<1||dotpos-apos<2) 
		  {alert(alerttxt);return false}
		else {return true}
		}
		}
		function validate_form(thisform){
		with (thisform)	{
		if (validate_email(email,"'.$lang_notvalidemail.'")==false)
		  {email.focus();return false}
		}
		}
		</script>';
		$list .=  '<div class="content_">
				<h3>'.$lang_subscriber_new_header.'</h3>
				<form action="index.php?a=112&id='.$modId.'&action=5" method="post">
				<b>'.$lang_subscriber_firstname.'</b><br /><input type="text" size="50" maxlength="50" name="firstname" value="'.$firstname.'"><br />
				<b>'.$lang_subscriber_lastname.'</b><br /><input type="text" size="50" maxlength="50" name="lastname" value="'.$lastname.'"><br />
				<b>'.$lang_subscriber_email.'</b><br /><input type="text" size="50" maxlength="50" name="email" value="'.$email.'"><br /><br />
				<ul class="actionButtons">
		<li><input type="submit" value="'.$lang_subscriber_edit_save.'" class="inputbutton" name="subscribe"></li>
		<li><a href="index.php?a=112&id='.$modId.'&action=1">Назад</a></li>
		</ul>
		</div>';

		echo $list;
		if ($msg) echo subscribeAlert($msg);
	break;

	case '6':
		// Subscribers list '

    	$webgroupid=(isset($_GET['webgroupid']) && $_GET['webgroupid']>0)?$_GET['webgroupid']:0;
		
    	if (!empty($webgroupid)) {
			$RESULT = $modx->db->query("SELECT `webusers` FROM `".$webgroup_table."` WHERE `webgroupid`='{$webgroupid}'");
			$row = $modx->db->getRow($RESULT);
    		$result = $modx->db->query("SELECT `email` FROM `".$subscribers_table."` WHERE `id` IN (".$row['webusers'].")");
    		while($row = $modx->db->getRow($result)) { 
    			$output .= $row['email'].'<br>';
    		}
    		echo '<p>'.$output.'</p><ul class="actionButtons">
          <li><a href="index.php?a=112&id='.$modId.'&action=1"> К списку событий</a></li>
          </ul>';
    	} else {
    		echo 'Не удалось получить список адресов.<ul class="actionButtons"><li><a href="index.php?a=112&id='.$modId.'&action=1">К списку событий</a></li></ul>';
    	}
	break;
}

?>