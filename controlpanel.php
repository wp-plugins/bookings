<?php
function bookings_options() {
	global $bookings_name,$bookings_shortname,$cc_login_type,$current_user;
	$bookings_name = "Bookings";
	$bookings_shortname = "bookings";

	$bookings_options[] = array(  "name" => "Settings",
            "type" => "heading",
			"desc" => "This section customizes the way the Bookings plugin works.");
	$bookings_options[] = array("name" => "API Key",
			"desc" => 'This is your API key, it is uniquely linked to your web site, make sure to keep it in a safe place.',
			"id" => $bookings_shortname."_key",
			"type" => "text");
	$bookings_options[] = array(	"name" => "Debug Mode",
			"desc" => "If you have problems with the plugin, activate the debug mode to generate a debug log for our support team",
			"id" => $bookings_shortname."_debug",
			"type" => "checkbox");

	return $bookings_options;
}

function bookings_add_admin() {

	global $bookings_name, $bookings_shortname;

	$bookings_options=bookings_options();

	if (isset($_GET['page']) && ($_GET['page'] == "cc-ce-bridge-cp")) {
		
		if ( isset($_REQUEST['action']) && 'install' == $_REQUEST['action'] ) {
			delete_option('bookings_log');
			foreach ($bookings_options as $value) {
				update_option( $value['id'], $_REQUEST[ $value['id'] ] );
			}

			foreach ($bookings_options as $value) {
				if( isset( $_REQUEST[ $value['id'] ] ) ) {
					update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
				} else { delete_option( $value['id'] );
				}
			}
			bookings_install();
			if (function_exists('bookings_sso_update')) bookings_sso_update();
			header("Location: options-general.php?page=cc-ce-bridge-cp&installed=true");
			die;
		}
	}

	add_menu_page($bookings_name, $bookings_name, 'administrator', 'bookings','bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Setup', 'Setup', 'administrator', 'bookings', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Schedules', 'Schedules', 'administrator', 'bookings&zb=admin&tool=schedules', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Resources', 'Resources', 'administrator', 'bookings&zb=admin&tool=resources', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Blackouts', 'Blackouts', 'administrator', 'bookings&zb=blackouts', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - List', 'Bookings List', 'administrator', 'bookings&zb=admin&tool=reservations', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Calendar', 'Bookings Calendar', 'administrator', 'bookings&zb=schedule', 'bookings_main');
	add_submenu_page('bookings', $bookings_name.' - Search Bookings', 'Search Bookings', 'administrator', 'bookings&zb=usage', 'bookings_main');
}

function bookings_main() {
	global $bookings;
	
	if (!isset($_GET['zb'])) return bookings_admin();

	echo '<div class="wrap">';
	echo '<div id="bookings" style="position:relative;float:left;width:75%">';
	if (isset($bookings['output']['messages']) && (count($bookings['output']['messages']) > 0)) {
		echo '<div class="error">';
		foreach ($bookings['output']['messages'] as $msg) {
			echo $msg.'<br />';
		}
		echo '</div>';
	} 
	if (isset($bookings['output']['body'])) echo $bookings['output']['body'];
	echo '</div>';
	require(dirname(__FILE__).'/includes/support-us.inc.php');
	zing_support_us('bookings','bookings','cc-ce-bridge-cp',BOOKINGS_VERSION);
	echo '</div>';
}

function bookings_admin() {

	global $bookings_name, $bookings_shortname;

	$controlpanelOptions=bookings_options();

	if ( isset($_REQUEST['install']) ) echo '<div id="message" class="updated fade"><p><strong>'.$bookings_name.' settings updated.</strong></p></div>';
	if ( isset($_REQUEST['error']) ) echo '<div id="message" class="updated fade"><p>The following error occured: <strong>'.$_REQUEST['error'].'</strong></p></div>';
	
	?>
<div class="wrap">
<div id="cc-left" style="position:relative;float:left;width:80%">
<h2><b><?php echo $bookings_name; ?></b></h2>

	<?php
	$bookings_version=get_option("bookings_version");
	$submit='Update';
	?>
<form method="post">

<?php require(dirname(__FILE__).'/includes/cpedit.inc.php')?>

<p class="submit"><input name="install" type="submit" value="<?php echo $submit;?>" /> <input
	type="hidden" name="action" value="install"
/></p>
</form>
<hr />
<?php  
	if ($bookings_version && get_option('bookings_debug')) {
		echo '<h2 style="color: green;">Debug log</h2>';
		echo '<textarea rows=10 cols=80>';
		$r=get_option('bookings_log');
		if ($r) {
			$v=$r;
			foreach ($v as $m) {
				echo date('H:i:s',$m[0]).' '.$m[1].chr(13).chr(10);
				echo $m[2].chr(13).chr(10);
			}
		}
		echo '</textarea><hr />';
	}
?>

</div> <!-- end cc-left -->
<?php
	require(dirname(__FILE__).'/includes/support-us.inc.php');
	zing_support_us('bookings','bookings','bookings',BOOKINGS_VERSION);
}
add_action('admin_menu', 'bookings_add_admin'); ?>