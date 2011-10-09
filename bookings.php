<?php
/*
 Plugin Name: Bookings
 Plugin URI: http://www.zingiri.net
 Description: Bookings is a powerful reservations scheduler.
 Author: Zingiri
 Version: 1.0.2
 Author URI: http://www.zingiri.net/
 */

define("BOOKINGS_VERSION","1.0.2");

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
	add_action('wp_head','bookings_header');
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
	//if (!is_writable(session_save_path())) $errors[]='PHP sessions are not properly configured on your server, the sessions save path '.session_save_path().' is not writable.';
	if ($upload['error']) $errors[]=$upload['error'];
	if (get_option('bookings_debug')) $warnings[]="Debug is active, once you finished debugging, it's recommended to turn this off";
	if (phpversion() < '5') $warnings[]="You are running PHP version ".phpversion().". We recommend you upgrade to PHP 5.3 or higher.";
	if (ini_get("zend.ze1_compatibility_mode")) $warnings[]="You are running PHP in PHP 4 compatibility mode. We recommend you turn this option off.";
	if (!function_exists('curl_init')) $errors[]="You need to have cURL installed. Contact your hosting provider to do so.";

	if (count($warnings) > 0) {
		echo "<div id='zing-warning' style='background-color:greenyellow' class='updated fade'><p><strong>";
		foreach ($warnings as $message) echo 'Bookings: '.$message.'<br />';
		echo "</strong> "."</p></div>";
	}
	if (count($errors) > 0) {
		echo "<div id='zing-warning' style='background-color:pink' class='updated fade'><p><strong>";
		foreach ($errors as $message) echo 'Bookings:'.$message.'<br />';
		echo "</strong> "."</p></div>";
	}

	return array('errors'=> $errors, 'warnings' => $warnings);
}

function bookings_activate() {
	global $wpdb,$current_user;

	update_option('bookings_key',md5(time().sprintf(mt_rand(),'%10d')));

	update_option("bookings_version",BOOKINGS_VERSION);

}

function bookings_deactivate() {
	bookings_output('deactivate');
	
	delete_option('bookings_key');

	$bookings_options=bookings_options();

	delete_option('bookings_log');
	foreach ($bookings_options as $value) {
		delete_option( $value['id'] );
	}

	delete_option("bookings_log");
	delete_option("bookings_ftp_user"); //legacy
	delete_option("bookings_ftp_password"); //legacy
	delete_option("bookings_version");
	delete_option('bookings-support-us');
}

function bookings_content($content) {
	global $bookings;

	if (preg_match('/\[bookings(.*)\]/',$content,$matches)==1) {
		//print_r($matches);echo '<br />';
		$pg=isset($_REQUEST['zb']) ? $_REQUEST['zb'] : 'book1';
		bookings_output($pg);
		$content='<div id="bookings">';
		$content.=$bookings['output']['body'];
		$content.='</div>';
		return $content;
	} else return $content;
}

function bookings_output($bookings_to_include='') {
	global $post,$bookings;
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
			$buffer=$news->DownloadToString();
			$bookings['output']=json_decode($buffer,true);

			if (!$bookings['output']) {
				$bookings['output']['body']=$buffer;
				$bookings['output']['head']='';
			} else {
				if (isset($bookings['output']['http_referer'])) $_SESSION['bookings']['http_referer']=$bookings['output']['http_referer'];
			}
		}
	}
}

function bookings_header() {
	global $bookings;
	echo '<script type="text/javascript">';
	echo "var bookingsPageurl='".bookings_home()."';";
	echo '</script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/functions.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/ajax.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/calendar.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/lang/calendar-en.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/calendar-setup.js"></script>';

	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/jscalendar/calendar-blue-custom.css" media="screen" />';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/client.css" media="screen" />';

}

function bookings_admin_header() {
	global $bookings;
	echo '<script type="text/javascript">';
	echo "var bookingsPageurl='admin.php?page=bookings&';";
	echo '</script>';
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
	if (is_user_logged_in()) {
		$wp['login']=$current_user->data->user_login;
		$wp['email']=$current_user->data->user_email;
		$wp['first_name']=$current_user->data->first_name ? $current_user->data->first_name: $current_user->data->display_name;
		$wp['last_name']=$current_user->data->last_name ? $current_user->data->last_name : $current_user->data->display_name;
		$wp['roles']=$current_user->roles;
	}
	//$wp['time']=$_SERVER['REQUEST_TIME']+get_option('gmt_offset')*3600;
	$wp['gmt_offset']=get_option('gmt_offset');
	$wp['siteurl']=home_url();
	$wp['sitename']=get_bloginfo('name');
	$wp['pluginurl']=BOOKINGS_URL;
	if (is_admin()) $wp['pageurl']='admin.php?page=bookings&';
	else $wp['pageurl']=bookings_home();

	$wp['admin_email']=get_option('admin_email');
	$wp['key']=get_option('bookings_key');
	$wp['lang']=get_option('bookings_lang'); //get_bloginfo('language');
	$vars.=$and.'wp='.base64_encode(json_encode($wp));

	if (isset($_SESSION['bookings']['http_referer'])) $vars.='&http_referer='.cc_urlencode($_SESSION['bookings']['http_referer']);

	if ($vars) $http.=$vars;

	return $http;
}

function bookings_home() {
	global $post,$page_id;

	$pageID = $page_id;

	if (get_option('permalink_structure')){
		$homePage = get_option('home');
		$wordpressPageName = get_permalink($pageID);
		$wordpressPageName = str_replace($homePage,"",$wordpressPageName);
		$home=$homePage.$wordpressPageName;
		if (substr($home,-1) != '/') $home.='/';
		$home.='?';
	}else{
		$home=get_option('home').'/?page_id='.$pageID.'&';
	}

	return $home;
}

function bookings_ajax() {
	global $bookings;
	echo '<head>'.$bookings['output']['head'].'</head>';
	echo '<body>'.$bookings['output']['body'].'</body>';
	die();
}

function bookings_init()
{
	ob_start();
	session_start();
	if (is_admin() && isset($_GET['zb'])) {
		$pg=$_GET['zb'];
		bookings_output($pg);
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
	$url='http://bookings.zingiri.net/us1/api.php';
	return $url;
}

