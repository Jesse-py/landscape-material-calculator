<?php
/**
 * Front-end markup. The form is rendered on the server (so it shows without layout shift and works with any theme);
 * assets/lmcalc.js does the math in the visitor's browser. Nothing is sent anywhere.
 *
 * @package LandscapeMaterialCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Config passed to the script for one calculator.
 *
 * @param array    $s    Settings.
 * @param string[] $keys Materials shown.
 * @return array
 */
function lmcalc_client_config( $s, $keys ) {
	$metric    = 'metric' === $s['units'];
	$materials = array();
	foreach ( $keys as $key ) {
		$m                 = $s['materials'][ $key ];
		$materials[ $key ] = array(
			'label'   => $m['label'],
			'soldBy'  => $m['sold_by'],
			'density' => (float) $m['density'],
			'price'   => (float) $m['price'],
			'step'    => (float) $m['step'],
			'min'     => (float) $m['min'],
			'depth'   => (float) $m['depth'],
			'bags'    => 'concrete' === $key && $s['bags'] && ! $metric,
		);
	}
	return array(
		'metric'    => $metric,
		'currency'  => $s['currency'],
		'waste'     => (float) $s['waste'],
		'materials' => $materials,
		'i18n'      => array(
			/* translators: %s: amount, e.g. 3.25 */
			'yd3'      => __( '%s cubic yards', 'landscape-material-calculator' ),
			/* translators: %s: amount */
			'm3'       => __( '%s cubic meters', 'landscape-material-calculator' ),
			/* translators: %s: amount */
			'tons'     => __( '%s tons', 'landscape-material-calculator' ),
			/* translators: %s: amount */
			'tonnes'   => __( '%s tonnes', 'landscape-material-calculator' ),
			/* translators: %s: the number 1 */
			'yd3One'   => __( '%s cubic yard', 'landscape-material-calculator' ),
			/* translators: %s: the number 1 */
			'm3One'    => __( '%s cubic meter', 'landscape-material-calculator' ),
			/* translators: %s: the number 1 */
			'tonsOne'  => __( '%s ton', 'landscape-material-calculator' ),
			/* translators: %s: the number 1 */
			'tonnesOne' => __( '%s tonne', 'landscape-material-calculator' ),
			/* translators: %s: quantity with unit, e.g. "3.09 cubic yards" */
			'need'     => __( 'You need about %s.', 'landscape-material-calculator' ),
			/* translators: 1: weight with unit, e.g. "5.06 tons", 2: volume with unit */
			'needBoth' => __( 'You need about %1$s (%2$s).', 'landscape-material-calculator' ),
			/* translators: %s: quantity with unit */
			'weighs'   => __( 'That is about %s of material.', 'landscape-material-calculator' ),
			/* translators: %s: waste percent */
			'waste'    => __( 'Includes %s%% extra for waste and settling.', 'landscape-material-calculator' ),
			/* translators: %s: quantity with unit */
			'order'    => __( 'Order %s.', 'landscape-material-calculator' ),
			/* translators: %s: step, e.g. 0.5 */
			'stepNote' => __( '(We sell in steps of %s.)', 'landscape-material-calculator' ),
			/* translators: %s: minimum quantity with unit */
			'minNote'  => __( '(Minimum order: %s.)', 'landscape-material-calculator' ),
			/* translators: 1: total price, 2: unit price, 3: unit name */
			'cost'     => __( 'Estimated cost: %1$s at %2$s per %3$s, before delivery and tax.', 'landscape-material-calculator' ),
			'unitYd3'  => __( 'cubic yard', 'landscape-material-calculator' ),
			'unitM3'   => __( 'cubic meter', 'landscape-material-calculator' ),
			'unitTon'  => __( 'ton', 'landscape-material-calculator' ),
			'unitT'    => __( 'tonne', 'landscape-material-calculator' ),
			/* translators: 1: number of bags, 2: bag weight in lb */
			'bag'      => __( '%1$s bags of %2$s lb premix', 'landscape-material-calculator' ),
			'bagsHead' => __( 'Or in bags:', 'landscape-material-calculator' ),
			'empty'    => __( 'Enter the size of the area and the depth.', 'landscape-material-calculator' ),
		),
	);
}

/**
 * Renders one calculator.
 *
 * @param string $material A material key, or "all" for a material picker.
 * @param string $wrapper  Extra wrapper attributes (from the block editor), already escaped.
 * @return string HTML.
 */
