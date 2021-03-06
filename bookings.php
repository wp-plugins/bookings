<?php
/*
 * Plugin Name: Bookings
 * Plugin URI: http://www.zingiri.com/bookings
 * Description: Bookings is a powerful reservations scheduler.
 * Author: Zingiri
 * Version: 5.0.0
 * Author URI: http://www.zingiri.com/
 */
define("BOOKINGS_VERSION", bookings_version());

if (!defined("BOOKINGS_USER_CAP")) define("BOOKINGS_USER_CAP", get_option('bookings_user_cap') ? get_option('bookings_user_cap') : 'edit_posts');
if (!defined("BOOKINGS_ADMIN_CAP")) define("BOOKINGS_ADMIN_CAP", get_option('bookings_admin_cap') ? get_option('bookings_admin_cap') : 'manage_options');

define("BOOKINGS_URL", plugin_dir_url(__FILE__));

if (defined('BOOKINGS_LIVE')) require (dirname(__FILE__) . '/live.php');

$bookingsRegions['us1']=array('North America, South America & Asia Pacific','bookings5.us1.zingiri.net/');
$bookingsRegions['eu1']=array('Europe & Africa','bookings5.eu1.zingiri.net/');
if (file_exists(dirname(__FILE__) . '/regions.php')) require (dirname(__FILE__) . '/regions.php');

if (!defined("BOOKINGS_JSPREFIX")) define("BOOKINGS_JSPREFIX", "min");

if (get_option('bookings_region') && (!defined('BOOKINGS_LIVE') || get_option('bookings_siteurl'))) {
	if (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('bookings_ajax_frontend','bookings_ajax_backend','aphps_ajax'))) {
		add_action("init", "bookings_init");
	}
	if (!isset($_GET['ajax']) || ($_GET['ajax'] != 1)) {
		add_action('wp_head', 'bookings_header');
	}
	add_action('wp_footer', 'bookings_footer');
	add_shortcode('bookings', 'bookings_shortcode');
	add_shortcode('bookings1', 'bookings_shortcode_pages');
	add_shortcode('bookings2', 'bookings_shortcode_pages');
	add_shortcode('bookings3', 'bookings_shortcode_pages');
	add_shortcode('bookings4', 'bookings_shortcode_pages');
	// remove auto loading rel=next post link in header
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
	add_action('wp_ajax_bookings_ajax_frontend', 'bookings_ajax_frontend_callback');
	add_action('wp_ajax_nopriv_bookings_ajax_frontend', 'bookings_ajax_frontend_callback');
}

add_action('admin_head', 'bookings_admin_header');
add_action('admin_notices', 'bookings_admin_notices');

register_deactivation_hook(__FILE__, 'bookings_deactivate');
register_uninstall_hook(__FILE__, 'bookings_uninstall');

require_once (dirname(__FILE__) . '/includes/shared.inc.php');
require_once (dirname(__FILE__) . '/includes/http.class.php');
require_once (dirname(__FILE__) . '/controlpanel.php');
require_once (dirname(__FILE__) . '/includes/widget.inc.php');
require_once (dirname(__FILE__) . '/includes/bookings.class.php');

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
	if ($upload['error']) $errors[]=$upload['error'];
	if (!get_option('bookings_region')) $warnings[]="Please verify your settings on the Bookings control panel and click 'Update'";
	if (get_option('bookings_debug')) $warnings[]="Debug is active, once you finished debugging, it's recommended to turn this off";
	if (phpversion() < '5') $warnings[]="You are running PHP version " . phpversion() . ". We recommend you upgrade to PHP 5.3 or higher.";
	if (ini_get("zend.ze1_compatibility_mode")) $warnings[]="You are running PHP in PHP 4 compatibility mode. We recommend you turn this option off.";
	if (!function_exists('curl_init')) $errors[]="You need to have cURL installed. Contact your hosting provider to do so.";
	
	if (count($warnings) > 0) {
		echo "<div id='zing-warning' style='background-color:greenyellow' class='updated fade'><p><strong>";
		foreach ($warnings as $message)
			echo 'Bookings: ' . $message . '<br />';
		echo "</strong> " . "</p></div>";
	}
	if (count($errors) > 0) {
		echo "<div id='zing-warning' style='background-color:pink' class='updated fade'><p><strong>";
		foreach ($errors as $message)
			echo 'Bookings:' . $message . '<br />';
		echo "</strong> " . "</p></div>";
	}
	
	return array('errors' => $errors,'warnings' => $warnings);
}

