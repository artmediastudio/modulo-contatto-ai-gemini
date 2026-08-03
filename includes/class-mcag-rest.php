<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MCAG_Rest {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'mcag/v1', '/consult', array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => array( $this, 'handle_consult' ),
		) );
	}

	private function client_ip() {
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		}
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	private function ip_rate_limit_ok() {
		$max    = (int) MCAG_Settings::get( 'ip_limit_max' );
		$window = (int) MCAG_Settings::get( 'ip_limit_window' );

		$key   = 'mcag_rl_' . md5( $this->client_ip() );
		$count = (int) get_transient( $key );
		if ( $count >= $max ) {
			return false;
		}
		set_transient( $key, $count + 1, $window );
		return true;
	}

	public function handle_consult( WP_REST_Request $request ) {
		// Honeypot: campo nascosto che solo un bot compilerebbe.
		$honeypot = (string) $request->get_param( 'website' );
		if ( '' !== trim( $honeypot ) ) {
			// Risposta "muta": non riveliamo al bot che è stato individuato.
			return rest_ensure_response( array( 'ok' => true ) );
		}

		if ( ! $this->ip_rate_limit_ok() ) {
			return new WP_Error( 'rate_limited', __( 'Troppe richieste, riprova tra qualche secondo.', 'modulo-contatto-ai-gemini' ), array( 'status' => 429 ) );
		}

		// Consenso privacy obbligatorio (il checkbox lato client è solo una comodità).
		$privacy = (string) $request->get_param( 'privacy' );
		if ( ! in_array( $privacy, array( '1', 'true', 'on', 'yes' ), true ) ) {
			return new WP_Error( 'no_consent', __( 'Devi accettare l\'informativa sulla privacy per inviare la richiesta.', 'modulo-contatto-ai-gemini' ), array( 'status' => 400 ) );
		}

		$name        = sanitize_text_field( (string) $request->get_param( 'name' ) );
		$email       = sanitize_email( (string) $request->get_param( 'email' ) );
		$service     = sanitize_text_field( (string) $request->get_param( 'service' ) );
		$description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );

		if ( strlen( trim( $description ) ) < 5 ) {
			return new WP_Error( 'bad_request', __( 'Descrizione del progetto troppo breve o mancante.', 'modulo-contatto-ai-gemini' ), array( 'status' => 400 ) );
		}

		$notify_email = MCAG_Settings::get( 'notify_email' );
		if ( $notify_email ) {
			wp_mail(
				$notify_email,
				sprintf(
					/* translators: %s: nome del richiedente */
					__( 'Nuova richiesta di contatto — %s', 'modulo-contatto-ai-gemini' ),
					$name ?: __( 'Anonimo', 'modulo-contatto-ai-gemini' )
				),
				implode( "\n", array(
					__( 'Nome/Azienda:', 'modulo-contatto-ai-gemini' ) . ' ' . ( $name ?: '-' ),
					__( 'Email:', 'modulo-contatto-ai-gemini' ) . ' ' . ( $email ?: '-' ),
					__( 'Servizio:', 'modulo-contatto-ai-gemini' ) . ' ' . ( $service ?: '-' ),
					'',
					__( 'Descrizione progetto:', 'modulo-contatto-ai-gemini' ),
					$description,
				) ),
				$email ? array( 'Reply-To: ' . $email ) : array()
			);
		}

		$daily_limit = (int) MCAG_Settings::get( 'daily_limit' );
		if ( ! MCAG_Quota::has_budget( $daily_limit ) ) {
			return rest_ensure_response( array(
				'ai_available' => false,
				'message'      => MCAG_Settings::get( 'fallback_message' ),
			) );
		}

		$report = $this->generate_report( $name, $email, $service, $description );
		if ( is_wp_error( $report ) ) {
			// L'email è già partita: non far fallire la richiesta del visitatore per un problema lato Gemini.
			return rest_ensure_response( array(
				'ai_available' => false,
				'message'      => MCAG_Settings::get( 'fallback_message' ),
			) );
		}

		MCAG_Quota::increment();

		return rest_ensure_response( array_merge( array( 'ai_available' => true ), $report ) );
	}

	private function categories_list() {
		$raw  = (string) MCAG_Settings::get( 'categories' );
		$list = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
		return $list ? array_values( $list ) : array( 'Generico' );
	}

	private function generate_report( $name, $email, $service, $description ) {
		$api_key = MCAG_Settings::get( 'api_key' );
		if ( ! $api_key ) {
			return new WP_Error( 'no_key', 'API key Gemini non configurata.' );
		}

		$categories = $this->categories_list();

		$prompt = "Analizza la seguente richiesta di consulenza pervenuta da un cliente.\n"
			. 'Nome/Azienda: ' . ( $name ?: 'Anonimo' ) . "\n"
			. "Servizio d'interesse dichiarato: " . ( $service ?: 'Non specificato' ) . "\n"
			. 'Descrizione progetto: ' . $description . "\n\n"
			. "Genera un report in formato JSON con:\n"
			. '- summary (string): analisi sintetica della richiesta in italiano.' . "\n"
			. '- serviceCategory (string): una tra queste categorie esatte: ' . implode( ', ', $categories ) . "\n"
			. "- estimatedComplexity (string): una tra \"Bassa\", \"Media\", \"Alta\".\n"
			. "- estimatedTimeline (string): tempo stimato di realizzazione.\n"
			. '- nextSteps (array di stringhe): 2-3 passi pratici successivi.';

		$model = MCAG_Settings::get( 'model' );
		$url   = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent';

		$response = wp_remote_post( $url, array(
			'timeout' => 60,
			'headers' => array(
				'Content-Type'   => 'application/json',
				'x-goog-api-key' => $api_key,
			),
			'body'    => wp_json_encode( array(
				'contents'         => array(
					array( 'parts' => array( array( 'text' => $prompt ) ) ),
				),
				'generationConfig' => array(
					'responseMimeType' => 'application/json',
					'responseSchema'   => array(
						'type'       => 'OBJECT',
						'required'   => array( 'summary', 'serviceCategory', 'estimatedComplexity', 'estimatedTimeline', 'nextSteps' ),
						'properties' => array(
							'summary'             => array( 'type' => 'STRING' ),
							'serviceCategory'     => array( 'type' => 'STRING' ),
							'estimatedComplexity' => array( 'type' => 'STRING' ),
							'estimatedTimeline'   => array( 'type' => 'STRING' ),
							'nextSteps'           => array(
								'type'  => 'ARRAY',
								'items' => array( 'type' => 'STRING' ),
							),
						),
					),
				),
			), JSON_UNESCAPED_UNICODE ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || empty( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return new WP_Error( 'gemini_error', $data['error']['message'] ?? ( 'HTTP ' . $code ) );
		}

		$report = json_decode( $data['candidates'][0]['content']['parts'][0]['text'], true );
		if ( ! is_array( $report ) ) {
			return new WP_Error( 'gemini_error', 'Risposta Gemini non interpretabile.' );
		}

		return $report;
	}
}
