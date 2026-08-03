<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagina impostazioni: Impostazioni > Modulo Contatto AI.
 */
class MCAG_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public static function defaults() {
		return array(
			'api_key'            => '',
			'model'              => 'gemini-3.5-flash',
			'categories'         => "AI Strategy & Advisory\nSviluppo Agenti AI\nCompliance Legale\nFormazione Corporate",
			'notify_email'       => get_option( 'admin_email' ),
			'ip_limit_max'       => 5,
			'ip_limit_window'    => 10,
			'daily_limit'        => 100,
			'fallback_message'   => 'Grazie! Abbiamo ricevuto la tua richiesta e ti risponderemo al più presto via email.',
		);
	}

	public static function get( $key ) {
		$opts = wp_parse_args( get_option( MCAG_OPTION, array() ), self::defaults() );
		return $opts[ $key ] ?? null;
	}

	public function add_menu() {
		add_options_page(
			__( 'Modulo Contatto AI (Gemini)', 'modulo-contatto-ai-gemini' ),
			__( 'Modulo Contatto AI', 'modulo-contatto-ai-gemini' ),
			'manage_options',
			'mcag-settings',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting( 'mcag_settings_group', MCAG_OPTION, array( $this, 'sanitize' ) );
	}

	public function sanitize( $input ) {
		$out = self::defaults();

		$out['api_key']          = isset( $input['api_key'] ) ? sanitize_text_field( wp_unslash( $input['api_key'] ) ) : '';
		$out['model']            = isset( $input['model'] ) ? sanitize_text_field( wp_unslash( $input['model'] ) ) : $out['model'];
		$out['categories']       = isset( $input['categories'] ) ? sanitize_textarea_field( wp_unslash( $input['categories'] ) ) : $out['categories'];
		$out['notify_email']     = isset( $input['notify_email'] ) ? sanitize_email( wp_unslash( $input['notify_email'] ) ) : $out['notify_email'];
		$out['ip_limit_max']     = isset( $input['ip_limit_max'] ) ? max( 1, absint( $input['ip_limit_max'] ) ) : $out['ip_limit_max'];
		$out['ip_limit_window']  = isset( $input['ip_limit_window'] ) ? max( 1, absint( $input['ip_limit_window'] ) ) : $out['ip_limit_window'];
		$out['daily_limit']      = isset( $input['daily_limit'] ) ? max( 1, absint( $input['daily_limit'] ) ) : $out['daily_limit'];
		$out['fallback_message'] = isset( $input['fallback_message'] ) ? sanitize_textarea_field( wp_unslash( $input['fallback_message'] ) ) : $out['fallback_message'];

		return $out;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o = wp_parse_args( get_option( MCAG_OPTION, array() ), self::defaults() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Modulo Contatto AI - Gemini', 'modulo-contatto-ai-gemini' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'mcag_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mcag_api_key"><?php esc_html_e( 'API Key Google Gemini', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<input type="password" id="mcag_api_key" name="<?php echo esc_attr( MCAG_OPTION ); ?>[api_key]" value="<?php echo esc_attr( $o['api_key'] ); ?>" class="regular-text" autocomplete="off" />
							<p class="description">
								<?php
								printf(
									/* translators: %s: link a Google AI Studio */
									esc_html__( 'Ottienila gratis su %s.', 'modulo-contatto-ai-gemini' ),
									'<a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer">Google AI Studio</a>'
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mcag_model"><?php esc_html_e( 'Modello Gemini', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<input type="text" id="mcag_model" name="<?php echo esc_attr( MCAG_OPTION ); ?>[model]" value="<?php echo esc_attr( $o['model'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Nome esatto del modello (es. gemini-3.5-flash). I modelli "Flash" sono quelli disponibili nel piano gratuito.', 'modulo-contatto-ai-gemini' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mcag_categories"><?php esc_html_e( 'Categorie di servizio', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<textarea id="mcag_categories" name="<?php echo esc_attr( MCAG_OPTION ); ?>[categories]" rows="4" class="large-text"><?php echo esc_textarea( $o['categories'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Una categoria per riga. Compaiono nel menu a tendina del modulo e Gemini classificherà ogni richiesta in una di queste.', 'modulo-contatto-ai-gemini' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mcag_notify_email"><?php esc_html_e( 'Email per le notifiche', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<input type="email" id="mcag_notify_email" name="<?php echo esc_attr( MCAG_OPTION ); ?>[notify_email]" value="<?php echo esc_attr( $o['notify_email'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Ogni richiesta arriva sempre qui via email, anche quando il limite giornaliero AI è esaurito.', 'modulo-contatto-ai-gemini' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Limiti anti-abuso e "resta sempre gratis"', 'modulo-contatto-ai-gemini' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %s: link al rate limit di Google AI Studio */
						esc_html__( 'Google offre un piano gratuito con un numero massimo di richieste al giorno per i modelli Flash, ma la cifra esatta varia per account e nel tempo. Controlla il tuo limite attuale su %s e imposta qui sotto un tetto giornaliero prudente (es. 70-80%% del tuo limite reale), così il modulo si ferma da solo prima di sforare in area a pagamento.', 'modulo-contatto-ai-gemini' ),
						'<a href="https://aistudio.google.com/rate-limit" target="_blank" rel="noopener noreferrer">Google AI Studio → Rate Limits</a>'
					);
					?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mcag_daily_limit"><?php esc_html_e( 'Limite giornaliero totale (tutte le richieste, tutti i visitatori)', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<input type="number" min="1" id="mcag_daily_limit" name="<?php echo esc_attr( MCAG_OPTION ); ?>[daily_limit]" value="<?php echo esc_attr( $o['daily_limit'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( 'Si azzera ogni giorno a mezzanotte (fuso orario del sito). Superato il limite, il modulo continua a inviare l\'email di notifica ma non genera più il report AI fino al giorno dopo.', 'modulo-contatto-ai-gemini' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mcag_ip_limit_max"><?php esc_html_e( 'Richieste massime per singolo visitatore (IP)', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<input type="number" min="1" id="mcag_ip_limit_max" name="<?php echo esc_attr( MCAG_OPTION ); ?>[ip_limit_max]" value="<?php echo esc_attr( $o['ip_limit_max'] ); ?>" class="small-text" />
							<?php esc_html_e( 'ogni', 'modulo-contatto-ai-gemini' ); ?>
							<input type="number" min="1" id="mcag_ip_limit_window" name="<?php echo esc_attr( MCAG_OPTION ); ?>[ip_limit_window]" value="<?php echo esc_attr( $o['ip_limit_window'] ); ?>" class="small-text" />
							<?php esc_html_e( 'secondi', 'modulo-contatto-ai-gemini' ); ?>
							<p class="description"><?php esc_html_e( 'Blocca chi invia più richieste di fila dallo stesso indirizzo IP (protezione da bot/spam).', 'modulo-contatto-ai-gemini' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mcag_fallback_message"><?php esc_html_e( 'Messaggio quando il limite giornaliero è esaurito', 'modulo-contatto-ai-gemini' ); ?></label></th>
						<td>
							<textarea id="mcag_fallback_message" name="<?php echo esc_attr( MCAG_OPTION ); ?>[fallback_message]" rows="2" class="large-text"><?php echo esc_textarea( $o['fallback_message'] ); ?></textarea>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr />
			<p><?php esc_html_e( 'Per mostrare il modulo in una pagina usa lo shortcode:', 'modulo-contatto-ai-gemini' ); ?> <code>[modulo_contatto_ai]</code></p>
		</div>
		<?php
	}
}
