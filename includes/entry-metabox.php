<?php
namespace coderkevin\TeamTimeLog;

include_once 'date-input.php';

function entry_metabox( $entry ) {
	$entry_id = $entry->ID;
	$clock_in_str = $entry->post_date;
	$clock_out_str = $entry->post_modified;
	$clock_in_date = datestring_to_datetime( $clock_in_str );
	$clock_out_date = datestring_to_datetime( $clock_out_str );
?>

<div class="team-time-log-entry">
	<?php date_input( 'clock_in_' . $entry_id, __( 'Clock In:', 'team-time-log' ), $clock_in_date ) ?>
</div>

<div class="team-time-log-entry">
	<?php date_input( 'clock_out_' . $entry_id, __( 'Clock Out:', 'team-time-log' ), $clock_out_date ) ?>
</div>

<?php } ?>