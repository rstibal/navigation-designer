export function generateId() {
	if ( window.crypto && window.crypto.randomUUID ) {
		return 'nd-' + window.crypto.randomUUID().slice( 0, 8 );
	}
	return 'nd-' + Math.random().toString( 36 ).slice( 2, 10 );
}
