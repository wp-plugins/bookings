<?php
/*
 Plugin Name: Bookings
 Plugin URI: http://www.zingiri.net
 Description: Bookings is a powerful appointment scheduler.

 Author: Zingiri
 Version: 0.9.0
 Author URI: http://www.zingiri.net/
 */

define("BOOKINGS_VERSION","0.9.0");

// Pre-2.6 compatibility for wp-content folder location
if (!defined("WP_CONTENT_URL")) {
	define("WP_CONTENT_URL", get_option("siteurl") . "/wp-content");
}
if (!defined("WP_CONTENT_DIR")) {
	define("WP_CONTENT_DIR", ABSPATH . "wp-content");
}

if (!defined("BOOKINGS_PLUGIN")) {
	$bookings_plugin=str_replace(realpath(dirname(__FILE__).'/..'),"",dirname(__FILE__));
	$bookings_plugin=substr($bookings_plugin,1);
	define("BOOKINGS_PLUGIN", $bookings_plugin);
}

if (!defined("BLOGUPLOADDIR")) {
	$upload=wp_upload_dir();
	define("BLOGUPLOADDIR",$upload['path']);
}

define("BOOKINGS_URL", WP_CONTENT_URL . "/plugins/".BOOKINGS_PLUGIN."/");

$bookings_version=get_option("bookings_version");
add_action("init","bookings_init");
if (isset($_GET['ajax']) && ($_GET['ajax'] == 1)) {
	add_action("init","bookings_ajax");
} else {
	add_action('admin_head','bookings_admin_header');
}
add_action('admin_notices','bookings_admin_notices');
add_filter('the_content', 'bookings_content', 10, 3);

register_activation_hook(__FILE__,'bookings_activate');
register_deactivation_hook(__FILE__,'bookings_deactivate');

require_once(dirname(__FILE__) . '/includes/shared.inc.php');
require_once(dirname(__FILE__) . '/includes/http.class.php');
require_once(dirname(__FILE__) . '/includes/footer.inc.php');
require_once(dirname(__FILE__) . '/controlpanel.php');

function bookings_admin_notices() {
	global $wpdb;
	$errors=array();
	$warnings=array();
	$files=array();
	$dirs=array();

	$upload=wp_upload_dir();
	if (!is_writable(session_save_path())) $errors[]='PHP sessions are not properly configured on your server, the sessions save path '.session_save_path().' is not writable.';
	if ($upload['error']) $errors[]=$upload['error'];
	if (get_option('bookings_debug')) $warnings[]="Debug is active, once you finished debugging, it's recommended to turn this off";
	if (phpversion() < '5') $warnings[]="You are running PHP version ".phpversion().". We recommend you upgrade to PHP 5.3 or higher.";
	if (ini_get("zend.ze1_compatibility_mode")) $warnings[]="You are running PHP in PHP 4 compatibility mode. We recommend you turn this option off.";
	if (!function_exists('curl_init')) $errors[]="You need to have cURL installed. Contact your hosting provider to do so.";

	if (count($warnings) > 0) {
		echo "<div id='zing-warning' style='background-color:greenyellow' class='updated fade'><p><strong>";
		foreach ($warnings as $message) echo 'WHMCS Bridge: '.$message.'<br />';
		echo "</strong> "."</p></div>";
	}
	if (count($errors) > 0) {
		echo "<div id='zing-warning' style='background-color:pink' class='updated fade'><p><strong>";
		foreach ($errors as $message) echo 'WHMCS Bridge :'.$message.'<br />';
		echo "</strong> "."</p></div>";
	}

	return array('errors'=> $errors, 'warnings' => $warnings);
}


/**
 * Activation: creation of database tables & set up of pages
 * @return unknown_type
 */
function bookings_activate() {
	global $wpdb,$current_user;
	
	update_option('bookings_key',md5(__FILE__.sprintf(mt_rand(),'%10d')));

	$bookings_version=get_option("bookings_version");
	if (!$bookings_version) add_option("bookings_version",BOOKINGS_VERSION);
	else update_option("bookings_version",BOOKINGS_VERSION);

	//create pages
	if (!$bookings_version) {
		$pages=array();
		$pages[]=array("Bookings","Bookings","*",0);

		$ids="";
		foreach ($pages as $i =>$p)
		{
			$my_post = array();
			$my_post['post_title'] = $p['0'];
			$my_post['post_content'] = '';
			$my_post['post_status'] = 'publish';
			$my_post['post_author'] = 1;
			$my_post['post_type'] = 'page';
			$my_post['menu_order'] = 100+$i;
			$my_post['comment_status'] = 'closed';
			$id=wp_insert_post( $my_post );
			if (empty($ids)) { $ids.=$id; } else { $ids.=",".$id; }
			if (!empty($p[1])) add_post_meta($id,'bookings_page',$p[1]);
		}
		if (get_option("bookings_pages")) update_option("bookings_pages",$ids);
		else add_option("bookings_pages",$ids);
	}

}

