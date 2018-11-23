<?php
namespace coderkevin\TeamTimeLog;

/**
 * Utility functions
 * 
 * @package   TeamTimeTracker
 * @author    Kevin Killingsworth
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+
 */

defined( 'ABSPATH' ) or die();

// Entries are consider expired if they're older than 24 hours.
define( 'ENTRY_EXPIRED_TIME', DAY_IN_SECONDS );
define( 'TIMECLOCK_COOKIE', 'team-time-log_timeclock' );
define( 'TIMECLOCK_COOKIE_EXPIRATION', 365 * DAY_IN_SECONDS );
define( 'TIMECLOCK_URL', '/time-clock/' );
define( 'TIMECLOCK_COOKIE_SEED_OPTION', 'timeclock_cookie_seed' );

function get_date_format() {
	return get_option( 'date_format' );
}

function get_time_format() {
	return get_date_format() . ' H:i';
}

function inputs_to_datetime( $date, $hour, $minute ) {
	$str = sprintf( '%s %s:%s', $date, $hour, $minute );
	return \DateTime::createFromFormat( get_time_format(), $str );
}

function datestring_to_datetime( $str, $format = 'Y-m-d H:i:s' ) {
	return \DateTime::createFromFormat( $format, $str );
}

function datetime_to_datestring( $datetime, $format = 'Y-m-d H:i:s' ) {
	return $datetime->format( $format );
}

function is_clocked_in( $user_id ) {
	return get_current_time_entry( $user_id ) !== null;
}

function get_current_time_entry( $user_id ) {
	$query = new \WP_Query( [ 'post_type' => 'time-log-entry', 'author' => $user_id ] );

	// Check this user's time log entries for an active one.
	if ( $query->have_posts() ) {
		foreach( $query->posts as $entry ) {
			if ( is_entry_active( $entry->post_date_gmt, $entry->post_modified_gmt ) ) {
				return $entry;
			}
		}
	}
	return null;
}

function is_entry_active( $clock_in_gmt, $clock_out_gmt ) {
	if ( $clock_in_gmt === $clock_out_gmt ) {
		$time_ago = time() - strtotime( $clock_in_gmt );
		return ENTRY_EXPIRED_TIME > $time_ago;
	}
	return false;
}

function set_cookie_seed() {
	$value = random_int( PHP_INT_MIN, PHP_INT_MAX );
	update_option( TIMECLOCK_COOKIE_SEED_OPTION, $value, true );
}

function set_timeclock_cookie() {
	if ( TIMECLOCK_URL === $_SERVER["REQUEST_URI"] ) {
		$seed = get_option( TIMECLOCK_COOKIE_SEED_OPTION );
		$hash = md5( $seed );

		setcookie(
			TIMECLOCK_COOKIE,
			$hash,
			time() + ( 30 * DAY_IN_SECONDS ),
			TIMECLOCK_URL,
			COOKIE_DOMAIN,
			false,
			true
		);
	}
}

function has_timeclock_cookie() {
	if ( isset( $_COOKIE[ TIMECLOCK_COOKIE ] ) ) {
		$cookie = $_COOKIE[ TIMECLOCK_COOKIE ];
		$seed = get_option( TIMECLOCK_COOKIE_SEED_OPTION );
		$hash = md5( $seed );
		return hash_equals( $hash, $cookie );
	}
	return false;
}

function is_valid_page( $page ) {
	return $page instanceof WP_Post && 'publish' === $page->post_status;
}

function create_page( $slug, $option, $title ) {
	global $wpdb;

	$pages = get_posts( [
		'post_type' => 'page', 
		'post_status' => [ 'publish', 'future', 'draft', 'pending', 'private', 'auto-draft', 'trash' ],
		'post_name__in' => [ $slug, $slug . '__trashed' ]
	] );
	$page = ( 1 === count( $pages ) ? $pages[ 0 ] : null );

	// If there's already a page, just update the option and return it.
	if ( is_valid_page( $page ) ) {
		update_option( $option, $page->ID );
		return $page->ID;
	}

	// If the page exists, but isn't in the right state (e.g. trashed), restore it.
	if ( $page ) {
		wp_update_post( [ 'ID' => $page->ID, 'post_status' => 'publish' ] );
		update_option( $option, $page->ID );
		return $page->ID;
	}

	// Page doesn't exist, so create it.
	$page_data = [
		'post_title'     => $title,
		'post_content'   => '',
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => $slug,
		'comment_status' => 'closed',
	];

	$page_id = wp_insert_post( $page_data );
	update_option( $option, $page_id );
	return $page_id;
}

function trash_page( $option ) {
	$page_id = get_option( $option );

	if ( 0 < $page_id && get_post( $page_id ) ) {
		wp_trash_post( $page_id );
	}
}
