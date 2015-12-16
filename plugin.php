<?php

/*
Plugin Name: Object Cache Off Alert
Plugin URI: https://github.com/ashfame/object-cache-off-alert/
Description: Send email alerts to developer when object caching is turned off on the site. Require functioning of WP cron + wp_mail()
Author: Ashfame
Version: 0.1
Author URI: http://ashfame.com/
*/

class Ashfame_Object_Cache_Off_Alert {

	private static $instance;
	private $alert_snooze_time;
	private $emails;

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'object_cache_off_inspection_time', array( $this, 'inspection' ) );
	}

	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function activation() {
		wp_schedule_event( time(), 'hourly', 'object_cache_off_inspection_time' );
	}

	public function deactivation() {
		wp_clear_scheduled_hook( 'object_cache_off_inspection_time' );
	}

	public function init() {
		$this->alert_snooze_time = apply_filters( 'object_cache_off_alert_snooze', HOUR_IN_SECONDS );
		$this->emails = get_option( 'object_cache_off_alert_emails' );
	}

	public function inspection() {
		if ( file_exists( trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php' ) ) {
			return;
		}

		if ( empty( $this->emails ) ) {
			return;
		}

		$last_alert_timestamp = get_option( 'object_cache_off_last_alert', 0 );

		if ( time() - $last_alert_timestamp >= $this->alert_snooze_time ) {

			wp_mail(
				$this->emails,
				apply_filters( 'object_cache_off_alert_email_subject', 'Object caching is turned off on ' . get_bloginfo( 'name' ) ),
				'Hourly emails will be sent unless object caching is turned back on. Deactivate the "Object Cache Off Alert" plugin if you don\'t want these emails.'
			);

			update_option( 'object_cache_off_last_alert', time(), true );

		}
	}
}

Ashfame_Object_Cache_Off_Alert::getInstance();