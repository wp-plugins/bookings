<?php
/*
 Plugin Name: Bookings
 Plugin URI: http://www.zingiri.com/bookings
 Description: Bookings is a powerful reservations scheduler.
 Author: Zingiri
 Version: 1.3.3
 Author URI: http://www.zingiri.com/
 */

define("BOOKINGS_VERSION","1.3.3");

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

define("BOOKINGS_USER_CAP",get_option('bookings_user_cap') ? get_option('bookings_user_cap') : 'edit_posts');
define("BOOKINGS_ADMIN_CAP",get_option('bookings_admin_cap') ? get_option('bookings_admin_cap') : 'manage_options');

define("BOOKINGS_URL", WP_CONTENT_URL . "/plugins/".BOOKINGS_PLUGIN."/");

$bookings_version=get_option("bookings_version");
if ($bookings_version != BOOKINGS_VERSION) {
	if ($bookings_version && ($bookings_version <= '1.3.0') && !get_option('bookings_region')) update_option('bookings_region','us1');
	update_option("bookings_version",BOOKINGS_VERSION);
}

if (get_option('bookings_region')) {
	add_action("init","bookings_init");
	if (isset($_GET['ajax']) && ($_GET['ajax'] == 1)) {
		add_action("init","bookings_ajax");
	} else {
		add_action('wp_head','bookings_header');
	}
	add_filter('the_content', 'bookings_content', 10, 3);
}

add_action('admin_head','bookings_admin_header');
add_action('admin_notices','bookings_admin_notices');

register_activation_hook(__FILE__,'bookings_activate');
register_deactivation_hook(__FILE__,'bookings_deactivate');

require_once(dirname(__FILE__) . '/includes/shared.inc.php');
require_once(dirname(__FILE__) . '/includes/http.class.php');
require_once(dirname(__FILE__) . '/controlpanel.php');

function bookings_admin_notices() {
	global $bookings;
	$errors=array();
	$warnings=array();
	$files=array();
	$dirs=array();

	if (isset($bookings['output']['warnings']) && is_array($bookings['output']['warnings']) && (count($bookings['output']['warnings']) > 0)) {
		$warnings=$bookings['output']['warnings'];
	}
	if (isset($bookings['output']['errors']) && is_array($bookings['output']['errors']) && (count($bookings['output']['errors']) > 0)) {
		$errors=$bookings['output']['errors'];
	}
	$upload=wp_upload_dir();
	//if (!is_writable(session_save_path())) $errors[]='PHP sessions are not properly configured on your server, the sessions save path '.session_save_path().' is not writable.';
	if ($upload['error']) $errors[]=$upload['error'];
	if (!get_option('bookings_region')) $warnings[]="Please verify your settings on the Bookings control panel and click 'Update'";
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
	delete_option("bookings_region");
	delete_option('bookings-support-us');
}

function bookings_content($content) {
	global $bookings;

	if (preg_match_all('/\[bookings(.*)\]/',$content,$matches)) {
		$pg=isset($_REQUEST['zb']) ? $_REQUEST['zb'] : 'book1';
		if ($pg=='book1') {
			foreach ($matches[0] as $id => $match) {
				$postVars=array();
				if ($matches[1][$id]) {
					$vars=explode(',',$matches[1][$id]);
					foreach ($vars as $var) {
						$t=explode('=',$var);
						$n=trim($t[0]);
						$v=isset($t[1]) ? trim($t[1]) : 1;
						if ($n=='template') $postVars['template']=$v;
						elseif ($n=='resource') $postVars['machid']=$v;
						elseif ($n=='schedule') $postVars['scheduleid']=$v;
						elseif ($n=='product') $postVars['productid']=$v;
						else echo '<br />Unknown variable '.$n;
					}
				}
				bookings_output($pg,$postVars);
				$output='<div id="bookings">';
				$output.=$bookings['output']['body'];
				$output.='</div>';
				$content=str_replace($match,$output,$content);
			}
		} else {
			bookings_output($pg);
			$output='<div id="bookings">';
			$output.=$bookings['output']['body'];
			$output.='</div>';
			$content=$output;

		}
		return $content;
	} elseif (isset($_REQUEST['page']) && ($_REQUEST['page']=='bookings') && isset($_REQUEST['zb']) && $_REQUEST['zb']) {
		bookings_output($_REQUEST['zb']);
		$output='<div id="bookings">';
		$output.=$bookings['output']['body'];
		$output.='</div>';
		$content=str_replace($match,$output,$content);
		return $content;
	} else return $content;
}

