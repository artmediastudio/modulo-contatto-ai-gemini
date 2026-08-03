<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MCAG_Shortcode {

	public function __construct() {
		add_shortcode( 'modulo_contatto_ai', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Carica JS/CSS solo nelle pagine dove lo shortcode è effettivamente usato.
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}
		global $post;
		if ( ! $post || ! has_shortcode( $post->post_content, 'modulo_contatto_ai' ) ) {
			return;
		}

		wp_enqueue_style( 'mcag-form', MCAG_PLUGIN_URL . 'assets/form.css', array(), MCAG_VERSION );
		wp_enqueue_script( 'mcag-form', MCAG_PLUGIN_URL . 'assets/form.js', array(), MCAG_VERSION, true );
		wp_localize_script( 'mcag-form', 'MCAG', array(
			'restUrl' => esc_url_raw( rest_url( 'mcag/v1/consult' ) ),
		) );
	}

	public function render( $atts ) {
		$categories = array_filter( array_map( 'trim', explode( "\n", (string) MCAG_Settings::get( 'categories' ) ) ) );
		ob_start();
		?>
		<form class="mcag-form" id="mcag-form">
			<p class="mcag-field">
				<label for="mcag-name"><?php esc_html_e( 'Nome / Azienda', 'modulo-contatto-ai-gemini' ); ?></label>
				<input type="text" id="mcag-name" name="name" required />
			</p>
			<p class="mcag-field">
				<label for="mcag-email"><?php esc_html_e( 'Email', 'modulo-contatto-ai-gemini' ); ?></label>
				<input type="email" id="mcag-email" name="email" required />
			</p>
			<?php if ( $categories ) : ?>
			<p class="mcag-field">
				<label for="mcag-service"><?php esc_html_e( 'Servizio di interesse', 'modulo-contatto-ai-gemini' ); ?></label>
				<select id="mcag-service" name="service">
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat ); ?>"><?php echo esc_html( $cat ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>
			<p class="mcag-field">
				<label for="mcag-description"><?php esc_html_e( 'Descrivi il tuo progetto', 'modulo-contatto-ai-gemini' ); ?></label>
				<textarea id="mcag-description" name="description" rows="5" required></textarea>
			</p>
			<p class="mcag-field mcag-privacy">
				<label for="mcag-privacy">
					<input type="checkbox" id="mcag-privacy" name="privacy" value="1" required />
					<span>
						<?php
						printf(
							wp_kses(
								/* translators: %s: URL dell'informativa privacy */
								__( 'Ho letto l\'<a href="%s" target="_blank" rel="noopener noreferrer">informativa sulla privacy</a> e acconsento al trattamento dei miei dati per rispondere alla mia richiesta, inclusa l\'elaborazione tramite Google Gemini AI.', 'modulo-contatto-ai-gemini' ),
								array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) )
							),
							esc_url( home_url( '/privacy-policy/' ) )
						);
						?>
					</span>
				</label>
			</p>
			<p class="mcag-honeypot" aria-hidden="true">
				<label for="mcag-website"><?php esc_html_e( 'Non compilare questo campo', 'modulo-contatto-ai-gemini' ); ?></label>
				<input type="text" id="mcag-website" name="website" tabindex="-1" autocomplete="off" />
			</p>
			<p class="mcag-field">
				<button type="submit" class="mcag-submit"><?php esc_html_e( 'Invia richiesta', 'modulo-contatto-ai-gemini' ); ?></button>
			</p>
			<div class="mcag-result" id="mcag-result" hidden></div>
		</form>
		<?php
		return ob_get_clean();
	}
}
