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
		add_action( 'team-time-log_timeclock_header', [ $this, 'check_permissions' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'process_post' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'enqueue_user_data' ] );
		add_action( 'team-time-log_user_select', [ $this, 'render_user_select' ] );
		add_action( 'team-time-log_user_pin', [ $this, 'render_user_pin' ] );
		add_action( 'team-time-log_keypad', [ $this, 'render_keypad' ] );
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

	public function render_user_select() {
		if ( is_user_logged_in() ) {
			$this->render_single_user_select();
		} else {
			$this->render_all_user_select();
		}
	}

	private function render_single_user_select() {
		$user = wp_get_current_user();
		$name = esc_html( $user->data->display_name );
		echo( '<label id="timeclock-single-user-select">' . $name . '</label>' );
		echo( '<input id="timeclock-user-select" name="user-select" type="hidden" value="' . $user->ID . '" />' );
	}

	private function render_all_user_select() {
		$user_query = new \WP_User_Query( [ 'role__in' => [ 'Administrator', 'Editor', 'Author'] ] );
		$users = $user_query->get_results();

		if ( ! empty( $users ) ) {
			echo( '<fieldset>' );
			echo(   '<select id="timeclock-user-select" name="user-select">' );
			echo( '    <option value="" disabled="disabled" selected="selected">' );
			echo( '      ' . __( 'Select your name', 'team-time-log' ) );
			echo( '    </option>' );

			foreach( $users as $user ) {
				$id = $user->ID;
				$name = esc_html( $user->data->display_name );
				echo '    <option value="' . $id . '">' . $name . '</option>';
			}
			echo( '  </select>' );
			echo( '</fieldset>' );
		}
	}

	public function render_user_pin() {
		if ( is_user_logged_in() ) {
			// Authenticated users don't need a pin.
			return;
		}

		echo( '<fieldset>' );
		echo(
			'  <input id="user-pin" ' .
				'type="password" ' .
				'name="user-pin" ' .
				'size="4" ' .
				'minlength="4" ' .
				'maxlength="4" ' .
				'inputmode="numeric" ' .
				'autocomplete="false" ' .
			'/>'
		);
		echo( '</fieldset>' );
	}

	public function render_keypad() {
		if ( is_user_logged_in() ) {
			// Authenticated users don't need a pin.
			return;
		}

		$button_clear = [ 'value' => 'clear', 'text' => __( 'CLR', 'team-time-log' ) ];
		$button_backspace = [ 'value' => 'backspace', 'text' => __( '<', 'team-time-log' ) ];
		$buttons = [
			'1', '2', '3',
			'4', '5', '6',
			'7', '8', '9',
			$button_clear, '0', $button_backspace
		];

		echo( '<fieldset>' );
		echo( '  <div id="timeclock-keypad">' );

		foreach( $buttons as $button ) {
			$value = is_array( $button ) ? $button['value'] : $button;
			$text = is_array( $button ) ? $button['text'] : $button;

			echo(
				'    <button class="keypad-button" value="' . $value . '">' .
				$text .
				'</button>'
			);
		}

		echo( '  </div>' );
		echo( '</fieldset>' );
	}

	public function enqueue_user_data() {
		if ( is_user_logged_in() ) {
			$users = [ wp_get_current_user() ];
		} else {
			$user_query = new \WP_User_Query( [ 'role__in' => [ 'Administrator', 'Editor', 'Author'] ] );
			$users = $user_query->get_results();
		}

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

	public function check_permissions() {
		if ( is_user_logged_in() ) {
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( _( 'Insufficient permission', 'team-time-log', 403 ) );
			}
		} else {
			// TODO: Only allow unauthenticated use for confirmed IP addresses.
			error_log( 'unauthenticated view of time-clock' );
		}
	}

	public function process_post() {
		if ( ! empty( $_POST ) ) {
			if ( is_user_logged_in() ) {
				$this->verify_authenticated_timeclock_post();
			} else {
				$this->verify_unauthenticated_timeclock_post();
			}

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

	private function verify_authenticated_timeclock_post() {
		$selected_user_id = intval( $_POST['user-select'] );
		$user = wp_get_current_user();

		if ( $selected_user_id !== $user->ID ) {
			wp_die( _( 'Insufficient permissions', 'team-time-log', 403 ) );
		}
	}

	private function verify_unauthenticated_timeclock_post() {
		$user = get_user_by( 'ID', $_POST['user-select'] );

		if ( ! $user->has_cap( 'publish_posts' ) ) {
			wp_die( _( 'Insufficient permissions for user', 'team-time-log', 403 ) );
		}

		$nonce_verified = isset( $_POST[ 'timeclock-form-submit' ] ) &&
			wp_verify_nonce( $_POST[ 'timeclock-form-submit' ], 'timeclock_in_or_out');
		if ( ! $nonce_verified ) {
			wp_nonce_ays( 'timeclock_in_or_out' );
		}

		$pin_hash = get_user_meta( $user->ID, 'team_time_log_pin', true );
		$pin_verified = wp_check_password( $_POST[ 'user-pin' ], $pin_hash, $user->ID );
		if ( ! $pin_verified ) {
			wp_die( _( 'Incorrect PIN', 'team-time-log', 403 ) );
		}
	}
}

$timeclock = new Timeclock();
$timeclock->init();