function bookings_output($bookings_to_include='',$postVars=array()) {
	global $post,$bookings;
	global $wpdb;
	global $wordpressPageName;
	global $bookings_loaded;

	$ajax=isset($_REQUEST['ajax']) ? $_REQUEST['ajax'] : false;

	$http=bookings_http($bookings_to_include);
	bookings_log('Notification','Call: '.$http);
	//echo '<br />'.$http.'<br />';
	$news = new zHttpRequest($http,'bookings');
	$news->noErrors=true;
	$news->post=array_merge($news->post,$postVars);

	if (!$news->curlInstalled()) {
		bookings_log('Error','CURL not installed');
		return "cURL not installed";
	} elseif (!$news->live()) {
		bookings_log('Error','A HTTP Error occured');
		return "A HTTP Error occured";
	} else {
		if ($ajax==1) {
			ob_end_clean();
			$buffer=$news->DownloadToString();
			$bookings['output']=json_decode($buffer,true);
			if (!$bookings['output']) {
				$bookings['output']['body']=$buffer;
				$bookings['output']['head']='';
			}
			if (isset($_REQUEST['scr'])) {
				echo $bookings['output']['body'];
			} else {
				echo '<html><head>';
				echo $bookings['output']['head'];
				echo '</head><body>';
				echo $bookings['output']['body'];
				echo '</body></hmtl>';
			}
			die();
		} elseif ($ajax==2) {
			ob_end_clean();
			$output=$news->DownloadToString();
			foreach (array('content-disposition','content-type') as $i) {
				if (isset($news->headers[$i])) header($i.':'.$news->headers[$i]);
			}
			echo $news->body;
			die();
			//echo 'it is ajax 2';
			die();
		} else {
			$buffer=$news->DownloadToString();
			if ($news->error) {
				$bookings['output']=array();
				if (is_admin()) $bookings['output']['body']='An error occured when connecting to the Bookings service:<br /><div style="width:100%;overflow:scroll">'.$news->errorMsg.'</div><br />If you need help with this, please contact our technical support service.';
				else $bookings['output']['body']='The service is currently not available, please try again later.';
				return false;
			}
			//print_r($buffer);
			$bookings['output']=json_decode($buffer,true);
			if (isset($bookings['output']['reload']) && $bookings['output']['reload']) {
				$buffer=$news->DownloadToString();
				$bookings['output']=json_decode($buffer,true);
			}
			if (!$bookings['output']) {
				$bookings['output']['body']=$buffer;
				$bookings['output']['head']='';
			} else {
				if (isset($bookings['output']['http_referer'])) $_SESSION['bookings']['http_referer']=$bookings['output']['http_referer'];
			}

			$bookings['output']['body']=bookings_parser($bookings['output']['body']);
		}
	}
}

function bookings_parser($buffer) {
	global $wp_version;
	//<textarea id="element_1_1" name="element_1_1" class="theEditor element text" cols="40" rows="3" >test</textarea>
	if ($wp_version >= '3.3') {
		$f[]='/<textarea.id\="(.*?)".*class\="theEditor.*>(.*?)<\/textarea>/';
		$buffer=preg_replace_callback($f,'bookings_replace',$buffer);
	}
	return $buffer;
}

function bookings_replace($match) {
	$id=$match[1];
	$content=$match[2];
	ob_start();
	wp_editor($content,$id);
	return ob_get_clean();
}

