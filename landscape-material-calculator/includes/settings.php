<?php
/**
 * Settings: defaults, reading, and sanitizing the single option `lmcalc_settings`.
 *
 * Densities are stored in the site's unit system: short tons per cubic yard (imperial) or tonnes per cubic meter (metric).
 * The gravel and sand default of 1.35 tons/yd3 comes from a 50 lb bag filling 0.5 cu ft (100 lb/ft3), the figure retailers
 * list for QUIKRETE all-purpose gravel (No. 1151) and sand; 1.6 t/m3 is the same density in metric. Suppliers should enter
 * their own figure, since it varies by stone and moisture.
 *
 * @package LandscapeMaterialCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1 short ton = 0.90718474 t and 1 cubic yard = 0.764554857984 m3 (NIST Handbook 44, Appendix C).
define( 'LMCALC_TONS_YD3_TO_T_M3', 0.90718474 / 0.764554857984 );

/**
 * Material keys in display order.
 *
 * @return string[]
 */
function lmcalc_material_keys() {
	return array( 'mulch', 'topsoil', 'compost', 'gravel', 'sand', 'concrete' );
}

/**
 * Default settings.
 *
 * @return array
 */
function lmcalc_defaults() {
	$m = array(
		'mulch'    => array( __( 'Mulch', 'landscape-material-calculator' ), 'volume', 0, 3 ),
		'topsoil'  => array( __( 'Topsoil', 'landscape-material-calculator' ), 'volume', 0, 4 ),
		'compost'  => array( __( 'Compost', 'landscape-material-calculator' ), 'volume', 0, 2 ),
		'gravel'   => array( __( 'Gravel / crushed stone', 'landscape-material-calculator' ), 'weight', 1.35, 4 ),
		'sand'     => array( __( 'Sand', 'landscape-material-calculator' ), 'weight', 1.35, 2 ),
		'concrete' => array( __( 'Concrete', 'landscape-material-calculator' ), 'volume', 0, 4 ),
	);
	$materials = array();
	foreach ( $m as $key => $v ) {
		$materials[ $key ] = array(
			'enabled' => true,
			'label'   => $v[0],
			'sold_by' => $v[1],
			'density' => $v[2],
			'depth'   => $v[3],
			'price'   => 0,
			'step'    => 0,
			'min'     => 0,
		);
	}
	return array(
		'units'      => 'imperial',
		'currency'   => '$',
		'waste'      => 0,
		'quote_url'  => '',
		'quote_text' => __( 'Request a quote', 'landscape-material-calculator' ),
		'bags'       => true,
		'materials'  => $materials,
	);
}

/**
 * Current settings merged over the defaults.
 *
 * @return array
 */
function lmcalc_get_settings() {
	$defaults = lmcalc_defaults();
	$saved    = get_option( 'lmcalc_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$out              = array_merge( $defaults, array_intersect_key( $saved, $defaults ) );
	$out['materials'] = array();
	foreach ( lmcalc_material_keys() as $key ) {
		$mine                     = isset( $saved['materials'][ $key ] ) && is_array( $saved['materials'][ $key ] ) ? $saved['materials'][ $key ] : array();
		$out['materials'][ $key ] = array_merge( $defaults['materials'][ $key ], array_intersect_key( $mine, $defaults['materials'][ $key ] ) );
	}
	return $out;
}

/**
 * A non-negative number from user input, capped at $max.
 *
 * @param mixed $value Raw value.
 * @param float $max   Upper limit.
 * @return float
 */
function lmcalc_number( $value, $max = 1000000 ) {
	$n = is_numeric( $value ) ? (float) $value : 0;
	return max( 0, min( $max, $n ) );
}

/**
 * Sanitizes the settings form.
 *
 * @param mixed $input Raw POSTed option.
 * @return array
 */
function lmcalc_sanitize( $input ) {
	$d   = lmcalc_defaults();
	$in  = is_array( $input ) ? $input : array();
	$out = array(
		'units'      => ( isset( $in['units'] ) && 'metric' === $in['units'] ) ? 'metric' : 'imperial',
		'currency'   => isset( $in['currency'] ) ? substr( sanitize_text_field( $in['currency'] ), 0, 5 ) : $d['currency'],
		'waste'      => lmcalc_number( isset( $in['waste'] ) ? $in['waste'] : 0, 50 ),
		'quote_url'  => isset( $in['quote_url'] ) ? esc_url_raw( trim( $in['quote_url'] ) ) : '',
		'quote_text' => isset( $in['quote_text'] ) && '' !== trim( $in['quote_text'] ) ? sanitize_text_field( $in['quote_text'] ) : $d['quote_text'],
		'bags'       => ! empty( $in['bags'] ),
		'materials'  => array(),
	);
	foreach ( lmcalc_material_keys() as $key ) {
		$m    = isset( $in['materials'][ $key ] ) && is_array( $in['materials'][ $key ] ) ? $in['materials'][ $key ] : array();
		$step = isset( $m['step'] ) ? (string) $m['step'] : '0';
		$out['materials'][ $key ] = array(
			'enabled' => ! empty( $m['enabled'] ),
			'label'   => isset( $m['label'] ) && '' !== trim( $m['label'] ) ? sanitize_text_field( $m['label'] ) : $d['materials'][ $key ]['label'],
			'sold_by' => ( isset( $m['sold_by'] ) && 'weight' === $m['sold_by'] ) ? 'weight' : 'volume',
			'density' => lmcalc_number( isset( $m['density'] ) ? $m['density'] : 0, 10 ),
			'depth'   => lmcalc_number( isset( $m['depth'] ) ? $m['depth'] : 0, 100 ),
			'price'   => lmcalc_number( isset( $m['price'] ) ? $m['price'] : 0 ),
			'step'    => in_array( $step, array( '0', '0.1', '0.25', '0.5', '1' ), true ) ? (float) $step : 0,
			'min'     => lmcalc_number( isset( $m['min'] ) ? $m['min'] : 0, 1000 ),
		);
		// Selling by weight needs a density; fall back to volume rather than show "0 tons".
		if ( 'weight' === $out['materials'][ $key ]['sold_by'] && ! $out['materials'][ $key ]['density'] ) {
			$out['materials'][ $key ]['sold_by'] = 'volume';
		}
		if ( 'concrete' === $key ) {
			$out['materials'][ $key ]['sold_by'] = 'volume';
		}
	}
	// Switching unit systems converts densities and default depths so they keep their meaning. Prices are per unit sold,
	// so the settings page asks the owner to re-check them.
	$old = get_option( 'lmcalc_settings', array() );
	$was = ( is_array( $old ) && isset( $old['units'] ) ) ? $old['units'] : 'imperial';
	if ( $was !== $out['units'] ) {
		$to_metric = 'metric' === $out['units'];
		foreach ( $out['materials'] as $key => $m ) {
			$out['materials'][ $key ]['density'] = round( $to_metric ? $m['density'] * LMCALC_TONS_YD3_TO_T_M3 : $m['density'] / LMCALC_TONS_YD3_TO_T_M3, 3 );
			$out['materials'][ $key ]['depth']   = $to_metric ? round( $m['depth'] * 2.54 * 2 ) / 2 : round( $m['depth'] / 2.54 * 4 ) / 4;
		}
	}
	return $out;
}
