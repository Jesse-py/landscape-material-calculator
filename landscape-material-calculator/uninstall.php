<?php
/**
 * Removes the plugin's single option when the plugin is deleted.
 *
 * @package LandscapeMaterialCalculator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'lmcalc_settings' );
