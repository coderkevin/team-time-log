<?php
namespace coderkevin\TeamTimeLog;

/**
 * @package   TeamTimeLog
 * @author    Kevin Killingsworth
 * @copyright 2018 Kevin Killingsworth
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
defined( 'ABSPATH' ) or die();

include_once 'includes/utils.php';
include_once 'includes/handle_post_data.php';
include_once 'includes/entry-metabox.php';
include_once 'includes/admin/class-admin-user-profile.php';
include_once 'includes/timeclock.php';
include_once 'timeclock-theme/class-timeclock-theme-loader.php';

// TODO: Move admin stuff to another class and only include when needed.
class Team_Time_Log {
	public function __construct() {
		$this->plugin_dir = plugin_dir_path( __FILE__ );
	}

	public function init() {
		register_activation_hook( __FILE__, [ $this, 'activate_plugin' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate_plugin' ] );
		add_action( 'init', [ $this, 'register_post_type' ], 10, 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ], 10 );
		add_filter( 'wp_insert_post_data', 'coderkevin\TeamTimeLog\handle_post_data', 10, 2 );
	}

	public function activate_plugin() {
		$timeclock_slug = esc_sql( _x( 'time-clock', 'Page slug', 'team-time-log' ) );
		$timeclock_title = _x( 'Time Clock', 'Page title', 'team-time-log' );

		create_page( $timeclock_slug, 'team-time-log_timeclock_page_id', $timeclock_title );
	}

	public function deactivate_plugin() {
		trash_page( 'team-time-log_timeclock_page_id' );
	}

	// TODO: Move out of main plugin file.
	function register_post_type() {
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
			'register_meta_box_cb' => [ $this, 'register_meta_box' ],
		);
	 
		register_post_type( 'time-log-entry', $args );
	}

	// TODO: Move out of main plugin file.
	function register_meta_box() {
		add_meta_box(
			'team_time_log_entry_times',
			'Clock In/Out',
			[ $this, 'meta_box_callback' ],
			'time-log-entry',
			'normal',
			'default'
		);
	}

	function enqueue_admin_scripts() {
		wp_enqueue_script(
			'team-time-log-admin', 
			plugin_dir_url( __FILE__ ) . 'js/team-time-log-admin.js',
			[ 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ],
			time(), // TODO: use filemtime
			true
		);
	}

	function enqueue_admin_styles() {
		wp_enqueue_style(
			'team-time-log-admin-jquery-ui',
			plugin_dir_url( __FILE__ ) . 'styles/jquery-ui/jquery-ui.css'
		);
		wp_enqueue_style(
			'team-time-log-admin',
			plugin_dir_url( __FILE__ ) . 'styles/team-time-log-admin.css',
			[ 'team-time-log-admin-jquery-ui' ],
			time() // TODO: use filemtime
		);

	}

	// TODO: Move out of main plugin file.
	function meta_box_callback( $entry ) {
		entry_metabox( $entry );
	}
}

$team_time_log = new Team_Time_Log();
$team_time_log->init();
