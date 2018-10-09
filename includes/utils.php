<?php
namespace coderkevin\TeamTimeLog;

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
	$author = get_user_by( 'id', $data['post_author'] );
	return $data['post_date'] . ' - ' . $author->data->display_name;
}

function get_entry_name( $data ) {
	$author = get_user_by( 'id', $data['post_author'] );
	return 'time-entry-' . $author->data->user_login . '-' . $data['ID'];
}
