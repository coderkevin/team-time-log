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

class TimeclockThemeLoader {
	protected $page_templates = [
		'time-clock' => 'timeclock-template.php',
	];

	protected $styles = [
		'time-clock' => 'timeclock.css',
	];

	public function init() {
		$hasThemeSupport = current_theme_supports( 'team-time-log' );

		if ( ! $hasThemeSupport ) {
			add_filter( 'template_include', [ $this, 'viewTemplate' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueueStyles' ], 11 );
		}
	}

	public function viewTemplate( $template ) {
		if ( is_search() ) {
			return $template;
		}

		global $post;

		if ( ! $post ) {
			return $template;
		}

		if ( array_key_exists( $post->post_name, $this->page_templates ) ) {
			$page_template = $this->page_templates[ $post->post_name ];
			$file = plugin_dir_path( __FILE__ ) . $page_template;

			if ( file_exists( $file ) ) {
				return $file;
			} else {
				echo $file;
			}

			return $template;
		}
	}

	public function enqueueStyles() {
		foreach( $this->styles as $handle => $src ) {
			$file = plugin_dir_url( __FILE__ ) . $src;
			wp_enqueue_style( $handle, $file, false, time() /* TODO: use filemtime */ );
		}
	}
}

$timeclock_theme_loader = new TimeclockThemeLoader();
$timeclock_theme_loader->init();
