<?php
/**
 * Block render callback.
 *
 * @package LandscapeMaterialCalculator
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo lmcalc_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lmcalc_render() escapes its output.
	isset( $attributes['material'] ) ? (string) $attributes['material'] : 'all',
	get_block_wrapper_attributes( array( 'class' => 'lmcalc' ) )
);