function lmcalc_render( $material = 'all', $wrapper = '' ) {
	$s       = lmcalc_get_settings();
	$enabled = array();
	foreach ( lmcalc_material_keys() as $key ) {
		if ( $s['materials'][ $key ]['enabled'] ) {
			$enabled[] = $key;
		}
	}
	$material = sanitize_key( $material );
	$keys     = in_array( $material, lmcalc_material_keys(), true ) ? array( $material ) : $enabled;
	if ( ! $keys ) {
		return '';
	}

	static $n = 0;
	++$n;
	$id     = 'lmcalc-' . $n;
	$metric = 'metric' === $s['units'];
	$len    = $metric ? __( 'm', 'landscape-material-calculator' ) : __( 'ft', 'landscape-material-calculator' );
	$area   = $metric ? __( 'm²', 'landscape-material-calculator' ) : __( 'sq ft', 'landscape-material-calculator' );
	$dep    = $metric ? __( 'cm', 'landscape-material-calculator' ) : __( 'in', 'landscape-material-calculator' );
	$first  = $s['materials'][ $keys[0] ];

	wp_enqueue_script( 'lmcalc' );
	wp_enqueue_style( 'lmcalc' );

	$field = function ( $name, $label, $unit, $value = '' ) use ( $id ) {
		return sprintf(
			'<p class="lmcalc-field" data-field="%1$s"><label for="%2$s-%1$s">%3$s <span class="lmcalc-unit">(%4$s)</span></label> <input type="number" inputmode="decimal" min="0" step="any" id="%2$s-%1$s" name="%1$s" value="%5$s"></p>',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_html( $label ),
			esc_html( $unit ),
			esc_attr( $value )
		);
	};

	ob_start();
	?>
	<div <?php echo $wrapper ? $wrapper : 'class="lmcalc"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes(). ?> data-lmcalc="<?php echo esc_attr( wp_json_encode( lmcalc_client_config( $s, $keys ) ) ); ?>">
		<form class="lmcalc-form" novalidate>
			<?php if ( count( $keys ) > 1 ) : ?>
				<p class="lmcalc-field"><label for="<?php echo esc_attr( $id ); ?>-material"><?php esc_html_e( 'Material', 'landscape-material-calculator' ); ?></label>
				<select id="<?php echo esc_attr( $id ); ?>-material" name="material">
					<?php foreach ( $keys as $key ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $s['materials'][ $key ]['label'] ); ?></option>
					<?php endforeach; ?>
				</select></p>
			<?php else : ?>
				<input type="hidden" name="material" value="<?php echo esc_attr( $keys[0] ); ?>">
				<p class="lmcalc-title"><?php echo esc_html( $first['label'] ); ?></p>
			<?php endif; ?>
			<p class="lmcalc-field"><label for="<?php echo esc_attr( $id ); ?>-shape"><?php esc_html_e( 'Shape of the area', 'landscape-material-calculator' ); ?></label>
			<select id="<?php echo esc_attr( $id ); ?>-shape" name="shape">
				<option value="rect"><?php esc_html_e( 'Rectangle', 'landscape-material-calculator' ); ?></option>
				<option value="circle"><?php esc_html_e( 'Circle', 'landscape-material-calculator' ); ?></option>
				<option value="triangle"><?php esc_html_e( 'Triangle', 'landscape-material-calculator' ); ?></option>
				<option value="area"><?php esc_html_e( 'I know the area', 'landscape-material-calculator' ); ?></option>
			</select></p>
			<?php
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $field() escapes every part.
			echo $field( 'length', __( 'Length', 'landscape-material-calculator' ), $len );
			echo $field( 'width', __( 'Width', 'landscape-material-calculator' ), $len );
			echo $field( 'diameter', __( 'Diameter', 'landscape-material-calculator' ), $len );
			echo $field( 'base', __( 'Base', 'landscape-material-calculator' ), $len );
			echo $field( 'height', __( 'Height (base to tip)', 'landscape-material-calculator' ), $len );
			echo $field( 'area', __( 'Area', 'landscape-material-calculator' ), $area );
			echo $field( 'depth', __( 'Depth', 'landscape-material-calculator' ), $dep, $first['depth'] ? (string) $first['depth'] : '' );
			// phpcs:enable
			?>
		</form>
		<div class="lmcalc-result" aria-live="polite"><p><?php esc_html_e( 'Enter the size of the area and the depth.', 'landscape-material-calculator' ); ?></p></div>
		<?php if ( $s['quote_url'] ) : ?>
			<p class="lmcalc-quote"><a class="wp-element-button" href="<?php echo esc_url( $s['quote_url'] ); ?>"><?php echo esc_html( $s['quote_text'] ); ?></a></p>
		<?php endif; ?>
		<noscript><p><?php esc_html_e( 'This calculator needs JavaScript.', 'landscape-material-calculator' ); ?></p></noscript>
	</div>
	<?php
	return (string) ob_get_clean();
}
