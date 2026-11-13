<?php
/**
 * Plugin Name:       PileQuote Material Calculator
 * Plugin URI:        https://jesse-py.github.io/diy-project-calculators/
 * Description:       Lets visitors work out how many cubic yards or tons of mulch, topsoil, gravel, sand, or concrete they need, priced with your own rates and rounded the way you sell. Shortcode [pilequote] or the block.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Jesse-py
 * Author URI:        https://github.com/Jesse-py
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pilequote-material-calculator
 *
 * @package PileQuote
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PILEQUOTE_VERSION', '0.1.0' );
define( 'PILEQUOTE_FILE', __FILE__ );
define( 'PILEQUOTE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PILEQUOTE_URL', plugin_dir_url( __FILE__ ) );

require_once PILEQUOTE_DIR . 'includes/settings.php';
require_once PILEQUOTE_DIR . 'includes/render.php';
if ( is_admin() ) {
	require_once PILEQUOTE_DIR . 'includes/admin.php';
}

/**
 * Registers the front-end assets (enqueued only on pages that show a calculator) and the block.
 */
function pilequote_init() {
	wp_register_script( 'pilequote', PILEQUOTE_URL . 'assets/pilequote.js', array(), PILEQUOTE_VERSION, array( 'in_footer' => true ) );
	wp_register_style( 'pilequote', PILEQUOTE_URL . 'assets/pilequote.css', array(), PILEQUOTE_VERSION );
	add_shortcode( 'pilequote', 'pilequote_shortcode' );
	register_block_type( PILEQUOTE_DIR . 'blocks/calculator' );
}
add_action( 'init', 'pilequote_init' );

/**
 * Shortcode handler: [pilequote material="gravel"].
 *
 * @param array|string $atts Shortcode attributes.
 * @return string HTML.
 */
function pilequote_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'material' => 'all' ), $atts, 'pilequote' );
	return pilequote_render( $atts['material'] );
}

/**
 * Adds a Settings link on the Plugins screen.
 *
 * @param array $links Existing links.
 * @return array
 */
function pilequote_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=pilequote-material-calculator' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'pilequote-material-calculator' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pilequote_action_links' );