function bookings_deactivate() {
	bookings_output('deactivate');
	unset($_SESSION['bookings']);
	delete_option("bookings_ftp_user"); // legacy
	delete_option("bookings_ftp_password"); // legacy
}

function bookings_uninstall() {
	bookings_output('uninstall');
	
	unset($_SESSION['bookings']);
	
	delete_option('bookings_key');
	
	$bookings_options=bookings_options();
	
	delete_option('bookings_log');
	foreach ($bookings_options as $value) {
		delete_option($value['id']);
	}
	delete_option("bookings_http_referer");
	delete_option("bookings_log");
	delete_option("bookings_version");
	delete_option("bookings_region");
	delete_option('bookings-support-us');
}

function bookings_shortcode($atts, $content=null, $code="") {
	global $bookings, $bookings_shortcode_id, $post, $bookings_shortcode_processed, $bookings_shortcode_counter;
	
	if (!is_page() && !is_single()) return '';
	
	// support old style, comma delimited format
	$attString='';
	if (is_array($atts) && count($atts) > 0) {
		foreach ($atts as $id => $value) {
			if (!is_numeric($id) && ($value != ',')) {
				if ($attString) $attString.=',';
				$attString.=$id . '=' . $value;
			}
		}
		$atts=array();
		$t1=explode(',', $attString);
		foreach ($t1 as $p1) {
			$p2=explode('=', $p1);
			$atts[$p2[0]]=$p2[1];
		}
	}
	
	$bookings_shortcode_counter=isset($bookings_shortcode_counter) ? $bookings_shortcode_counter + 1 : 1;
	
	$defaults=array('template' => '','scheduleid' => '','calendar' => '','form' => 'form1','resourcerequired' => 'no');
	extract(shortcode_atts($defaults, $atts));
	$pg=isset($_REQUEST['zfaces']) ? $_REQUEST['zfaces'] : 'book1';
	if ($pg == 'book1') {
		$bookings_shortcode_id=$bookings_shortcode_counter;
		unset($_SESSION['bookings']['sid']);
		$postVars=array();
		if (is_array($atts) && count($atts) > 0) {
			foreach ($atts as $id => $value) {
				$postVars[$id]=$value;
			}
		}
		// echo '<h1>'.$pg.'</h1>';
		bookings_output($pg, $postVars);
		$output='<div id="bookings" class="bookings aphps">';
		$output.=$bookings['output']['body'];
		$output.='</div>';
		return $content . $output;
	} else {
		if ($bookings_shortcode_processed) return;
		if (isset($_REQUEST['bookingssid'])) $bookings_shortcode_id=$_SESSION['bookings']['sid']=$_REQUEST['bookingssid'];
		elseif (isset($_SESSION['bookings']['sid'])) $bookings_shortcode_id=$_SESSION['bookings']['sid'];
		if ($bookings_shortcode_id && ($bookings_shortcode_id != $bookings_shortcode_counter)) return;
		$bookings_shortcode_processed=true;
		bookings_output($pg);
		$output='<div id="bookings" class="bookings aphps">';
		$output.=$bookings['output']['body'];
		$output.='</div>';
		return $content . $output;
	}
}

function bookings_shortcode_pages($atts, $content=null, $code="") {
	if (!is_page() && !is_single()) return '';
	
	$step=isset($_REQUEST['zfaces']) ? $_REQUEST['zfaces'] : 'book1';
	switch ($code) {
		case 'bookings1' :
			if ($step == 'book1') return $content;
			break;
		case 'bookings2' :
			if ($step == 'book2') return $content;
			break;
		case 'bookings3' :
			if ($step == 'book3') return $content;
			break;
		case 'bookings4' :
			if ($step == 'book4') return $content;
			break;
	}
	return '';
}

