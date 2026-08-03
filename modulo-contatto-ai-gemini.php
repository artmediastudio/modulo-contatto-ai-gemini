<?php
/**
 * Plugin Name:       Modulo Contatto AI - Report di Fattibilità con Gemini
 * Plugin URI:        https://github.com/artmediastudio/modulo-contatto-ai-gemini
 * Description:       Modulo di contatto che genera un report di fattibilità automatico con Google Gemini per ogni richiesta ricevuta, con limiti configurabili per restare sempre nel piano gratuito. Sviluppato da AI Agenti Intelligenti.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            AI Agenti Intelligenti
 * Author URI:        https://www.agentiintelligenti.it
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       modulo-contatto-ai-gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MCAG_VERSION', '1.0.2' );
define( 'MCAG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCAG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MCAG_OPTION', 'mcag_settings' );

require_once MCAG_PLUGIN_DIR . 'includes/class-mcag-settings.php';
require_once MCAG_PLUGIN_DIR . 'includes/class-mcag-quota.php';
require_once MCAG_PLUGIN_DIR . 'includes/class-mcag-rest.php';
require_once MCAG_PLUGIN_DIR . 'includes/class-mcag-shortcode.php';

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'modulo-contatto-ai-gemini', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	new MCAG_Settings();
	new MCAG_Rest();
	new MCAG_Shortcode();
} );
