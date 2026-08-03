<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contatore giornaliero globale delle chiamate a Gemini.
 *
 * Si azzera automaticamente al cambio di data (fuso orario del sito),
 * indipendentemente da IP/utente: serve a tenere l'uso complessivo del
 * plugin sotto al limite giornaliero del piano gratuito Gemini.
 */
class MCAG_Quota {

	const OPTION_COUNT = 'mcag_quota_count';
	const OPTION_DATE   = 'mcag_quota_date';

	/**
	 * True se c'è ancora margine per un'altra chiamata AI oggi.
	 */
	public static function has_budget( $daily_limit ) {
		self::maybe_reset();
		return (int) get_option( self::OPTION_COUNT, 0 ) < (int) $daily_limit;
	}

	/**
	 * Registra una chiamata AI effettuata.
	 */
	public static function increment() {
		self::maybe_reset();
		$count = (int) get_option( self::OPTION_COUNT, 0 );
		update_option( self::OPTION_COUNT, $count + 1, false );
	}

	private static function maybe_reset() {
		$today = current_time( 'Y-m-d' );
		if ( get_option( self::OPTION_DATE ) !== $today ) {
			update_option( self::OPTION_DATE, $today, false );
			update_option( self::OPTION_COUNT, 0, false );
		}
	}
}
