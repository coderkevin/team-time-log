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
		add_action( 'init', [ $this, 'set_cookie' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'check_permissions' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'process_post' ] );
		add_action( 'team-time-log_timeclock_header', [ $this, 'enqueue_user_data' ] );
		add_action( 'team-time-log_user_select', [ $this, 'render_user_select' ] );
		add_action( 'team-time-log_user_pin', [ $this, 'render_user_pin' ] );
		add_action( 'team-time-log_keypad', [ $this, 'render_keypad' ] );
		add_action( 'team-time-log_summary', [ $this, 'render_summary' ] );
	}

	public function set_cookie() {
		if ( current_user_can( 'edit_others_posts' ) ) {
			set_timeclock_cookie();
		}
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
			wp_enqueue_script(
				'js-toast',
				plugin_dir_url( __FILE__ ) . '../js/js-toast/toast.js',
				[],
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
		$status = is_clocked_in( $user->ID ) ? _( '(clocked in)', 'team-time-log' ) : '';
		$name = esc_html( $user->data->display_name ) . ' ' . $status;
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
				$pin_hash = get_user_meta( $id, 'team_time_log_pin', true );

				if ( isset( $pin_hash ) && strlen( $pin_hash ) > 0 ) {
					$status = is_clocked_in( $id ) ? _( '[in]', 'team-time-log' ) : '';
					$name = esc_html( $user->data->display_name ) . ' ' . $status;
					echo '    <option value="' . $id . '">' . $name . '</option>';
				}
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

	public function render_summary() {
		if ( ! is_user_logged_in() ) {
			// Unauthenticated timeclocks don't require a summary.
			echo( '<input type="hidden" name="summary" value="' . __( 'Shop Time', 'team-time-log' ) . '" />' );
			return;
		}
		$user = wp_get_current_user();
		$clocked_in = is_clocked_in( $user->ID );
		$summary = $clocked_in ? get_current_time_entry( $user->ID )->post_content : '';

		$question = ! $clocked_in ?
			__( 'What are you going to accomplish today?', 'team-time-log' ) :
			__( 'What did you accomplish today?', 'team-time-log' );
		$placeholder = __( 'Examples:&#10;Visit businesses for sponsorship.&#10;Presentation at Chamber of Commerce.', 'team-time-log' );

		echo( '<fieldset>' );
		echo( '  <label>' . $question . '</label>' );
		echo( '  <textarea name="summary" id="timeclock-summary" rows="5" cols="60" placeholder="' . $placeholder . '">' );
		echo( $summary );
		echo( '</textarea>' );
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

		$script = 'var timeclock_user_info = ' . json_encode( $user_info ) . ';';

		if ( isset( $this->notification ) ) {
			$script = $script . ' var timeclock_notification = ' . json_encode( $this->notification ) . ';';
		}

		wp_add_inline_script( 'timeclock-form', $script );
	}

	public function set_notification( $message, $isError = false ) {
		$this->notification = [
			'message' => $message,
			'isError' => $isError,
		];
	}

	public function check_permissions() {
		if ( is_user_logged_in() ) {
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( _( 'Insufficient permission', 'team-time-log', 403 ) );
			}
		} else if ( ! has_timeclock_cookie() ) {
			wp_die( _( 'Unauthorized browser', 'team-time-log', 403 ) );
		}
	}

	public function process_post() {
		if ( ! empty( $_POST ) ) {
			if ( is_user_logged_in() ) {
				$this->verify_authenticated_timeclock_post();
			} else if ( ! $this->verify_unauthenticated_timeclock_post() ) {
				return false;
			}

			switch( $_POST['action'] ) {
				case 'clock_in':
					return $this->clock_in();
				case 'clock_out':
					return $this->clock_out();
				default:
					error_log( 'team-time-log: Unrecognized action: ' . $_POST['action'] );
					die(); // Possible security issue, give no information to user.
			}
		}
	}

	public function clock_in() {
		$user_id = $_POST['user-select'];
		$user = get_user_by( 'ID', $user_id );
		$name = $user->data->display_name;
		$summary = $_POST['summary'];

		$postarr = [
			'post_type' => 'time-log-entry',
			'post_author' => $user_id,
			'post_status' => 'publish',
			'post_content' => $summary,
		];

		if ( wp_insert_post( $postarr ) > 0 ) {
			$this->set_notification( $name . _( ' has clocked in' , 'team-time-log' ) );
			return true;
		} else {
			$this->set_notification( _( 'Error on clock in' , 'team-time-log' ), true );
			error_log( 'team-time-log: Error on user ' . $user_id . ' clock in (wp_insert_post)' );
			return false;
		}
	}

	public function clock_out() {
		$user_id = $_POST['user-select'];
		$user = get_user_by( 'ID', $user_id );
		$name = $user->data->display_name;
		$summary = $_POST['summary'];
		$entry = get_current_time_entry( $user_id );

		if ( $entry ) {
			$data = [
				'ID' => $entry->ID,
				'post_content' => $summary,
			];

			if ( 0 === strlen( $summary ) ) {
				$this->set_notification( __( 'Summary is mandatory', 'team-time-log' ), true );
				return false;
			}

			if ( wp_update_post( $data ) > 0 ) {
				$this->set_notification( $name . _( ' has clocked out' , 'team-time-log' ) );
				return true;
			} else {
				$this->set_notification( _( 'Error on clock out' , 'team-time-log' ), true );
				error_log( 'team-time_log: Error on user ' . $user_id . ' clock in (wp_update_post)' );
			}
		}
		$this->set_notification( $name . _( ' was not clocked in' , 'team-time-log' ), true );
		return false;
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
			$this->set_notification( _( 'Incorrect PIN' , 'team-time-log' ), true );
			return false;
		}

		return true;
	}
}

$timeclock = new Timeclock();
$timeclock->init();
