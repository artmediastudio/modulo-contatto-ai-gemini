( function () {
	'use strict';

	var form = document.getElementById( 'mcag-form' );
	if ( ! form ) {
		return;
	}

	var result = document.getElementById( 'mcag-result' );
	var button = form.querySelector( '.mcag-submit' );

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}

	function showError( message ) {
		result.hidden = false;
		result.className = 'mcag-result mcag-error';
		result.innerHTML = '<p>' + escapeHtml( message ) + '</p>';
	}

	function showReport( data ) {
		result.hidden = false;
		result.className = 'mcag-result';

		if ( ! data.ai_available ) {
			result.innerHTML = '<p>' + escapeHtml( data.message || '' ) + '</p>';
			return;
		}

		var steps = ( data.nextSteps || [] )
			.map( function ( s ) { return '<li>' + escapeHtml( s ) + '</li>'; } )
			.join( '' );

		result.innerHTML =
			'<h4>Report di fattibilità</h4>' +
			'<p>' + escapeHtml( data.summary || '' ) + '</p>' +
			'<p><strong>Categoria:</strong> ' + escapeHtml( data.serviceCategory || '' ) + '<br>' +
			'<strong>Complessità stimata:</strong> ' + escapeHtml( data.estimatedComplexity || '' ) + '<br>' +
			'<strong>Tempistiche stimate:</strong> ' + escapeHtml( data.estimatedTimeline || '' ) + '</p>' +
			( steps ? '<p><strong>Prossimi passi:</strong></p><ul>' + steps + '</ul>' : '' );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		button.disabled = true;
		result.hidden = true;

		var payload = {
			name: form.name.value,
			email: form.email.value,
			service: form.service ? form.service.value : '',
			description: form.description.value,
			privacy: form.privacy && form.privacy.checked ? '1' : '',
			website: form.website.value, // honeypot
		};

		// Eventi pubblici: permettono a temi/pagine di mostrare esito e caricamento altrove (es. pannello laterale).
		document.dispatchEvent( new CustomEvent( 'mcag:submit', { detail: payload } ) );

		fetch( MCAG.restUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( payload ),
		} )
			.then( function ( res ) {
				return res.json().then( function ( data ) {
					if ( ! res.ok ) {
						throw new Error( data.message || 'Errore durante l\'invio.' );
					}
					return data;
				} );
			} )
			.then( function ( data ) {
				showReport( data );
				document.dispatchEvent( new CustomEvent( 'mcag:result', { detail: data } ) );
			} )
			.catch( function ( err ) {
				var message = err.message || 'Errore durante l\'invio.';
				showError( message );
				document.dispatchEvent( new CustomEvent( 'mcag:error', { detail: { message: message } } ) );
			} )
			.finally( function () {
				button.disabled = false;
			} );
	} );
} )();
