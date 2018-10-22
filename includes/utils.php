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

function database_string_to_datetime( $str, $format = 'Y-m-d H:i:s' ) {
	return \DateTime::createFromFormat( $format, $str );
}

function datetime_to_database_string( $datetime, $format = 'Y-m-d H:i:s' ) {
	return $datetime->format( $format );
}

function get_entry_title( $data ) {
	$author_name = 'unknown';
	$clock_in = datetime_to_database_string( new \DateTime() );

	if ( array_key_exists( 'post_author', $data ) ) {
		$author = get_user_by( 'id', $data['post_author'] );
		$author_name = $author->data->display_name;
	}
	if ( array_key_exists( 'post_date', $data ) ) {
		$clock_in = $data['post_date'];
	}
	return $clock_in . ' - ' . $author_name;
}

function get_entry_name( $data ) {
	$author_login = 'unknown';
	$entry_id = 0;

	if ( array_key_exists( 'post_author', $data ) ) {
		$author = get_user_by( 'id', $data['post_author'] );
		$author_login = $author->data->user_login;
	}
	if ( array_key_exists( 'ID', $data ) ) {
		$entry_id = $data['ID'];
	}
	return 'time-entry-' . $author_login . '-' . $entry_id;
}

function is_clocked_in( $user_id ) {
	$query = new \WP_Query( [ 'post_type' => 'time-log-entry', 'author' => $user_id ] );

	// Check this user's time log entries for an active one.
	if ( $query->have_posts() ) {
		foreach( $query->posts as $entry ) {
			if ( is_entry_active( $entry ) ) {
				return true;
			}
		}
	}
	return false;
}

function is_entry_active( $entry ) {
	return $entry->post_date_gmt === $entry->post_modified_gmt;
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
		if ( is_valid_page( $page ) ) {
			update_option( $option, $page->ID );
			return $page->ID;
		}
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
