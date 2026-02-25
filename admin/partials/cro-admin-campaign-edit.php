<?php
/**
 * Admin campaign edit page
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$campaign_id = isset( $_GET['campaign_id'] ) ? absint( $_GET['campaign_id'] ) : 0;
$campaign = $campaign_id > 0 ? CRO_Campaign::get( $campaign_id ) : null;

// Show success notice if campaign was duplicated
if ( isset( $_GET['duplicated'] ) && $_GET['duplicated'] == '1' ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campaign duplicated successfully!', 'cro-toolkit' ) . '</p></div>';
}

// Get targeting rules with defaults.
$default_targeting = CRO_Activator::get_default_targeting_rules();
$targeting = isset( $campaign['targeting_rules'] ) ? $campaign['targeting_rules'] : ( isset( $campaign['targeting'] ) ? $campaign['targeting'] : $default_targeting );
$targeting = wp_parse_args( $targeting, $default_targeting );

// Handle save
if ( isset( $_POST['cro_save_campaign'] ) && isset( $_POST['cro_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['cro_nonce'] ), 'cro_save_campaign' ) ) {
	$data = array(
		'name'          => sanitize_text_field( wp_unslash( $_POST['campaign_name'] ?? '' ) ),
		'campaign_type' => sanitize_text_field( wp_unslash( $_POST['campaign_type'] ?? 'exit_intent' ) ),
		'status'        => sanitize_text_field( wp_unslash( $_POST['campaign_status'] ?? 'draft' ) ),
	);

	// Process targeting data.
	if ( isset( $_POST['targeting'] ) && is_array( $_POST['targeting'] ) ) {
		$targeting_data = array();

		// Behavior.
		if ( isset( $_POST['targeting']['behavior'] ) ) {
			$behavior = array();
			$behavior['min_time_on_page']   = isset( $_POST['targeting']['behavior']['min_time_on_page'] ) ? absint( $_POST['targeting']['behavior']['min_time_on_page'] ) : 0;
			$behavior['min_scroll_depth']    = isset( $_POST['targeting']['behavior']['min_scroll_depth'] ) ? absint( $_POST['targeting']['behavior']['min_scroll_depth'] ) : 0;
			$behavior['require_interaction'] = ! empty( $_POST['targeting']['behavior']['require_interaction'] );
			$behavior['cart_status']         = isset( $_POST['targeting']['behavior']['cart_status'] ) ? sanitize_text_field( wp_unslash( $_POST['targeting']['behavior']['cart_status'] ) ) : 'any';
			$behavior['cart_min_value']      = isset( $_POST['targeting']['behavior']['cart_min_value'] ) ? floatval( $_POST['targeting']['behavior']['cart_min_value'] ) : 0;
			$behavior['cart_max_value']      = isset( $_POST['targeting']['behavior']['cart_max_value'] ) ? floatval( $_POST['targeting']['behavior']['cart_max_value'] ) : 0;
			$targeting_data['behavior']     = $behavior;
		}

		// Visitor.
		if ( isset( $_POST['targeting']['visitor'] ) ) {
			$visitor = array();
			$visitor['type'] = isset( $_POST['targeting']['visitor']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['targeting']['visitor']['type'] ) ) : 'all';
			$targeting_data['visitor'] = $visitor;
		}

		// Device.
		if ( isset( $_POST['targeting']['device'] ) ) {
			$device = array();
			$device['desktop'] = ! empty( $_POST['targeting']['device']['desktop'] );
			$device['mobile']  = ! empty( $_POST['targeting']['device']['mobile'] );
			$device['tablet']  = ! empty( $_POST['targeting']['device']['tablet'] );
			$targeting_data['device'] = $device;
		}

		// Merge with existing targeting (preserve pages, schedule, etc.).
		$data['targeting_rules'] = wp_parse_args( $targeting_data, $targeting );
	}

	if ( $campaign_id > 0 ) {
		CRO_Campaign::update( $campaign_id, $data );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campaign updated.', 'cro-toolkit' ) . '</p></div>';
		$campaign = CRO_Campaign::get( $campaign_id );
		// Reload targeting after update.
		$targeting = isset( $campaign['targeting_rules'] ) ? $campaign['targeting_rules'] : ( isset( $campaign['targeting'] ) ? $campaign['targeting'] : $default_targeting );
		$targeting = wp_parse_args( $targeting, $default_targeting );
	} else {
		$new_id = CRO_Campaign::create( $data );
		if ( $new_id ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campaign created.', 'cro-toolkit' ) . '</p></div>';
			wp_safe_redirect( admin_url( 'admin.php?page=cro-campaign-edit&campaign_id=' . $new_id ) );
			exit;
		}
	}
}
?>

<h1><?php echo esc_html( $campaign_id > 0 ? __( 'Edit Campaign', 'cro-toolkit' ) : __( 'Add New Campaign', 'cro-toolkit' ) ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=cro-campaigns' ) ); ?>" class="page-title-action"><?php esc_html_e( '&larr; Back to Campaigns', 'cro-toolkit' ); ?></a>

	<form method="post">
		<?php wp_nonce_field( 'cro_save_campaign', 'cro_nonce' ); ?>

		<div class="cro-settings-section">
			<div class="cro-fields-grid cro-fields-grid--1col">
				<div class="cro-field cro-col-12">
					<label for="campaign_name" class="cro-field__label"><?php esc_html_e( 'Campaign Name', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<input type="text" id="campaign_name" name="campaign_name" value="<?php echo esc_attr( $campaign ? $campaign['name'] : '' ); ?>" class="regular-text" required>
					</div>
				</div>
				<div class="cro-field cro-col-12">
					<label for="campaign_type" class="cro-field__label"><?php esc_html_e( 'Campaign Type', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<select id="campaign_type" name="campaign_type" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Exit Intent', 'cro-toolkit' ); ?>">
							<option value="exit_intent" <?php selected( $campaign ? ( $campaign['campaign_type'] ?? 'exit_intent' ) : 'exit_intent', 'exit_intent' ); ?>><?php esc_html_e( 'Exit Intent', 'cro-toolkit' ); ?></option>
							<option value="scroll_trigger" <?php selected( $campaign ? ( $campaign['campaign_type'] ?? '' ) : '', 'scroll_trigger' ); ?>><?php esc_html_e( 'Scroll Trigger', 'cro-toolkit' ); ?></option>
							<option value="time_trigger" <?php selected( $campaign ? ( $campaign['campaign_type'] ?? '' ) : '', 'time_trigger' ); ?>><?php esc_html_e( 'Time Trigger', 'cro-toolkit' ); ?></option>
							<option value="manual" <?php selected( $campaign ? ( $campaign['campaign_type'] ?? '' ) : '', 'manual' ); ?>><?php esc_html_e( 'Manual', 'cro-toolkit' ); ?></option>
						</select>
					</div>
				</div>
				<div class="cro-field cro-col-12">
					<label for="campaign_status" class="cro-field__label"><?php esc_html_e( 'Status', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<select id="campaign_status" name="campaign_status" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Draft', 'cro-toolkit' ); ?>">
							<option value="draft" <?php selected( $campaign ? $campaign['status'] : 'draft', 'draft' ); ?>><?php esc_html_e( 'Draft', 'cro-toolkit' ); ?></option>
							<option value="active" <?php selected( $campaign ? $campaign['status'] : 'draft', 'active' ); ?>><?php esc_html_e( 'Active', 'cro-toolkit' ); ?></option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- Behavioral Targeting Section -->
		<div class="cro-settings-section">
			<h2>
				<?php echo CRO_Icons::svg( 'user', array( 'class' => 'cro-ico' ) ); ?>
				<?php esc_html_e( 'Behavioral Targeting', 'cro-toolkit' ); ?>
			</h2>
			<p class="cro-section-description">
				<?php esc_html_e( 'Show campaigns based on how visitors interact with your site, not just which page they are on.', 'cro-toolkit' ); ?>
			</p>

			<div class="cro-fields-grid cro-fields-grid--1col">
				<div class="cro-field cro-col-12">
					<label class="cro-field__label"><?php esc_html_e( 'Time on Page', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<label>
							<?php esc_html_e( 'Minimum time before trigger:', 'cro-toolkit' ); ?>
							<input type="number" name="targeting[behavior][min_time_on_page]"
								value="<?php echo esc_attr( $targeting['behavior']['min_time_on_page'] ?? 0 ); ?>"
								min="0" max="300" class="small-text" />
							<?php esc_html_e( 'seconds', 'cro-toolkit' ); ?>
						</label>
					</div>
					<span class="cro-help"><?php esc_html_e( 'Wait until visitor has been on page this long. 0 = no minimum.', 'cro-toolkit' ); ?></span>
				</div>
				<div class="cro-field cro-col-12">
					<label class="cro-field__label"><?php esc_html_e( 'Scroll Depth', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<label>
							<?php esc_html_e( 'Minimum scroll depth:', 'cro-toolkit' ); ?>
							<input type="number" name="targeting[behavior][min_scroll_depth]"
								value="<?php echo esc_attr( $targeting['behavior']['min_scroll_depth'] ?? 0 ); ?>"
								min="0" max="100" class="small-text" />
							%
						</label>
					</div>
					<span class="cro-help"><?php esc_html_e( 'Visitor must scroll this far before trigger. 0 = no minimum.', 'cro-toolkit' ); ?></span>
				</div>
				<div class="cro-field cro-col-12">
					<div class="cro-field__control">
						<label>
							<input type="checkbox" name="targeting[behavior][require_interaction]" value="1"
								<?php checked( ! empty( $targeting['behavior']['require_interaction'] ) ); ?> />
							<?php esc_html_e( 'Require at least one click/tap before triggering', 'cro-toolkit' ); ?>
						</label>
					</div>
					<span class="cro-help"><?php esc_html_e( 'Ensures visitor is engaged, not just bouncing.', 'cro-toolkit' ); ?></span>
				</div>
				<div class="cro-field cro-col-12">
					<label for="cart_status" class="cro-field__label"><?php esc_html_e( 'Cart Status', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<select id="cart_status" name="targeting[behavior][cart_status]" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Any (show regardless of cart)', 'cro-toolkit' ); ?>">
							<option value="any" <?php selected( $targeting['behavior']['cart_status'] ?? 'any', 'any' ); ?>>
								<?php esc_html_e( 'Any (show regardless of cart)', 'cro-toolkit' ); ?>
							</option>
							<option value="has_items" <?php selected( $targeting['behavior']['cart_status'] ?? '', 'has_items' ); ?>>
								<?php esc_html_e( 'Cart has items', 'cro-toolkit' ); ?>
							</option>
							<option value="empty" <?php selected( $targeting['behavior']['cart_status'] ?? '', 'empty' ); ?>>
								<?php esc_html_e( 'Cart is empty', 'cro-toolkit' ); ?>
							</option>
						</select>
					</div>
				</div>
				<div class="cro-field cro-col-12">
					<label class="cro-field__label"><?php esc_html_e( 'Cart Value', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<label>
							<?php esc_html_e( 'Minimum:', 'cro-toolkit' ); ?>
							<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
							<input type="number" name="targeting[behavior][cart_min_value]"
								value="<?php echo esc_attr( $targeting['behavior']['cart_min_value'] ?? 0 ); ?>"
								min="0" step="0.01" class="small-text" />
						</label>
						&nbsp;&nbsp;
						<label>
							<?php esc_html_e( 'Maximum:', 'cro-toolkit' ); ?>
							<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
							<input type="number" name="targeting[behavior][cart_max_value]"
								value="<?php echo esc_attr( $targeting['behavior']['cart_max_value'] ?? 0 ); ?>"
								min="0" step="0.01" class="small-text" />
						</label>
					</div>
					<span class="cro-help"><?php esc_html_e( 'Set to 0 for no limit.', 'cro-toolkit' ); ?></span>
				</div>
				<div class="cro-field cro-col-12">
					<label for="visitor_type" class="cro-field__label"><?php esc_html_e( 'Visitor Type', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<select id="visitor_type" name="targeting[visitor][type]" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'All visitors', 'cro-toolkit' ); ?>">
							<option value="all" <?php selected( $targeting['visitor']['type'] ?? 'all', 'all' ); ?>>
								<?php esc_html_e( 'All visitors', 'cro-toolkit' ); ?>
							</option>
							<option value="new" <?php selected( $targeting['visitor']['type'] ?? '', 'new' ); ?>>
								<?php esc_html_e( 'New visitors only (first visit)', 'cro-toolkit' ); ?>
							</option>
							<option value="returning" <?php selected( $targeting['visitor']['type'] ?? '', 'returning' ); ?>>
								<?php esc_html_e( 'Returning visitors only', 'cro-toolkit' ); ?>
							</option>
						</select>
					</div>
				</div>
				<div class="cro-field cro-col-12">
					<label class="cro-field__label"><?php esc_html_e( 'Device', 'cro-toolkit' ); ?></label>
					<div class="cro-field__control">
						<label>
							<input type="checkbox" name="targeting[device][desktop]" value="1"
								<?php checked( ! empty( $targeting['device']['desktop'] ) ); ?> />
							<?php esc_html_e( 'Desktop', 'cro-toolkit' ); ?>
						</label>
						&nbsp;&nbsp;
						<label>
							<input type="checkbox" name="targeting[device][mobile]" value="1"
								<?php checked( ! empty( $targeting['device']['mobile'] ) ); ?> />
							<?php esc_html_e( 'Mobile', 'cro-toolkit' ); ?>
						</label>
						&nbsp;&nbsp;
						<label>
							<input type="checkbox" name="targeting[device][tablet]" value="1"
								<?php checked( ! empty( $targeting['device']['tablet'] ) ); ?> />
							<?php esc_html_e( 'Tablet', 'cro-toolkit' ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>

		<?php submit_button( __( 'Save Campaign', 'cro-toolkit' ), 'primary', 'cro_save_campaign' ); ?>
	</form>
