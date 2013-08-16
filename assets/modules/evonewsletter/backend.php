<?php
error_reporting(E_ALL ^ E_NOTICE);
if(!defined('MODX_BASE_PATH')){die();}
define('BASE_PATH', dirname(__FILE__).'/');

require_once ( BASE_PATH.'newsletter.class.inc.php' );
$class = new newsletter();


include_once($BASE_PATH.'languages/'.$lang_backend.'.php');

if(!isset($_GET['p'])) { $_GET['p'] = ''; }
if(!isset($_GET['action'])) { $_GET['action'] = 1; }

if (!function_exists('subscribeAlert')) {
  function subscribeAlert($msg){
    global $modx;
    return "<script>window.setTimeout(\"alert('".addslashes($modx->db->escape($msg))."')\",10);</script>";
  }
}


switch($_GET['p']) {
	case "1":
		include (BASE_PATH.'actions/listletter.php');
	break;
	
	case "2":
		include (BASE_PATH.'actions/configs.php');
	break;	
	
	default:
		include (BASE_PATH.'actions/subscribers.php');
	break;
}
?>
