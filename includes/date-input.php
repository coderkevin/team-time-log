<?php
namespace coderkevin\TeamTimeLog;

function date_input( $name, $display_name, $date_time ) {
	$date = '';
	$hour = '';
	$minute = '';

	if ( $date_time instanceof \DateTime ) {
		$date = $date_time->format( get_date_format() );
		$hour = $date_time->format( 'H' );
		$minute = $date_time->format( 'i' );
	}
?>

<fieldset>
	<label>
		<?php echo $display_name ?>
	</label>
	<input
		type="text"
		name="<?php echo $name . '_date' ?>"
		value="<?php echo esc_textarea( $date ) ?>"
		class="team-time-log-datepicker"
		aria-label="<?php echo __( 'Date', 'team-time-log' ) ?>"
	/>
	<input
		type="number"
		name="<?php echo $name . '_hour' ?>"
		min="1"
		max="24"
		value="<?php echo esc_textarea( $hour ) ?>"
		class="team-time-log-entry-hour"
		aria-label="<?php echo __( 'Hour', 'team-time-log' ) ?>"
	/>
	<span>:</span>
	<input
		type="text"
		name="<?php echo $name . '_minute' ?>"
		min="0"
		max="59"
		value="<?php echo esc_textarea( $minute ) ?>"
		class="team-time-log-entry-minute"
		aria-label="<?php echo __( 'Minute', 'team-time-log' ) ?>"
	/>
</fieldset>

<?php } ?>