function bookings_output($bookings_to_include='', $postVars=array()) {
	global $post, $bookings, $bookingsTemplate;
	global $wpdb;
	global $wordpressPageName;
	global $bookings_loaded;
	
	$ajax=isset($_REQUEST['ajax']) ? $_REQUEST['ajax'] : false;
	
	$http=bookings_http($bookings_to_include);
	bookings_log('Notification', 'Call: ' . $http);
	// echo '<br />'.$http.'<br />';
	$news=new bookingsHttpRequest($http, 'bookings');
	$news->noErrors=true;
	$news->post=array_merge($news->post, $postVars);
	
	if (!$news->curlInstalled()) {
		bookings_log('Error', 'CURL not installed');
		return "cURL not installed";
	} elseif (!$news->live()) {
		bookings_log('Error', 'A HTTP Error occured');
		return "A HTTP Error occured";
	} else {
		if (($ajax == 1) && !in_array($_REQUEST['form'], array('form_field','form'))) {
			while ( count(ob_get_status(true)) > 0 ) {
				ob_end_clean();
			}
			$buffer=$news->DownloadToString();
			echo $buffer;
		} elseif (($ajax == 1) && in_array($_REQUEST['form'], array('form_field','form'))) {
			if (!defined('BOOKINGS_AJAX_ORIGIN')) {
				while ( count(ob_get_status(true)) > 0 ) {
					ob_end_clean();
				}
			}
			$buffer=$news->DownloadToString();
			$output=json_decode($buffer, true);
			echo $output['body'];
			if (!defined('BOOKINGS_AJAX_ORIGIN')) die();
		} elseif (($ajax == 2) || (($ajax == 1) && ($_REQUEST['form'] == 'form_field'))) {
			while ( count(ob_get_status(true)) > 0 ) {
				ob_end_clean();
			}
			$output=$news->DownloadToString();
			foreach (array('content-disposition','content-type') as $i) {
				if (isset($news->headers[$i])) header($i . ':' . $news->headers[$i]);
			}
			if (isset($news->body)) echo $news->body;
			die();
		} elseif ($ajax == 3) {
			while ( count(ob_get_status(true)) > 0 ) {
				ob_end_clean();
			}
			$buffer=$news->DownloadToString();
			$output=json_decode($buffer, true);
			echo $output['body'];
			die();
		} else {
			$buffer=$news->DownloadToString();
			if ($news->error) {
				$bookings['output']=array();
				if (is_admin()) $bookings['output']['body']='An error occured when connecting to the Bookings service.<br />If you need help with this, please contact our <a href="http://www.zingiri.com/go" target="_blank">technical support service</a>.';
				else $bookings['output']['body']='The service is currently not available, please try again later.';
				return false;
			}
			$bookings['output']=json_decode($buffer, true);
			if (isset($bookings['output']['reload']) && $bookings['output']['reload']) {
				$buffer=$news->DownloadToString();
				$bookings['output']=json_decode($buffer, true);
			}
			if (!$bookings['output']) {
				$bookings['output']['body']=$buffer;
				$bookings['output']['head']='';
			} else {
				if (isset($bookings['output']['http_referer'])) update_option('bookings_http_referer', $bookings['output']['http_referer']);
				else update_option('bookings_http_referer', '');
			}
			if (isset($bookings['output']['template']) && $bookings['output']['template']) $bookingsTemplate=$bookings['output']['template'];
			else $bookings['output']['template']=$bookingsTemplate;
		}
	}
}

function bookings_header() {
	global $bookings, $post;
	
	echo '<script type="text/javascript">';
	echo "var bookingsPageurl='" . bookings_home() . "';";
	echo "var bookingsAjaxUrl='" . admin_url('admin-ajax.php') . "?action=bookings_ajax_frontend&bookingspid=" . $post->ID . "&';";
	echo "var aphpsAjaxURL='" . admin_url('admin-ajax.php') . "'+'?action=bookings_ajax_frontend&zfaces=ajax&ajax=1&form=';";
	echo "var aphpsBaseUrl='" . bookings_url(false) . "';";
	echo '</script>';
	if (BOOKINGS_JSPREFIX == 'src') {
		bookings_load_js_client();
	} else {
		echo '<script type="text/javascript" src="' . bookings_cdn('js') . 'js/' . BOOKINGS_JSPREFIX . '/client.js"></script>';
	}
	
	$pg=isset($_REQUEST['zfaces']) ? $_REQUEST['zfaces'] : 'book1';
	echo '<link rel="stylesheet" type="text/css" href="' . bookings_cdn('css') . 'app/bookings/css/client.css" media="screen" />';
	if (in_array($pg, array('myschedule'))) {
		echo '<link rel="stylesheet" type="text/css" href="' . bookings_cdn('css') . 'app/bookings/css/client/css-client.css" media="screen" />';
	}
}

