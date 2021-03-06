<?php
namespace coderkevin\TeamTimeLog;

/**
 * Handles post inserts and updates via HTTP POST.
 * 
 * @package   TeamTimeTracker
 * @author    Kevin Killingsworth
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+
 */

defined( 'ABSPATH' ) or die();

function handle_post_data( $data, $postarr ) {
	if ( 'time-log-entry' === $data['post_type'] && isset( $_POST['action'] ) ) {
		switch( $_POST['action'] ) {
			case 'editpost':
				return handle_admin_edit( $data, $postarr, $_POST );
			case 'clock_in':
				return handle_clock_in( $data, $postarr );
			case 'clock_out':
				return handle_clock_out( $data, $postarr );
			default:
				error_log( 'team-time-log: Unrecognized action: ' . $params['action'] );
				die(); // Possible security issue, give no information to user.
		}
	}
	return $data;
}

function handle_admin_edit( $data, $postarr, $params ) {
	// TODO: Check to verify user permissions for editing this entry?
	$id = $postarr['ID'];
	$author_id = $postarr['post_author'];

	$clock_in_date = inputs_to_datetime(
		$params[ 'clock_in_' . $id . '_date' ],
		$params[ 'clock_in_' . $id . '_hour' ],
		$params[ 'clock_in_' . $id . '_minute' ]
	);
	$clock_in_gmt = get_gmt_from_date( datetime_to_datestring( $clock_in_date ) );

	$clock_out_date = inputs_to_datetime(
		$params[ 'clock_out_' . $id . '_date' ],
		$params[ 'clock_out_' . $id . '_hour' ],
		$params[ 'clock_out_' . $id . '_minute' ]
	);
	$clock_out_gmt = get_gmt_from_date( datetime_to_datestring( $clock_out_date ) );
	$summary = isset( $params['summary'] ) ? $params['summary'] : '';

	return set_entry_data( $data, $author_id, $clock_in_gmt, $clock_out_gmt, $summary );
}

function handle_clock_in( $data, $postarr ) {
	$author_id = $data['post_author'];
	$clock_in_gmt = current_time( 'Y-m-d H:i:s', true );
	$clock_out_gmt = $clock_in_gmt;
	$summary = isset( $data['post_content'] ) ? $data['post_content'] : '';
	return set_entry_data( $data, $author_id, $clock_in_gmt, $clock_out_gmt, $summary );
}

function handle_clock_out( $data, $postarr ) {
	$author_id = $data['post_author'];
	$clock_in_gmt = $data['post_date_gmt'];
	$clock_out_gmt = current_time( 'Y-m-d H:i:s', true );
	$summary = isset( $data['post_content'] ) ? $data['post_content'] : '';
	return set_entry_data( $data, $author_id, $clock_in_gmt, $clock_out_gmt, $summary );
}

function set_entry_data( $data, $author_id, $clock_in_gmt, $clock_out_gmt, $summary = '' ) {
	$author_login = 'unknown';
	$author_name = 'unknown';
	$entry_id = 0;

	if ( array_key_exists( 'ID', $data ) ) {
		$entry_id = $data['ID'];
	}

	if ( $author_id ) {
		$author = get_user_by( 'id', $author_id );
		$author_login = $author->data->user_login;
		$author_name = $author->data->display_name;

	} elseif ( $data['post_author'] ) {
		$author = get_user_by( 'id', $data['post_author'] );
		$author_login = $author->data->user_login;
		$author_name = $author->data->display_name;
	}

	$data['post_author'] = $author_id;
	$data['post_name'] = 'time-entry-' . $author_login . '-' . $entry_id;
	$data['post_title'] = calculate_entry_title( $author_name, $clock_in_gmt, $clock_out_gmt );
	$data['post_date'] = get_date_from_gmt( $clock_in_gmt );
	$data['post_date_gmt'] = $clock_in_gmt;
	$data['post_modified'] = get_date_from_gmt( $clock_out_gmt );
	$data['post_modified_gmt'] = $clock_out_gmt;
	$data['post_content'] = $summary;
	return $data;
}

function calculate_entry_title( $author_name, $clock_in_gmt, $clock_out_gmt ) {
	$time_in = strtotime( $clock_in_gmt );
	$time_out = strtotime( $clock_out_gmt );
	$date = date( 'M jS, Y', $time_in );

	if ( $time_in === $time_out ) {
		$time_diff = __( 'not clocked out', 'team-time-log' );
	} else {
		$time_diff = human_time_diff( $time_in, $time_out );
	}

	return $author_name . ': ' . $date . ' - ' . $time_diff;
}