function bookings_header() {
	global $bookings;

	echo '<script type="text/javascript">';
	echo "var bookingsPageurl='".bookings_home()."';";
	echo '</script>';

	//if (isset($bookings['output']['head'])) echo $bookings['output']['head'];
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/functions.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/ajax.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/calendar.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/lang/calendar-en.js"></script>';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jscalendar/calendar-setup.js"></script>';
	
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/jscalendar/calendar-blue-custom.css" media="screen" />';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/client.css" media="screen" />';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/colors.css" media="screen" />';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/integrated_view.css" media="screen" />';

}

function bookings_admin_header() {
	global $bookings,$wp_version;
	if (isset($bookings['output']['head'])) echo $bookings['output']['head'];
	echo '<script type="text/javascript">';
	echo "var bookingsPageurl='admin.php?page=bookings&';";
	echo "var aphpsAjaxURL='".get_admin_url().'admin.php?page=bookings&zb=ajax&ajax=1&form='."';";
	echo "var aphpsURL='".bookings_url(false).'aphps/fwkfor/'."';";
	echo "var wsCms='gn';";
	echo '</script>';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/admin.css" media="screen" />';
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/integrated_view.css" media="screen" />';
	echo '<script type="text/javascript" src="' . BOOKINGS_URL . 'js/jquery-ui-1.7.3.custom.min.js"></script>';
	if ($wp_version < '3.3') wp_tiny_mce( false, array( 'editor_selector' => 'theEditor' ) );

}

function bookings_http($page="index") {
	global $current_user;

	$vars="";
	$http=bookings_url().'?pg='.$page;
	$and="&";
	if (count($_GET) > 0) {
		foreach ($_GET as $n => $v) {
			if (!in_array($n,array('page','zb')))
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
		$wp['first_name']=isset($current_user->data->first_name) ? $current_user->data->first_name: $current_user->data->display_name;
		$wp['last_name']=isset($current_user->data->last_name) ? $current_user->data->last_name : $current_user->data->display_name;
		$wp['roles']=$current_user->roles;
	}
	$wp['lic']=get_option('bookings_lic');
	$wp['gmt_offset']=get_option('gmt_offset');
	$wp['siteurl']=home_url();
	$wp['sitename']=get_bloginfo('name');
	$wp['pluginurl']=BOOKINGS_URL;
	if (is_admin()) $wp['pageurl']=get_admin_url().'admin.php?page=bookings&';
	else $wp['pageurl']=bookings_home();

	$wp['time_format']=get_option('time_format');
	$wp['admin_email']=get_option('admin_email');
	$wp['key']=get_option('bookings_key');
	$wp['lang']=get_option('bookings_lang'); //get_bloginfo('language');
	$wp['client_version']=BOOKINGS_VERSION;
	if (current_user_can(BOOKINGS_ADMIN_CAP)) $wp['cap']='admin';
	elseif (current_user_can(BOOKINGS_USER_CAP)) $wp['cap']='operator';
	
	$vars.=$and.'wp='.urlencode(base64_encode(json_encode($wp)));

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

function bookings_init() {
	global $wp_version;

	ob_start();
	session_start();
	if (is_admin()) {
		if (isset($_GET['page']) && $_GET['page']=='bookings' && !current_user_can(BOOKINGS_ADMIN_CAP) && !isset($_GET['zb'])) {
			$_GET['zb']='stats';
		}
		if ((isset($_GET['zb']) || !isset($_SESSION['bookings']['menus']))) {
			$pg=isset($_GET['zb']) ? $_GET['zb'] : 'usage';
			bookings_output($pg);
			if ($pg=='form_edit') {
				wp_enqueue_script('prototype');
				wp_enqueue_script('scriptaculous');
			}
		}
		if ($wp_version < '3.3') {
			wp_enqueue_script(array('editor', 'thickbox', 'media-upload'));
			wp_enqueue_style('thickbox');
		}
	}
	wp_enqueue_script('jquery');

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

function bookings_url($endpoint=true) {
	if (substr(get_option('bookings_region'),0,2)=='eu') $url='http://bookings-eu.zingiri.net/'.get_option('bookings_region').'/';
	else $url='http://bookings.zingiri.net/us1/';
	if ($endpoint) $url.='api.php';
	return $url;
}


