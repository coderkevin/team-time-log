<?php
/*
 * Template Name: Time Clock
 * Description: A time clock for use with the team-time-log plugin.
 * 
 * @package team-time-log
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel"pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
<?php do_action( 'team-time-log_timeclock_header' ); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">
		<h2>Time Clock</h2>
	</header>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">
			<div id="timeclock-live-clock">
				<span id="timeclock-clock-digits" />
			</div>

			<form id="timeclock-form" method="post" autocomplete="false">
				<?php wp_nonce_field( 'timeclock_in_or_out', 'timeclock-form-submit' ); ?>
				<?php do_action( 'team-time-log_user_select' ); ?>

				<?php do_action( 'team-time-log_user_pin' ); ?>

				<?php do_action( 'team-time-log_keypad' ); ?>

				<fieldset>
					<div class="timeclock-submit">
						<button id="timeclock-clock-in" name="action" type="submit" form="timeclock-form" value="clock_in">
							<?php echo __( 'Clock IN', 'team-time-log' ); ?>
						</button>
						<button id="timeclock-clock-out" name="action" type="submit" form="timeclock-form" value="clock_out">
							<?php echo __( 'Clock OUT', 'team-time-log' ); ?>
						</button>
					</div>
				</fieldset>
			</form>
		</div> <!-- .col-full -->
	</div> <!-- #content -->

	<footer class="site-footer" role="contentinfo">
		<span class="team-time-log-tagline"><?php echo __( 'Team Time Log', 'team-time-log' ) ?></span>
	</footer>
</div> <!-- #page -->

<?php wp_footer(); ?>
<?php do_action( 'team-time-log_timeclock_footer' ); ?>
</body>
</html>
