<?php
namespace coderkevin\TeamTimeLog;

/**
 * Time Clock Theme Loader
 * 
 * This loads Theme elements for a custom Timeclock page.
 * It can be overridden by a actual theme supporting 'team-time-log'
 * 
 * @package   TeamTimeTracker
 * @author    Kevin Killingsworth
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+
 */

defined( 'ABSPATH' ) or die();

class Timeclock_Theme_Loader {
	protected $page_templates = [
		'time-clock' => 'timeclock-template.php',
	];

	protected $styles = [
		'time-clock' => 'timeclock.css',
	];

	public function init() {
		$has_theme_support = current_theme_supports( 'team-time-log' );

		if ( ! $has_theme_support ) {
			add_filter( 'template_include', [ $this, 'view_template' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 11 );
		}
	}

	public function view_template( $template ) {
		if ( $this->is_my_page() ) {
			global $post;
			$page_template = $this->page_templates[ $post->post_name ];
			$file = plugin_dir_path( __FILE__ ) . $page_template;

			if ( file_exists( $file ) ) {
				return $file;
			} else {
				echo $file;
			}
		}
		return $template;
	}

	public function enqueue_styles() {
		if ( $this->is_my_page() ) {
			foreach( $this->styles as $handle => $src ) {
				$file = plugin_dir_url( __FILE__ ) . $src;
				wp_enqueue_style( $handle, $file, false, time() /* TODO: use filemtime */ );
			}
		}
	}

	private function is_my_page() {
		global $post;
		return ! is_search() && $post && array_key_exists( $post->post_name, $this->page_templates );
	}
}

$timeclock_theme_loader = new Timeclock_Theme_Loader();
$timeclock_theme_loader->init();
