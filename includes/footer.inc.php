<?php

function cc_footers($nodisplay='') {
	$bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
	if ( $bail_out ) return $footer;

	//Please contact us if you wish to remove the Zingiri logo in the footer
	$msg='<center style="margin-top:0px;font-size:small">';
	$msg.='Bookings by <a href="http://www.zingiri.net" target="_blank"	>Zingiri</a>';
	$msg.='</center>';
	$cc_footer=true;
	if ($nodisplay===true) return $msg;
	else echo $msg;

}
