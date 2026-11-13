<?php
/**
 * Removes the plugin's single option when the plugin is deleted.
 *
 * @package PileQuote
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'pilequote_settings' );
