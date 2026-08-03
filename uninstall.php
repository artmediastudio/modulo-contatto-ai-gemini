<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'mcag_settings' );
delete_option( 'mcag_quota_count' );
delete_option( 'mcag_quota_date' );
