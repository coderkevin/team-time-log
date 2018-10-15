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
include_once 'includes/entry_metabox.php';
include_once 'includes/admin/admin_profile.php';
include_once 'includes/timeclock.php';
include_once 'timeclock-theme/timeclock_theme_loader.php';

class TeamTimeLog {
	public function __construct() {
		$this->plugin_dir = plugin_dir_path( __FILE__ );
	}

	public function init() {
		register_activation_hook( __FILE__, [ $this, 'activatePlugin' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivatePlugin' ] );
		add_action( 'init', [ $this, 'registerPostType' ], 10, 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminStyles' ], 10 );
		add_filter( 'wp_insert_post_data', [ $this, 'setPostData' ], 10, 2 );
	}

	public function activatePlugin() {
		$timeclock_slug = esc_sql( _x( 'time-clock', 'Page slug', 'team-time-log' ) );
		$timeclock_title = _x( 'Time Clock', 'Page title', 'team-time-log' );

		create_page( $timeclock_slug, 'team-time-log_timeclock_page_id', $timeclock_title );
	}

	public function deactivatePlugin() {
		trash_page( 'team-time-log_timeclock_page_id' );
	}

	// TODO: Move out of main plugin file.
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
			'register_meta_box_cb' => [ $this, 'registerMetaBox' ],
		);
	 
		register_post_type( 'time-log-entry', $args );
	}

	// TODO: Move out of main plugin file.
	function registerMetaBox() {
		add_meta_box(
			'team_time_log_entry_times',
			'Clock In/Out',
			[ $this, 'metaBoxCallback' ],
			'time-log-entry',
			'normal',
			'default'
		);
	}

	function enqueueAdminScripts() {
		wp_enqueue_script(
			'team-time-log-admin', 
			plugin_dir_url( __FILE__ ) . 'js/team-time-log-admin.js',
			[ 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ],
			time(), // TODO: use filemtime
			true
		);
	}

	function enqueueAdminStyles() {
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
	function metaBoxCallback( $entry ) {
		entry_metabox( $entry );
	}

	// TODO: Move out of main plugin file.
	function setPostData( $data, $postarr ) {
		$id = $postarr['ID'];
		if ( 'time-log-entry' === $data['post_type'] ) {
			$data['post_name'] = get_entry_name( $postarr );
			$data['post_title'] = get_entry_title( $postarr );

			// If we have a valid ID, set the clock in/out dates.
			if ( $id ) {
				// TODO: Use time zone/GMT for this.
				$clock_in_date = inputs_to_datetime(
					$_POST[ 'clock_in_' . $id . '_date' ],
					$_POST[ 'clock_in_' . $id . '_hour' ],
					$_POST[ 'clock_in_' . $id . '_minute' ]
				);
				$clock_in_str = datetime_to_database_string( $clock_in_date );

				$clock_out_date = inputs_to_datetime(
					$_POST[ 'clock_out_' . $id . '_date' ],
					$_POST[ 'clock_out_' . $id . '_hour' ],
					$_POST[ 'clock_out_' . $id . '_minute' ]
				);
				$clock_out_str = datetime_to_database_string( $clock_out_date );

				// TODO: convert local/GMT
				$data['post_date'] = $clock_in_str;
				$data['post_date_gmt'] = $clock_in_str;
				$data['post_modified'] = $clock_out_str;
				$data['post_modified_gmt'] = $clock_out_str;
			}
			return $data;
		}
		return $data;
	}
}

$team_time_log = new TeamTimeLog();
$team_time_log->init();
