<?php
namespace coderkevin\TeamTimeLog;

/**
 * Render HTML for Admin Profile Field
 * 
 * @package   TeamTimeLog
 * @author    coderkevin
 * @copyright 2018 Kevin Killingsworth
 * @license   GPL-2.0+ 
 */

defined( 'ABSPATH' ) or die();

function admin_profile_fieldset( $pin_exists ) {
	$placeholder = ( $pin_exists ? '####' : '' );
	// TODO: JS Validate PIN input.

?>
<h2><?php echo __( 'Team Time Log', 'team-time-log' ) ?></h2>
<table class="form-table">
	<tr>
		<th>
			<label for="time-clock-pin">
				<?php echo __( 'Time Clock PIN (4-digit)', 'team-time-log' ) ?>
			</label>
		</th>
		<td>
			<input
				type="password"
				name="time-clock-pin"
				size="4"
				minlength="4"
				maxlength="4"
				inputmode="numeric"
				placeholder="<?php echo $placeholder ?>"
			/>
		</td>
	</tr>
</table>

<?php } ?>