function bookings_admin_header() {
	global $bookings, $wp_version;
	echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/admin.css" media="screen" />';
}

function bookings_http($page="index", $params=array()) {
	global $current_user, $post, $bookings_shortcode_id;
	
	$vars="";
	$http=bookings_url();
	$and='?';
	if (!isset($_REQUEST['zfaces'])) {
		$http.='?zfaces=' . $page;
		$and="&";
	}
	if (count($_GET) > 0) {
		foreach ($_GET as $n => $v) {
			if ($n == 'action' && $v == 'bookings_ajax_frontend') continue;
			if (!in_array($n, array('page','bookingsasid'))) {
				if (is_array($v)) {
					foreach ($v as $w) {
						$vars.=$and . $n . '[]=' . urlencode($w);
						$and="&";
					}
				} else {
					$vars.=$and . $n . '=' . urlencode($v);
					$and="&";
				}
			}
		}
	}
	
	$and="&";
	
	$wp=array();
	if (is_user_logged_in()) {
		$wp['login']=$current_user->data->user_login;
		$wp['email']=$current_user->data->user_email;
		$wp['first_name']=get_user_meta($current_user->data->ID, 'first_name', true) ? get_user_meta($current_user->data->ID, 'first_name', true) : $current_user->data->display_name;
		$wp['last_name']=get_user_meta($current_user->data->ID, 'last_name', true) ? get_user_meta($current_user->data->ID, 'last_name', true) : $current_user->data->display_name;
		$wp['roles']=$current_user->roles;
	}
	$wp['lic']=get_option('bookings_lic');
	$wp['gmt_offset']=get_option('gmt_offset');
	$wp['siteurl']=get_option('bookings_siteurl') ? get_option('bookings_siteurl') : home_url();
	$wp['sitename']=get_bloginfo('name');
	$wp['pluginurl']=BOOKINGS_URL;
	$wp['client']='wordpress';
	if (defined('BOOKINGS_AJAX_ORIGIN') && (BOOKINGS_AJAX_ORIGIN == 'f')) {
		$wp['mode']='f';
		$wp['pageurl']=bookings_home();
		$wp['sid']=$_REQUEST['bookingspid'] . '-' . (isset($_REQUEST['bookingssid']) ? $_REQUEST['bookingssid'] : '1');
	} elseif (!is_admin()) {
		$wp['mode']='f';
		$wp['pageurl']=bookings_home();
		if (isset($post)) $wp['sid']=$post->ID . '-' . (isset($bookings_shortcode_id) ? $bookings_shortcode_id : '1');
	} else {
		$wp['mode']='b';
		$wp['pageurl']=get_admin_url() . 'admin.php?page=bookings&';
		$wp['secret']=get_option('bookings_secret');
	}
	if (get_option('bookings_showcase')) {
		$wp['desc']=get_bloginfo('description');
		$wp['showcase']=1;
	}
	$wp['time_format']=get_option('time_format');
	$wp['admin_email']=get_option('admin_email');
	$wp['key']=get_option('bookings_key');
	$wp['lang']=get_option('bookings_lang') ? get_option('bookings_lang') : 'en_US'; // get_bloginfo('language');
	$wp['client_version']=BOOKINGS_VERSION;
	if (isset($_SESSION['bookings']['force_license_check'])) {
		$wp['force_license_check']=true;
		unset($_SESSION['bookings']['force_license_check']);
	}
	if (current_user_can(BOOKINGS_ADMIN_CAP)) $wp['cap']='admin';
	elseif (current_user_can(BOOKINGS_USER_CAP)) $wp['cap']='operator';
	$wp['is_secure']=zingiri::isSecure();
	
	$wp=array_merge($wp, $params);
	
	$wp=apply_filters('bookings_http_call', $wp);
	
	$vars.=$and . 'wp=' . urlencode(base64_encode(json_encode($wp)));
	
	$_SESSION['bookings']['wp']=urlencode(base64_encode(json_encode($wp)));
	
	if (get_option('bookings_http_referer')) $vars.='&http_referer=' . urlencode(get_option('bookings_http_referer'));
	
	if ($vars) $http.=$vars;
	
	return $http;
}

