/**
 * Handle the timeclock form client.
 * 
 * - Keypad buttons edit the user pin field
 */

jQuery( document ).ready( () => {
	updateForm( );

	if ( window.timeclock_notification ) {
		const message = window.timeclock_notification.message;
		const isError = window.timeclock_notification.isError;

		const options = {
			style: {
				main: {
					background: ( isError ? '#bb2727' : '#505050' ),
					color: 'white',
					'font-weight': ( isError ? 'bold' : 'normal' ),
				}
			}
		};

		iqwerty.toast.Toast( message, options );
	}

	window.setInterval( updateClock, 500 );
} );

jQuery( '#timeclock-user-select' ).on( 'change', ( event ) => {
	updateForm();
} );

jQuery( '#user-pin' ).on( 'input', () => {
	updateForm();
} );

jQuery( '.keypad-button' ).on( 'click', ( event ) => {
	event.preventDefault();
	const previousPin = jQuery( '#user-pin' ).val();
	const buttonValue = event.target.value;
	const pin = updatePin( previousPin, buttonValue );
	jQuery( '#user-pin' ).val( pin ).trigger( 'input' );
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

function updateForm() {
	const userId = jQuery( '#timeclock-user-select' ).val();
	const userInfo = userId && timeclock_user_info && timeclock_user_info[ userId ] || null;
	const userSelected = null !== userInfo;
	const userAuthed = 'hidden' === jQuery( '#timeclock-user-select' ).attr('type');
	const clockedIn = userSelected && userInfo.clocked_in;
	const pinEntered = userAuthed ? true : jQuery( '#user-pin' ).val().length >= 4;

	const canClockIn = ! clockedIn && userSelected && pinEntered;
	const canClockOut = clockedIn && userSelected && pinEntered;

	jQuery( '#user-pin' ).attr( 'disabled', ! userSelected );
	jQuery( '.keypad-button' ).attr( 'disabled', ! userSelected );
	jQuery( '#timeclock-clock-in' ).attr( 'disabled', ! canClockIn );
	jQuery( '#timeclock-clock-out' ).attr( 'disabled', ! canClockOut );
}

function updateClock() {
	const now = new Date();

	const isPM = now.getHours() > 11;
	const hours12 = isPM ? now.getHours() - 12 : now.getHours()

	const hours = twoDigit( hours12 );
	const minutes = twoDigit( now.getMinutes() );
	const seconds = twoDigit( now.getSeconds() ) ;
	const ampm = isPM ? 'pm' : 'am';
	const timeStr = hours + ':' + minutes + ':' + seconds + ' ' + ampm;

	jQuery( '#timeclock-clock-digits' ).text( timeStr );
}

function twoDigit( value ) {
	return ( value < 10 ? '0' + value : value );
}
