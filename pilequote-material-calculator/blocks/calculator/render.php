<?php
/**
 * Block render callback.
 *
 * @package PileQuote
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo pilequote_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pilequote_render() escapes its output.
	isset( $attributes['material'] ) ? (string) $attributes['material'] : 'all',
	get_block_wrapper_attributes( array( 'class' => 'pilequote' ) )
);
