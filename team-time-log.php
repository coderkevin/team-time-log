<?php
/**
 * @package   TeamTimeLog
 * @author    Kevin Killingsworth
 * @copyright 2018 Automattic
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Team Time Log
 * Plugin URI:  https://github.com/coderkevin/team-time-log
 * Description: Time logging for team activities.
 * Version:     0.1.0
 * Author:      Kevin Killingsworth
 * Author URI:  https://coderkevin.com
 * Text Domain: team-time-log
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

class TeamTimeLog {
	public function init() {
		add_action( 'init', [ $this, 'registerPostType' ], 10, 3 );
	}

	function registerPostType() {
		$labels = array(
			'name'                  => _x( 'Time Log Entries', 'Post type general name', 'textdomain' ),
			'singular_name'         => _x( 'Time Log Entry', 'Post type singular name', 'textdomain' ),
			'menu_name'             => _x( 'Time Log', 'Admin Menu text', 'textdomain' ),
			'name_admin_bar'        => _x( 'Time Log Entry', 'Add New on Toolbar', 'textdomain' ),
			'add_new'               => __( 'Add New', 'textdomain' ),
			'add_new_item'          => __( 'Add New Entry', 'textdomain' ),
			'new_item'              => __( 'New Entry', 'textdomain' ),
			'edit_item'             => __( 'Edit Entry', 'textdomain' ),
			'view_item'             => __( 'View Entry', 'textdomain' ),
			'all_items'             => __( 'All Entries', 'textdomain' ),
			'search_items'          => __( 'Search Entries', 'textdomain' ),
			'not_found'             => __( 'No time entries found.', 'textdomain' ),
			'not_found_in_trash'    => __( 'No time entries found in Trash.', 'textdomain' ),
			'filter_items_list'     => _x( 'Filter time entries', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain' ),
			'items_list_navigation' => _x( 'Time entries list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain' ),
			'items_list'            => _x( 'Time entries list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain' ),
		);
	 
		$args = array(
			'labels'             => $labels,
			'description'        => 'Team time log entries for time logged.',
			'public'             => true,
			'hierarchical'       => false,
			'capability_type'    => 'post',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-clock',
			'supports'           => array( 'author' ),
			'delete_with_user'   => true,
		);
	 
		register_post_type( 'time-log-entry', $args );
	}
}

$team_time_log = new TeamTimeLog();
$team_time_log->init();
