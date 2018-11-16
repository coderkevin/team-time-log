<?php
namespace coderkevin\TeamTimeLog;

/**
 * Handle actions and form submits for time-clock page.
 * 
 * @package   TeamTimeLog
 * @author    coderkevin
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+ 
 */

defined( 'ABSPATH' ) or die();

class Timeclock {
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'process_post' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'enqueue_user_data' ] );
		add_action( 'team-time-log_user_options', [ $this, 'render_user_options' ] );
	}

	public function enqueue_scripts() {
		if ( is_page( 'time-clock' ) ) {
			wp_enqueue_script(
				'timeclock-form', 
				plugin_dir_url( __FILE__ ) . '../js/timeclock-form.js',
				[ 'jquery' ],
				time(), // TODO: use filemtime
				true
			);
		}
	}

	public function render_user_options() {
		$user_query = new \WP_User_Query( [ 'role__in' => [ 'Administrator', 'Editor', 'Author'] ] );
		$users = $user_query->get_results();

		if ( ! empty( $users ) ) {
			foreach( $users as $user ) {
				$id = $user->ID;
				$name = esc_html( $user->data->display_name );
				echo '<option value="' . $id . '">' . $name . '</option>';
			}
		}
	}

	public function enqueue_user_data() {
		$user_query = new \WP_User_Query( [ 'role__in' => [ 'Administrator', 'Editor', 'Author'] ] );
		$users = $user_query->get_results();
		$user_info = [];

		if ( ! empty( $users ) ) {
			foreach( $users as $user ) {
				$user_id = $user->ID;
				$user_info[ $user_id ] = [
					'clocked_in' => is_clocked_in( $user_id ),
				];
			}
		}

		wp_add_inline_script(
			'timeclock-form',
			'var timeclock_user_info =' . json_encode( $user_info )
		);
	}

	public function process_post() {
		if ( ! empty( $_POST ) ) {
			$this->verify_unauthenticated_timeclock_post();

			switch( $_POST['action'] ) {
				case 'clock_in':
					return $this->clock_in();
				case 'clock_out':
					return $this->clock_out();
				default:
					error_log( 'timeclock: Unrecognized action: ' . $_POST['action'] );
					die(); // Possible security issue, give no information to user.
			}
		}
	}

	public function clock_in() {
		$user_id = $_POST['user-select'];

		$postarr = [
			'post_type' => 'time-log-entry',
			'post_author' => $user_id,
			'post_status' => 'publish',
		];

		return wp_insert_post( $postarr );
	}

	public function clock_out() {
		$user_id = $_POST['user-select'];
		$entry = get_current_time_entry( $user_id );

		if ( $entry ) {
			return wp_update_post( $entry );
		}
		wp_die( _( 'User is not clocked in.', 'team-time-log' ) );
	}

	private function verify_unauthenticated_timeclock_post() {
		$user = get_user_by( 'ID', $_POST['user-select'] );

		if ( ! $user->has_cap( 'publish_posts' ) ) {
			wp_die( _( 'Insufficient permissions for user', 'team-time-log' ) );
		}

		$nonce_verified = isset( $_POST[ 'timeclock-form-submit' ] ) &&
			wp_verify_nonce( $_POST[ 'timeclock-form-submit' ], 'timeclock_in_or_out');
		if ( ! $nonce_verified ) {
			wp_nonce_ays( 'timeclock_in_or_out' );
		}

		$pin_hash = get_user_meta( $user->ID, 'team_time_log_pin', true );
		$pin_verified = wp_check_password( $_POST[ 'user-pin' ], $pin_hash, $user->ID );
		if ( ! $pin_verified ) {
			wp_die( _( 'Incorrect PIN', 'team-time-log' ) );
		}
	}
}

$timeclock = new Timeclock();
$timeclock->init();
