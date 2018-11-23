/**
 * Handle the timeclock form client.
 * 
 * - Keypad buttons edit the user pin field
 */

jQuery( document ).ready( () => {
	const userId = jQuery( '#timeclock-user-select' ).val();
	updateForm( userId );

	if ( window.timeclock_notification ) {
		iqwerty.toast.Toast( window.timeclock_notification );
	}
} );

jQuery( '#timeclock-user-select' ).on( 'change', ( event ) => {
	const userId = jQuery( '#timeclock-user-select' ).val();
	updateForm( userId );
} );

jQuery( '.keypad-button' ).on( 'click', ( event ) => {
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

function updateForm( userId ) {
	const userInfo = userId && timeclock_user_info && timeclock_user_info[ userId ] || null;
	const userSelected = null !== userInfo;
	const clockedIn = userSelected && userInfo.clocked_in;

	jQuery( '#user-pin' ).attr( 'disabled', ! userSelected );
	jQuery( '.keypad-button' ).attr( 'disabled', ! userSelected );
	jQuery( '#timeclock-clock-in' ).attr( 'disabled', clockedIn );
	jQuery( '#timeclock-clock-out' ).attr( 'disabled', ! clockedIn );
}
