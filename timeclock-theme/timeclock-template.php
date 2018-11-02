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
			<form id="timeclock-form" method="post" autocomplete="false">
				<?php wp_nonce_field( 'timeclock_in_or_out', 'timeclock-form' ); ?>
				<fieldset>
					<select id="timeclock-user-select" name="user-select">
						<option value="" disabled="disabled" selected="selected">
							<?php echo __( 'Select your name', 'team-time-log' ) ?>
						</option>
						<?php do_action( 'team-time-log_user_options' ); ?>
					</select>
				</fieldset>

				<fieldset>
					<input
						id="user-pin"
						type="password"
						name="user-pin"
						size="4"
						minlength="4"
						maxlength="4"
						inputmode="numeric"
						autocomplete="false"
					/>
				</fieldset>

				<fieldset>
					<div id="timeclock-keypad">
						<button class="keypad-button" value="1">1</button>
						<button class="keypad-button" value="2">2</button>
						<button class="keypad-button" value="3">3</button>
						<button class="keypad-button" value="4">4</button>
						<button class="keypad-button" value="5">5</button>
						<button class="keypad-button" value="6">6</button>
						<button class="keypad-button" value="7">7</button>
						<button class="keypad-button" value="8">8</button>
						<button class="keypad-button" value="9">9</button>
						<button class="keypad-button" value="clear">
							<?php echo __( 'CLR', 'team-time-log' ); ?>
						</button>
						<button class="keypad-button" value="0">0</button>
						<button class="keypad-button" value="backspace">
							<?php echo __( '<', 'team-time-log' ); ?>
						</button>
					</div-button>
				</fieldset>

				<fieldset>
					<div class="timeclock-submit">
						<button id="timeclock-clock-in" name="submit" type="submit" form="timeclock-form" value="in">
							<?php echo __( 'IN', 'team-time-log' ); ?>
						</button>
						<button id="timeclock-clock-out" name="submit" type="submit" form="timeclock-form" value="out">
							<?php echo __( 'OUT', 'team-time-log' ); ?>
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
