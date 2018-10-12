<?php
namespace coderkevin\TeamTimeLog;

/**
 * Template Loader
 * 
 * @package   TeamTimeTracker
 * @author    Kevin Killingsworth
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+
 */

defined( 'ABSPATH' ) or die();

class TemplateLoader {
	protected $templates = [
		'time-clock' => 'timeclock-template.php',
	];

	public function init() {
		$hasThemeSupport = current_theme_supports( 'team-time-log' );

		if ( ! $hasThemeSupport ) {
			add_filter( 'template_include', [ $this, 'viewTemplate' ] );
		}
	}

	public function getTemplateDir() {
		global $team_time_log;
		return trailingslashit( $team_time_log->plugin_dir . 'templates' );
	}

	public function viewTemplate( $template ) {
		if ( is_search() ) {
			return $template;
		}

		global $post;

		if ( ! $post ) {
			return $template;
		}

		$page_template = $this->templates[ $post->post_name ];

		if ( $page_template ) {
			$file = $this->getTemplateDir() . $page_template;

			if ( file_exists( $file ) ) {
				return $file;
			} else {
				echo $file;
			}

			return $template;
		}
	}
}

$template_loader = new TemplateLoader();
$template_loader->init();
