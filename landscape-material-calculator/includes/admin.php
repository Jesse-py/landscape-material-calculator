<?php
/**
 * Settings > Material Calculator.
 *
 * @package LandscapeMaterialCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the option. The option is created on first visit to the page, so the first save goes through
 * update_option() and the sanitize callback runs once.
 */
function lmcalc_admin_init() {
	register_setting(
		'lmcalc',
		'lmcalc_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'lmcalc_sanitize',
			'default'           => lmcalc_defaults(),
		)
	);
}
add_action( 'admin_init', 'lmcalc_admin_init' );

/**
 * Adds the settings page.
 */
function lmcalc_admin_menu() {
	add_options_page(
		__( 'Material Calculator', 'landscape-material-calculator' ),
		__( 'Material Calculator', 'landscape-material-calculator' ),
		'manage_options',
		'landscape-material-calculator',
		'lmcalc_settings_page'
	);
}
add_action( 'admin_menu', 'lmcalc_admin_menu' );

/**
 * Prints the settings page.
 */
function lmcalc_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( false === get_option( 'lmcalc_settings', false ) ) {
		add_option( 'lmcalc_settings', lmcalc_defaults() );
	}
	$s      = lmcalc_get_settings();
	$metric = 'metric' === $s['units'];
	$vol    = $metric ? __( 'cubic meter', 'landscape-material-calculator' ) : __( 'cubic yard', 'landscape-material-calculator' );
	$wt     = $metric ? __( 'tonne', 'landscape-material-calculator' ) : __( 'ton', 'landscape-material-calculator' );
	$dens   = $metric ? __( 'tonnes per m³', 'landscape-material-calculator' ) : __( 'tons per yd³', 'landscape-material-calculator' );
	$dep    = $metric ? __( 'cm', 'landscape-material-calculator' ) : __( 'in', 'landscape-material-calculator' );
	$o      = 'lmcalc_settings';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Material Calculator', 'landscape-material-calculator' ); ?></h1>
		<p>
			<?php esc_html_e( 'Add the calculator to any page with the Material Calculator block, or with this shortcode:', 'landscape-material-calculator' ); ?>
			<code>[landscape_material_calculator]</code>
			<?php esc_html_e( 'For one material only:', 'landscape-material-calculator' ); ?>
			<code>[landscape_material_calculator material="gravel"]</code>
			(mulch, topsoil, compost, gravel, sand, concrete)
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'lmcalc' ); ?>
			<h2><?php esc_html_e( 'General', 'landscape-material-calculator' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Units', 'landscape-material-calculator' ); ?></th>
					<td>
						<label><input type="radio" name="<?php echo esc_attr( $o ); ?>[units]" value="imperial" <?php checked( ! $metric ); ?>> <?php esc_html_e( 'Feet, inches, cubic yards, tons', 'landscape-material-calculator' ); ?></label><br>
						<label><input type="radio" name="<?php echo esc_attr( $o ); ?>[units]" value="metric" <?php checked( $metric ); ?>> <?php esc_html_e( 'Meters, centimeters, cubic meters, tonnes', 'landscape-material-calculator' ); ?></label>
						<p class="description"><?php esc_html_e( 'Switching converts densities and depths for you. Re-check your prices afterwards, since they are per unit sold.', 'landscape-material-calculator' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="lmcalc-currency"><?php esc_html_e( 'Currency symbol', 'landscape-material-calculator' ); ?></label></th>
					<td><input id="lmcalc-currency" class="small-text" type="text" name="<?php echo esc_attr( $o ); ?>[currency]" value="<?php echo esc_attr( $s['currency'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="lmcalc-waste"><?php esc_html_e( 'Extra for waste and settling (%)', 'landscape-material-calculator' ); ?></label></th>
					<td><input id="lmcalc-waste" class="small-text" type="number" min="0" max="50" step="any" name="<?php echo esc_attr( $o ); ?>[waste]" value="<?php echo esc_attr( $s['waste'] ); ?>">
						<p class="description"><?php esc_html_e( 'Added to every result. 0 shows the exact amount.', 'landscape-material-calculator' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="lmcalc-quote-url"><?php esc_html_e( 'Quote or order page (optional)', 'landscape-material-calculator' ); ?></label></th>
					<td><input id="lmcalc-quote-url" class="regular-text" type="url" name="<?php echo esc_attr( $o ); ?>[quote_url]" value="<?php echo esc_attr( $s['quote_url'] ); ?>" placeholder="https://">
						<p><label for="lmcalc-quote-text"><?php esc_html_e( 'Button text', 'landscape-material-calculator' ); ?></label>
						<input id="lmcalc-quote-text" class="regular-text" type="text" name="<?php echo esc_attr( $o ); ?>[quote_text]" value="<?php echo esc_attr( $s['quote_text'] ); ?>"></p>
						<p class="description"><?php esc_html_e( 'A button under the result that links to your contact, quote, or shop page. Leave blank for no button.', 'landscape-material-calculator' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Concrete bags', 'landscape-material-calculator' ); ?></th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $o ); ?>[bags]" value="1" <?php checked( $s['bags'] ); ?>> <?php esc_html_e( 'Also show how many 40, 60, and 80 lb bags of premix it takes (imperial only)', 'landscape-material-calculator' ); ?></label></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Materials', 'landscape-material-calculator' ); ?></h2>
			<p><?php esc_html_e( 'Price, rounding, and minimum are optional. With no price, the calculator shows quantities only.', 'landscape-material-calculator' ); ?></p>
			<table class="widefat striped" role="presentation">
				<thead><tr>
					<th><?php esc_html_e( 'Show', 'landscape-material-calculator' ); ?></th>
					<th><?php esc_html_e( 'Name', 'landscape-material-calculator' ); ?></th>
					<th><?php esc_html_e( 'Sold by', 'landscape-material-calculator' ); ?></th>
					<?php /* translators: %s: density unit */ ?>
					<th><?php echo esc_html( sprintf( __( 'Density (%s)', 'landscape-material-calculator' ), $dens ) ); ?></th>
					<?php /* translators: %s: depth unit */ ?>
					<th><?php echo esc_html( sprintf( __( 'Usual depth (%s)', 'landscape-material-calculator' ), $dep ) ); ?></th>
					<th><?php esc_html_e( 'Price per unit sold', 'landscape-material-calculator' ); ?></th>
					<th><?php esc_html_e( 'Round orders up to', 'landscape-material-calculator' ); ?></th>
					<th><?php esc_html_e( 'Minimum order', 'landscape-material-calculator' ); ?></th>
				</tr></thead>
				<tbody>
				<?php
				foreach ( lmcalc_material_keys() as $key ) :
					$m = $s['materials'][ $key ];
					$n = $o . '[materials][' . $key . ']';
					?>
					<tr>
						<td><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $m['enabled'] ); ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: material */ __( 'Show %s', 'landscape-material-calculator' ), $m['label'] ) ); ?>"></td>
						<td><input type="text" name="<?php echo esc_attr( $n ); ?>[label]" value="<?php echo esc_attr( $m['label'] ); ?>"></td>
						<td>
							<?php if ( 'concrete' === $key ) : ?>
								<?php echo esc_html( $vol ); ?><input type="hidden" name="<?php echo esc_attr( $n ); ?>[sold_by]" value="volume">
							<?php else : ?>
								<select name="<?php echo esc_attr( $n ); ?>[sold_by]">
									<option value="volume" <?php selected( $m['sold_by'], 'volume' ); ?>><?php echo esc_html( $vol ); ?></option>
									<option value="weight" <?php selected( $m['sold_by'], 'weight' ); ?>><?php echo esc_html( $wt ); ?></option>
								</select>
							<?php endif; ?>
						</td>
						<td><input class="small-text" type="number" min="0" max="10" step="any" name="<?php echo esc_attr( $n ); ?>[density]" value="<?php echo esc_attr( $m['density'] ? $m['density'] : '' ); ?>"></td>
						<td><input class="small-text" type="number" min="0" max="100" step="any" name="<?php echo esc_attr( $n ); ?>[depth]" value="<?php echo esc_attr( $m['depth'] ? $m['depth'] : '' ); ?>"></td>
						<td style="white-space:nowrap"><?php echo esc_html( $s['currency'] ); ?> <input class="small-text" type="number" min="0" step="any" name="<?php echo esc_attr( $n ); ?>[price]" value="<?php echo esc_attr( $m['price'] ? $m['price'] : '' ); ?>"></td>
						<td>
							<select name="<?php echo esc_attr( $n ); ?>[step]">
								<?php
								$steps = array(
									'0'    => __( 'No rounding', 'landscape-material-calculator' ),
									'0.1'  => '0.1',
									'0.25' => '1/4',
									'0.5'  => '1/2',
									'1'    => __( 'Whole units', 'landscape-material-calculator' ),
								);
								foreach ( $steps as $v => $label ) :
									?>
									<option value="<?php echo esc_attr( $v ); ?>" <?php selected( (string) (float) $m['step'], (string) (float) $v ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><input class="small-text" type="number" min="0" step="any" name="<?php echo esc_attr( $n ); ?>[min]" value="<?php echo esc_attr( $m['min'] ? $m['min'] : '' ); ?>"></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description"><?php esc_html_e( 'Density turns volume into weight. The gravel and sand defaults (1.35 tons per cubic yard, 1.6 tonnes per m³) match a 50 lb bag filling half a cubic foot; use the figure for your own stone, since it varies by type and moisture.', 'landscape-material-calculator' ); ?></p>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