/**
 * Deactivation: nothing to do
 * @return void
 */
function bookings_deactivate() {
	delete_option('bookings_key');

	$ids=get_option("bookings_pages");
	$ida=explode(",",$ids);
	foreach ($ida as $id) {
		wp_delete_post($id);
	}
	$bookings_options=bookings_options();

	delete_option('bookings_log');
	foreach ($bookings_options as $value) {
		delete_option( $value['id'] );
	}

	delete_option("bookings_log");
	delete_option("bookings_ftp_user"); //legacy
	delete_option("bookings_ftp_password"); //legacy
	delete_option("bookings_version");
	delete_option("bookings_pages");
	delete_option('cc-ce-bridge-cp-support-us');
}

function bookings_content($content) {
	echo 'hello';	
	return $content;
}

function bookings_output($bookings_to_include='') {
	global $post;
	global $wpdb;
	global $wordpressPageName;
	global $bookings_loaded;

	$ajax=false;

	$http=bookings_http($bookings_to_include);
	bookings_log('Notification','Call: '.$http);
	//echo '<br />'.$http.'<br />';
	$news = new zHttpRequest($http,'bookings');

	if (!$news->curlInstalled()) {
		bookings_log('Error','CURL not installed');
		return "cURL not installed";
	} elseif (!$news->live()) {
		bookings_log('Error','A HTTP Error occured');
		return "A HTTP Error occured";
	} else {
		if ($ajax==1) {
			ob_end_clean();
			$output=$news->DownloadToString();
			$body=$news->body;
			$body=bookings_parser_ajax1($body);
			echo $body;
			die();
		} elseif ($ajax==2) {
			ob_end_clean();
			$output=$news->DownloadToString();
			$body=$news->body;
			$body=bookings_parser_ajax2($body);
			header('HTTP/1.1 200 OK');
			echo $body;
			//echo 'it is ajax 2';
			die();
		} else {
			$output=$news->DownloadToString();
			return $output;
		}
	}
}


function bookings_header() {
	global $bookings;

}

function bookings_admin_header() {
	global $bookings;
	if (isset($bookings['output']['head'])) echo $bookings['output']['head'];
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/admin.css" media="screen" />';
}

function bookings_http($page="index") {
	global $current_user;

	$vars="";
	$http=bookings_url().'?pg='.$page;
	$and="&";
	if (count($_GET) > 0) {
		foreach ($_GET as $n => $v) {
			if (!in_array($n,array('page')))
			{
				$vars.= $and.$n.'='.cc_urlencode($v);
				$and="&";
			}
		}
	}

	$and="&";

	$wp=array();
	$wp['login']=$current_user->data->user_login;
	$wp['email']=$current_user->data->user_email;
	$wp['first_name']=$current_user->data->first_name;
	$wp['last_name']=$current_user->data->last_name;
	$wp['roles']=$current_user->roles;
	$wp['siteurl']=home_url();
	$wp['pluginurl']=BOOKINGS_URL;
	$wp['admin_email']=get_option('admin_email');
	$wp['key']=get_option('bookings_key');
	$wp['lang']=get_bloginfo('language');
	$vars.=$and.'wp='.base64_encode(json_encode($wp));

	if (isset($_SESSION['bookings']['http_referer'])) $vars.='&http_referer='.cc_urlencode($_SESSION['bookings']['http_referer']);

	if ($vars) $http.=$vars;

	return $http;
}


function bookings_mainpage() {
	$ids=get_option("bookings_pages");
	$ida=explode(",",$ids);
	return $ida[0];
}

function bookings_ajax() {
	global $bookings;
	echo '<head>'.$bookings['output']['head'].'</head>';
	echo '<body>'.$bookings['output']['body'].'</body>';
	die();
}

/**
 * Initialization of page, action & page_id arrays
 * @return unknown_type
 */
function bookings_init()
{
	global $bookings;

	if (isset($_GET['zb'])) {
		ob_start();
		if (!session_id()) session_start();
		$pg=$_GET['zb'];
		$buffer=bookings_output($pg);
		//echo $buffer;
		$bookings['output']=json_decode($buffer,true);

		if (!$bookings['output']) {
			$bookings['output']['body']=$buffer;
			$bookings['output']['head']='';
		} else {
			if (isset($bookings['output']['http_referer'])) $_SESSION['bookings']['http_referer']=$bookings['output']['http_referer'];
		}
	}
}

function bookings_log($type=0,$msg='',$filename="",$linenum=0) {
	if (get_option('bookings_debug')) {
		if (is_array($msg)) $msg=print_r($msg,true);
		$v=get_option('bookings_log');
		if (!is_array($v)) $v=array();
		array_unshift($v,array(time(),$type,$msg));
		update_option('bookings_log',$v);
	}
}

function bookings_url() {
	$url='http://bookings.zingiri.net/api.php';
	return $url;
}

