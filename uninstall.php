<?php
// Escape out if the file is not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'object_cache_off_last_alert' );
delete_option( 'object_cache_off_alert_emails' );