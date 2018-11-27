<?php
namespace coderkevin\TeamTimeLog;

/**
 * Add profile field for timeclock PIN.
 * 
 * @package   TeamTimeLog
 * @author    coderkevin
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+ 
 */

defined( 'ABSPATH' ) or die();

include_once dirname( __FILE__ ) . '/admin-user-profile-fieldset.php';

class Admin_Profile {
	public function init() {
		add_action( 'show_user_profile', array( $this, 'add_pin_field' ) );
		add_action( 'edit_user_profile', array( $this, 'add_pin_field' ) );

		add_action( 'personal_options_update', array( $this, 'save_pin_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_pin_field' ) );
	}

	public function add_pin_field( $user ) {
		if ( ! user_can( $user, 'publish_posts' ) ) {
			// User must be able to publish posts to make time entries.
			return;
		}

		$user_id = $user->ID;
		if ( wp_get_current_user()->ID === $user_id || current_user_can( 'edit_user' ) ) {
			$pin_hash = get_user_meta( $user_id, 'team_time_log_pin', true );
			$has_pin = $pin_hash !== '';

			admin_profile_fieldset( $has_pin );
		}
	}

	public function save_pin_field( $user_id ) {
		if ( ! current_user_can( 'publish_posts' ) ) {
			// User must be able to publish posts to make time entries.
			return;
		}

		if ( wp_get_current_user()->ID === $user_id || current_user_can( 'edit_user' ) ) {
			// TODO: Validate raw pin.
			$raw_pin = $_POST[ 'time-clock-pin' ];

			if ( $raw_pin ) {
				$pin_hash = wp_hash_password( $raw_pin );
				update_user_meta( $user_id, 'team_time_log_pin', $pin_hash );
			}
		}
	}
}

$admin_profile = new Admin_Profile();
$admin_profile->init();
