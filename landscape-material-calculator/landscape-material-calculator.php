<?php
/**
 * Plugin Name:       Landscape Material Calculator
 * Plugin URI:        https://jesse-py.github.io/diy-project-calculators/
 * Description:       Lets visitors work out how many cubic yards or tons of mulch, topsoil, gravel, sand, or concrete they need, priced with your own rates and rounded the way you sell. Shortcode [landscape_material_calculator] or the block.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Jesse-py
 * Author URI:        https://github.com/Jesse-py
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       landscape-material-calculator
 *
 * @package LandscapeMaterialCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LMCALC_VERSION', '0.1.0' );
define( 'LMCALC_FILE', __FILE__ );
define( 'LMCALC_DIR', plugin_dir_path( __FILE__ ) );
define( 'LMCALC_URL', plugin_dir_url( __FILE__ ) );

require_once LMCALC_DIR . 'includes/settings.php';
require_once LMCALC_DIR . 'includes/render.php';
if ( is_admin() ) {
	require_once LMCALC_DIR . 'includes/admin.php';
}

/**
 * Registers the front-end assets (enqueued only on pages that show a calculator) and the block.
 */
function lmcalc_init() {
	wp_register_script( 'lmcalc', LMCALC_URL . 'assets/lmcalc.js', array(), LMCALC_VERSION, array( 'in_footer' => true ) );
	wp_register_style( 'lmcalc', LMCALC_URL . 'assets/lmcalc.css', array(), LMCALC_VERSION );
	add_shortcode( 'landscape_material_calculator', 'lmcalc_shortcode' );
	register_block_type( LMCALC_DIR . 'blocks/calculator' );
}
add_action( 'init', 'lmcalc_init' );

/**
 * Shortcode handler: [landscape_material_calculator material="gravel"].
 *
 * @param array|string $atts Shortcode attributes.
 * @return string HTML.
 */
function lmcalc_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'material' => 'all' ), $atts, 'landscape_material_calculator' );
	return lmcalc_render( $atts['material'] );
}

/**
 * Adds a Settings link on the Plugins screen.
 *
 * @param array $links Existing links.
 * @return array
 */
function lmcalc_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=landscape-material-calculator' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'landscape-material-calculator' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lmcalc_action_links' );