function bookings_home() {
	global $post, $page_id;
	
	$pageID=isset($_REQUEST['bookingspid']) ? $_REQUEST['bookingspid'] : $page_id;
	
	if (get_option('permalink_structure')) {
		$homePage=get_option('home');
		$wordpressPageName=get_permalink($pageID);
		$wordpressPageName=str_replace($homePage, "", $wordpressPageName);
		$home=$homePage . $wordpressPageName;
		if (substr($home, -1) != '/') $home.='/';
		$home.='?';
	} else {
		$home=get_option('home') . '/?page_id=' . $pageID . '&';
	}
	
	return $home;
}

function bookings_init() {
	global $wp_version, $bookingsScriptsLoaded;
	
	$bookingsScriptsLoaded=false;
	
	ob_start();
	session_start();
	// FIXME: check this
	if (!is_admin() && isset($_REQUEST['zfaces']) && ($_REQUEST['zfaces'] == 'wp-user-profile')) {
		$userid=email_exists($_REQUEST['email']);
		if ($userid) {
			header('Location:' . home_url() . '/?author=' . $userid);
			die();
		}
	}
	if (is_admin()) {
		if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'bookings') && get_option('bookings_version')) {
			bookings_output('saas_check');
			// global $bookings;var_dump($bookings);die();
		}
	}
	wp_enqueue_script('jquery');
	if (!is_admin()) {
		$bookingsScriptsLoaded=true;
		wp_enqueue_script(array('jquery-ui-core','jquery-ui-dialog','jquery-ui-datepicker'));
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/flick/jquery-ui.css');
	}
}

function bookings_log($type=0, $msg='', $filename="", $linenum=0) {
	if (get_option('bookings_debug')) {
		if (is_array($msg)) $msg=print_r($msg, true);
		$v=get_option('bookings_log');
		if (!is_array($v)) $v=array();
		array_unshift($v, array(time(),$type,$msg));
		update_option('bookings_log', $v);
	}
}

function bookings_url($endpoint=true) { // URL end point for web services stored on Zingiri servers
	global $bookingsRegions;
	$r=get_option('bookings_region');
	$url='http://' . $bookingsRegions[$r][1];
	if ($endpoint) $url.='index.php';
	return $url;
}

function bookings_footer() {
	global $bookings, $bookingsScriptsLoaded;
	$ext='.css';
	
	if (isset($bookings['output']['template']) && file_exists(dirname(__FILE__) . '/css/templates/' . $bookings['output']['template'] . $ext)) echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/templates/' . $bookings['output']['template'] . $ext . '" media="screen" />';
	if (isset($bookings['output']['calendar']) && file_exists(dirname(__FILE__) . '/css/calendars/' . $bookings['output']['calendar'] . $ext)) echo '<link rel="stylesheet" type="text/css" href="' . BOOKINGS_URL . 'css/calendars/' . $bookings['output']['calendar'] . $ext . '" media="screen" />';
	
	if (!$bookingsScriptsLoaded && isset($bookings['output']['template'])) {
		wp_enqueue_script(array('jquery-ui-core','jquery-ui-dialog','jquery-ui-datepicker'));
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/flick/jquery-ui.css');
	}
	if (get_option('bookings_css')) echo '<style type="text/css">' . get_option('bookings_css') . '</style>';
}

function bookings_version($tag='Stable tag') {
	$trunk_readme=file(dirname(__FILE__) . '/readme.txt');
	foreach ($trunk_readme as $i => $line)
		if (substr_count($line, $tag . ': ') > 0) return trim(substr($line, strpos($line, $tag . ': ') + strlen($tag) + 2));
	return NULL;
}

function bookings_ajax_frontend_callback() {
	define('BOOKINGS_AJAX_ORIGIN', "f");
	$pg=isset($_REQUEST['zfaces']) ? $_REQUEST['zfaces'] : 'ajax';
	bookings_output();
	die();
}

function bookings_cdn($sub='') {
	if (defined('BOOKINGS_CDN')) return BOOKINGS_CDN;
	else {
		$s=(zingiri::isSecure() ? 'https://d173498e4e66d414ff74-516be1fc79a87be931cfbe73f8cfa194.ssl.cf1.rackcdn.com' : 'http://cdn.zingiri.net') . '/bookings/v5/' . ($sub ? $sub . '/' : '');
		return $s;
	}
}

