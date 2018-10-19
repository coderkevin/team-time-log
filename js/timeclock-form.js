/**
 * Handle the timeclock form client.
 * 
 * - Keypad buttons edit the user pin field
 */

jQuery( '.keypad-button' ).on( "click", ( event ) => {
	event.preventDefault();
	const previousPin = jQuery( '#user-pin' ).val();
	const buttonValue = event.target.value;
	const pin = updatePin( previousPin, buttonValue );
	jQuery( '#user-pin' ).val( pin );
} );

function updatePin( previousPin, buttonValue ) {
	switch( buttonValue ) {
		case 'clear':
			return '';
		case 'backspace':
			return previousPin.substring( 0, previousPin.length - 1 );
		default:
			return previousPin + buttonValue;
	}
}
