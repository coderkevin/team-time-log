<?php
/*
 * Template Name: Time Clock
 * Description: A time clock for use with the team-time-log plugin.
 * 
 * @package team-time-log
 */
?>
<!doctype html>
<html <?= language_attributes(); ?>>
<head>
<meta charset="<?= bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel"pingback" href="<?= bloginfo( 'pingback_url' ); ?>">

<?= wp_head(); ?>
</head>

<body <?= body_class(); ?>>

<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">
		<h2>Time Clock</h2>
	</header>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">
			<form class="timeclock-form">
				<label for="user-select">
					<?= __( 'Select User:', 'team-time-log' ) ?>
				</label>
				<select>
					<option name="user-select" value="" disabled="disabled" selected="selected">
						<?= __( 'Select your name', 'team-time-log' ) ?>
					</option>
					<?= do_action( 'team-time-log_user_options' ) ?>
				</select>
			</form>
		</div> <!-- .col-full -->
	</div> <!-- #content -->

	<footer class="site-footer" role="contentinfo">
		<span class="team-time-log-tagline"><?= __( 'Team Time Log', 'team-time-log' ) ?></span>
	</footer>
</div> <!-- #page -->

<?= wp_footer(); ?>

</body>
</html>
