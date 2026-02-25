<?php
/**
 * Targeting controls partial for the campaign builder.
 * Expects $campaign_data (object with optional targeting_rules array).
 */
$targeting = ( is_object( $campaign_data ) && isset( $campaign_data->targeting_rules ) && is_array( $campaign_data->targeting_rules ) )
	? $campaign_data->targeting_rules
	: array();
$pages    = isset( $targeting['pages'] ) && is_array( $targeting['pages'] ) ? $targeting['pages'] : array();
$behavior = isset( $targeting['behavior'] ) && is_array( $targeting['behavior'] ) ? $targeting['behavior'] : array();
$visitor  = isset( $targeting['visitor'] ) && is_array( $targeting['visitor'] ) ? $targeting['visitor'] : array();
$device   = isset( $targeting['device'] ) && is_array( $targeting['device'] ) ? $targeting['device'] : array();
$include_list = isset( $pages['include'] ) && is_array( $pages['include'] ) ? $pages['include'] : array();
$exclude_list = isset( $pages['exclude'] ) && is_array( $pages['exclude'] ) ? $pages['exclude'] : array();
$page_mode    = $targeting['page_mode'] ?? ( ( ! empty( $include_list ) ? 'include' : ( ! empty( $exclude_list ) ? 'exclude' : 'all' ) ) );
$audience_mode = $targeting['audience_mode'] ?? 'all';
$currency_sym  = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';
?>

<div class="cro-targeting-controls">

	<!-- Targeting Mode -->
	<div class="cro-control-group">
		<label><?php esc_html_e( 'Show campaign to:', 'cro-toolkit' ); ?></label>
		<div class="cro-targeting-mode">
			<label class="cro-radio-card">
				<input type="radio" name="targeting-mode" value="all" <?php checked( $audience_mode, 'all' ); ?> />
				<span class="cro-radio-content">
					<strong><?php esc_html_e( 'Everyone', 'cro-toolkit' ); ?></strong>
					<span><?php esc_html_e( 'All visitors on selected pages', 'cro-toolkit' ); ?></span>
				</span>
			</label>
			<label class="cro-radio-card">
				<input type="radio" name="targeting-mode" value="rules" <?php checked( $audience_mode, 'rules' ); ?> />
				<span class="cro-radio-content">
					<strong><?php esc_html_e( 'Specific Visitors', 'cro-toolkit' ); ?></strong>
					<span><?php esc_html_e( 'Based on rules below', 'cro-toolkit' ); ?></span>
				</span>
			</label>
		</div>
	</div>

	<!-- Page Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'file', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Page Targeting', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Show on pages:', 'cro-toolkit' ); ?></label>
			<select id="targeting-page-mode" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'All pages', 'cro-toolkit' ); ?>">
				<option value="all" <?php selected( $page_mode, 'all' ); ?>><?php esc_html_e( 'All pages', 'cro-toolkit' ); ?></option>
				<option value="include" <?php selected( $page_mode, 'include' ); ?>><?php esc_html_e( 'Only specific pages', 'cro-toolkit' ); ?></option>
				<option value="exclude" <?php selected( $page_mode, 'exclude' ); ?>><?php esc_html_e( 'All pages except...', 'cro-toolkit' ); ?></option>
			</select>
		</div>

		<div class="cro-page-selector <?php echo $page_mode === 'include' ? '' : 'cro-is-hidden'; ?>" id="page-include-selector">
			<label><?php esc_html_e( 'Include these pages:', 'cro-toolkit' ); ?></label>
			<div class="cro-checkbox-grid">
				<label><input type="checkbox" name="pages[]" value="home" <?php checked( in_array( 'home', $include_list, true ) ); ?> /> <?php esc_html_e( 'Homepage', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="pages[]" value="shop" <?php checked( in_array( 'shop', $include_list, true ) ); ?> /> <?php esc_html_e( 'Shop page', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="pages[]" value="product" <?php checked( in_array( 'product', $include_list, true ) ); ?> /> <?php esc_html_e( 'Product pages', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="pages[]" value="category" <?php checked( in_array( 'category', $include_list, true ) ); ?> /> <?php esc_html_e( 'Category pages', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="pages[]" value="cart" <?php checked( in_array( 'cart', $include_list, true ) ); ?> /> <?php esc_html_e( 'Cart page', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="pages[]" value="blog" <?php checked( in_array( 'blog', $include_list, true ) ); ?> /> <?php esc_html_e( 'Blog posts', 'cro-toolkit' ); ?></label>
			</div>

			<div class="cro-specific-pages">
				<label><?php esc_html_e( 'Or select specific pages/products:', 'cro-toolkit' ); ?></label>
				<select id="targeting-specific-pages" multiple class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Search pages or products...', 'cro-toolkit' ); ?>" data-action="cro_search_pages">
					<!-- Populated via AJAX -->
				</select>
			</div>
		</div>

		<div class="cro-page-selector <?php echo $page_mode === 'exclude' ? '' : 'cro-is-hidden'; ?>" id="page-exclude-selector">
			<label><?php esc_html_e( 'Exclude these pages:', 'cro-toolkit' ); ?></label>
			<div class="cro-checkbox-grid">
				<label><input type="checkbox" name="exclude-pages[]" value="checkout" checked disabled />
					<?php esc_html_e( 'Checkout (always excluded)', 'cro-toolkit' ); ?>
				</label>
				<label><input type="checkbox" name="exclude-pages[]" value="cart" <?php checked( in_array( 'cart', $exclude_list, true ) ); ?> /> <?php esc_html_e( 'Cart page', 'cro-toolkit' ); ?></label>
				<label><input type="checkbox" name="exclude-pages[]" value="account" <?php checked( in_array( 'account', $exclude_list, true ) ); ?> /> <?php esc_html_e( 'My Account', 'cro-toolkit' ); ?></label>
			</div>
		</div>
	</div>

	<!-- Visitor Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'user', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Visitor Targeting', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Visitor type:', 'cro-toolkit' ); ?></label>
<select id="targeting-visitor-type" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'All visitors', 'cro-toolkit' ); ?>">
			<option value="all" <?php selected( $visitor['type'] ?? 'all', 'all' ); ?>><?php esc_html_e( 'All visitors', 'cro-toolkit' ); ?></option>
				<option value="new" <?php selected( $visitor['type'] ?? 'all', 'new' ); ?>><?php esc_html_e( 'New visitors only (first visit)', 'cro-toolkit' ); ?></option>
				<option value="returning" <?php selected( $visitor['type'] ?? 'all', 'returning' ); ?>><?php esc_html_e( 'Returning visitors only', 'cro-toolkit' ); ?></option>
				<option value="logged_in" <?php selected( $visitor['type'] ?? 'all', 'logged_in' ); ?>><?php esc_html_e( 'Logged in users only', 'cro-toolkit' ); ?></option>
				<option value="logged_out" <?php selected( $visitor['type'] ?? 'all', 'logged_out' ); ?>><?php esc_html_e( 'Logged out visitors only', 'cro-toolkit' ); ?></option>
			</select>
		</div>

		<div class="cro-control-group">
			<label>
				<input type="checkbox" id="targeting-exclude-purchased" <?php checked( ! empty( $targeting['exclude_purchased'] ) ); ?> />
				<?php esc_html_e( 'Exclude customers who already purchased', 'cro-toolkit' ); ?>
			</label>
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Session count:', 'cro-toolkit' ); ?></label>
			<div class="cro-range-inputs">
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Min sessions:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-min-sessions" min="1" placeholder="1" value="<?php echo esc_attr( (string) ( $visitor['min_sessions'] ?? $targeting['min_sessions'] ?? '' ) ); ?>" />
				</div>
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Max sessions:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-max-sessions" min="1" placeholder="∞" value="<?php echo esc_attr( (string) ( $visitor['max_sessions'] ?? $targeting['max_sessions'] ?? '' ) ); ?>" />
				</div>
			</div>
			<p class="cro-hint"><?php esc_html_e( 'Target visitors based on how many times they have visited', 'cro-toolkit' ); ?></p>
		</div>
	</div>

	<!-- Device Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'smartphone', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Device Targeting', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-device-options">
			<label class="cro-device-option">
				<input type="checkbox" name="devices[]" value="desktop" <?php checked( $device['desktop'] ?? true ); ?> />
				<span class="cro-device-icon"><?php echo CRO_Icons::svg( 'monitor', array( 'class' => 'cro-ico' ) ); ?></span>
				<span><?php esc_html_e( 'Desktop', 'cro-toolkit' ); ?></span>
			</label>
			<label class="cro-device-option">
				<input type="checkbox" name="devices[]" value="tablet" <?php checked( $device['tablet'] ?? true ); ?> />
				<span class="cro-device-icon"><?php echo CRO_Icons::svg( 'smartphone', array( 'class' => 'cro-ico' ) ); ?></span>
				<span><?php esc_html_e( 'Tablet', 'cro-toolkit' ); ?></span>
			</label>
			<label class="cro-device-option">
				<input type="checkbox" name="devices[]" value="mobile" <?php checked( $device['mobile'] ?? true ); ?> />
				<span class="cro-device-icon"><?php echo CRO_Icons::svg( 'smartphone', array( 'class' => 'cro-ico' ) ); ?></span>
				<span><?php esc_html_e( 'Mobile', 'cro-toolkit' ); ?></span>
			</label>
		</div>
	</div>

	<!-- Cart Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'shopping-cart', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Cart Targeting', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Cart status:', 'cro-toolkit' ); ?></label>
			<select id="targeting-cart-status" class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Any (with or without items)', 'cro-toolkit' ); ?>">
				<option value="any" <?php selected( $behavior['cart_status'] ?? 'any', 'any' ); ?>><?php esc_html_e( 'Any (with or without items)', 'cro-toolkit' ); ?></option>
				<option value="has_items" <?php selected( $behavior['cart_status'] ?? 'any', 'has_items' ); ?>><?php esc_html_e( 'Has items in cart', 'cro-toolkit' ); ?></option>
				<option value="empty" <?php selected( $behavior['cart_status'] ?? 'any', 'empty' ); ?>><?php esc_html_e( 'Cart is empty', 'cro-toolkit' ); ?></option>
			</select>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Cart value:', 'cro-toolkit' ); ?></label>
			<div class="cro-range-inputs">
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Min:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-cart-min" min="0" step="1" placeholder="0" value="<?php echo esc_attr( (string) ( $behavior['cart_min_value'] ?? 0 ) ); ?>" />
					<span class="cro-currency"><?php echo esc_html( $currency_sym ); ?></span>
				</div>
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Max:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-cart-max" min="0" step="1" placeholder="∞" value="<?php echo esc_attr( (string) ( $behavior['cart_max_value'] ?? '' ) ); ?>" />
					<span class="cro-currency"><?php echo esc_html( $currency_sym ); ?></span>
				</div>
			</div>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Cart item count:', 'cro-toolkit' ); ?></label>
			<div class="cro-range-inputs">
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Min items:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-cart-min-items" min="0" placeholder="0" value="<?php echo esc_attr( (string) ( $behavior['cart_min_items'] ?? '' ) ); ?>" />
				</div>
				<div class="cro-range-input">
					<span><?php esc_html_e( 'Max items:', 'cro-toolkit' ); ?></span>
					<input type="number" id="targeting-cart-max-items" min="0" placeholder="∞" value="<?php echo esc_attr( (string) ( $behavior['cart_max_items'] ?? '' ) ); ?>" />
				</div>
			</div>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Cart contains product:', 'cro-toolkit' ); ?></label>
			<select id="targeting-cart-contains" multiple class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Any product', 'cro-toolkit' ); ?>" data-action="cro_search_products">
				<!-- Populated via AJAX -->
			</select>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Cart contains category:', 'cro-toolkit' ); ?></label>
			<select id="targeting-cart-category" multiple class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Any category', 'cro-toolkit' ); ?>">
				<?php
				if ( taxonomy_exists( 'product_cat' ) ) {
					$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
					if ( ! is_wp_error( $categories ) ) {
						$selected_cats = isset( $behavior['cart_contains_category'] ) && is_array( $behavior['cart_contains_category'] ) ? $behavior['cart_contains_category'] : array();
						foreach ( $categories as $cat ) {
							$sel = in_array( (string) $cat->term_id, $selected_cats, true ) ? ' selected' : '';
							echo '<option value="' . esc_attr( (string) $cat->term_id ) . '"' . $sel . '>' . esc_html( $cat->name ) . '</option>';
						}
					}
				}
				?>
			</select>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Exclude if cart contains product:', 'cro-toolkit' ); ?></label>
			<select id="targeting-cart-exclude-product" multiple class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'None', 'cro-toolkit' ); ?>" data-action="cro_search_products">
				<!-- Populated via AJAX -->
			</select>
			<p class="cro-hint"><?php esc_html_e( 'Do not show when cart contains any of these products', 'cro-toolkit' ); ?></p>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label><?php esc_html_e( 'Exclude if cart contains category:', 'cro-toolkit' ); ?></label>
			<select id="targeting-cart-exclude-category" multiple class="cro-selectwoo" data-placeholder="<?php esc_attr_e( 'None', 'cro-toolkit' ); ?>">
				<?php
				if ( taxonomy_exists( 'product_cat' ) ) {
					$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
					if ( ! is_wp_error( $categories ) ) {
						$exclude_cats = isset( $behavior['cart_exclude_category'] ) && is_array( $behavior['cart_exclude_category'] ) ? $behavior['cart_exclude_category'] : array();
						foreach ( $categories as $cat ) {
							$sel = in_array( (string) $cat->term_id, $exclude_cats, true ) ? ' selected' : '';
							echo '<option value="' . esc_attr( (string) $cat->term_id ) . '"' . $sel . '>' . esc_html( $cat->name ) . '</option>';
						}
					}
				}
				?>
			</select>
			<p class="cro-hint"><?php esc_html_e( 'Do not show when cart contains any product from these categories', 'cro-toolkit' ); ?></p>
		</div>

		<div class="cro-control-group cro-conditional" data-show-when="targeting-cart-status=has_items">
			<label>
				<input type="checkbox" id="targeting-cart-has-sale" <?php checked( ! empty( $behavior['cart_has_sale_only'] ) ); ?> />
				<?php esc_html_e( 'Only if cart contains sale items', 'cro-toolkit' ); ?>
			</label>
		</div>
	</div>

	<!-- Behavioral Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'target', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Behavioral Targeting', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Minimum time on page:', 'cro-toolkit' ); ?></label>
			<div class="cro-input-with-suffix">
				<input type="number" id="targeting-min-time" min="0" max="300" value="<?php echo esc_attr( (string) ( $behavior['min_time_on_page'] ?? 0 ) ); ?>" />
				<span class="cro-suffix"><?php esc_html_e( 'seconds (0 = no minimum)', 'cro-toolkit' ); ?></span>
			</div>
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Minimum scroll depth:', 'cro-toolkit' ); ?></label>
			<div class="cro-range-slider">
				<?php $min_scroll = (int) ( $behavior['min_scroll_depth'] ?? 0 ); ?>
				<input type="range" id="targeting-min-scroll" min="0" max="100" value="<?php echo esc_attr( (string) $min_scroll ); ?>" />
				<span class="cro-range-value"><span id="min-scroll-value"><?php echo esc_html( (string) $min_scroll ); ?></span>% (0 = no minimum)</span>
			</div>
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Minimum pages viewed this session:', 'cro-toolkit' ); ?></label>
			<input type="number" id="targeting-min-pages" min="0" value="<?php echo esc_attr( (string) ( $behavior['min_pages_viewed'] ?? $targeting['min_pages_viewed'] ?? 0 ) ); ?>" />
		</div>

		<div class="cro-control-group">
			<label>
				<input type="checkbox" id="targeting-require-interaction" <?php checked( ! empty( $behavior['require_interaction'] ) ); ?> />
				<?php esc_html_e( 'Require at least one click/interaction', 'cro-toolkit' ); ?>
			</label>
			<p class="cro-hint"><?php esc_html_e( 'Ensures visitor is engaged, not just bouncing', 'cro-toolkit' ); ?></p>
		</div>
	</div>

	<!-- Traffic Source Targeting -->
	<div class="cro-rule-section">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'link', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Traffic Source', 'cro-toolkit' ); ?>
		</h3>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'Referrer contains:', 'cro-toolkit' ); ?></label>
			<input type="text" id="targeting-referrer" placeholder="<?php esc_attr_e( 'e.g., google.com, facebook.com', 'cro-toolkit' ); ?>" value="<?php echo esc_attr( (string) ( $targeting['referrer'] ?? '' ) ); ?>" />
			<p class="cro-hint"><?php esc_html_e( 'Show only to visitors coming from specific sites', 'cro-toolkit' ); ?></p>
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'UTM Source:', 'cro-toolkit' ); ?></label>
			<input type="text" id="targeting-utm-source" placeholder="<?php esc_attr_e( 'e.g., newsletter, facebook', 'cro-toolkit' ); ?>" value="<?php echo esc_attr( (string) ( $targeting['utm_source'] ?? '' ) ); ?>" />
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'UTM Medium:', 'cro-toolkit' ); ?></label>
			<input type="text" id="targeting-utm-medium" placeholder="<?php esc_attr_e( 'e.g., email, cpc, social', 'cro-toolkit' ); ?>" value="<?php echo esc_attr( (string) ( $targeting['utm_medium'] ?? '' ) ); ?>" />
		</div>

		<div class="cro-control-group">
			<label><?php esc_html_e( 'UTM Campaign:', 'cro-toolkit' ); ?></label>
			<input type="text" id="targeting-utm-campaign" placeholder="<?php esc_attr_e( 'e.g., summer_sale, black_friday', 'cro-toolkit' ); ?>" value="<?php echo esc_attr( (string) ( $targeting['utm_campaign'] ?? '' ) ); ?>" />
		</div>
	</div>

	<!-- Advanced: Custom Rules Builder -->
	<div class="cro-rule-section cro-advanced">
		<h3>
			<span class="cro-section-icon"><?php echo CRO_Icons::svg( 'settings', array( 'class' => 'cro-ico' ) ); ?></span>
			<?php esc_html_e( 'Advanced Rules', 'cro-toolkit' ); ?>
		</h3>

		<p class="cro-hint"><?php esc_html_e( 'Build custom rules using AND/OR logic for complex targeting scenarios', 'cro-toolkit' ); ?></p>

		<div class="cro-rule-builder" id="advanced-rules">

			<div class="cro-rule-groups">
				<!-- Rule groups will be added here dynamically -->
			</div>

			<button type="button" class="button" id="add-rule-group">
				<?php echo CRO_Icons::svg( 'plus', array( 'class' => 'cro-ico' ) ); ?>
				<?php esc_html_e( 'Add Rule Group (OR)', 'cro-toolkit' ); ?>
			</button>
		</div>

		<!-- Rule Group Template (hidden) -->
		<template id="rule-group-template">
			<div class="cro-rule-group">
				<div class="cro-rule-group-header">
					<span class="cro-rule-group-logic"><?php esc_html_e( 'OR', 'cro-toolkit' ); ?></span>
					<button type="button" class="cro-remove-group" aria-label="<?php esc_attr_e( 'Remove', 'cro-toolkit' ); ?>"><?php echo CRO_Icons::svg( 'x', array( 'class' => 'cro-ico' ) ); ?></button>
				</div>
				<div class="cro-rules-list">
					<!-- Rules will be added here -->
				</div>
				<button type="button" class="button button-small cro-add-rule">
					<?php echo CRO_Icons::svg( 'plus', array( 'class' => 'cro-ico' ) ); ?>
					<?php esc_html_e( 'Add Condition (AND)', 'cro-toolkit' ); ?>
				</button>
			</div>
		</template>

		<!-- Rule Template (hidden) -->
		<template id="rule-template">
			<div class="cro-rule-item">
				<select class="cro-rule-field cro-selectwoo" data-placeholder="<?php esc_attr_e( 'Select field', 'cro-toolkit' ); ?>">
					<optgroup label="<?php esc_attr_e( 'Page', 'cro-toolkit' ); ?>">
						<option value="page.type"><?php esc_html_e( 'Page Type', 'cro-toolkit' ); ?></option>
						<option value="page.id"><?php esc_html_e( 'Page ID', 'cro-toolkit' ); ?></option>
						<option value="page.url"><?php esc_html_e( 'Page URL', 'cro-toolkit' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Visitor', 'cro-toolkit' ); ?>">
						<option value="visitor.is_new"><?php esc_html_e( 'Is New Visitor', 'cro-toolkit' ); ?></option>
						<option value="visitor.session_count"><?php esc_html_e( 'Session Count', 'cro-toolkit' ); ?></option>
						<option value="user.logged_in"><?php esc_html_e( 'Is Logged In', 'cro-toolkit' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Cart', 'cro-toolkit' ); ?>">
						<option value="cart.total"><?php esc_html_e( 'Cart Total', 'cro-toolkit' ); ?></option>
						<option value="cart.item_count"><?php esc_html_e( 'Cart Item Count', 'cro-toolkit' ); ?></option>
						<option value="cart.has_items"><?php esc_html_e( 'Cart Has Items', 'cro-toolkit' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Behavior', 'cro-toolkit' ); ?>">
						<option value="behavior.time_on_page"><?php esc_html_e( 'Time on Page', 'cro-toolkit' ); ?></option>
						<option value="behavior.scroll_depth"><?php esc_html_e( 'Scroll Depth', 'cro-toolkit' ); ?></option>
					</optgroup>
				</select>
				<select class="cro-rule-operator cro-selectwoo" data-placeholder="=">
					<option value="=">=</option>
					<option value="!=">≠</option>
					<option value=">">&gt;</option>
					<option value="<">&lt;</option>
					<option value=">=">&gt;=</option>
					<option value="<=">&lt;=</option>
					<option value="contains"><?php esc_html_e( 'contains', 'cro-toolkit' ); ?></option>
					<option value="not_contains"><?php esc_html_e( 'not contains', 'cro-toolkit' ); ?></option>
				</select>
				<input type="text" class="cro-rule-value" placeholder="<?php esc_attr_e( 'Value', 'cro-toolkit' ); ?>" />
				<button type="button" class="cro-remove-rule" aria-label="<?php esc_attr_e( 'Remove', 'cro-toolkit' ); ?>"><?php echo CRO_Icons::svg( 'x', array( 'class' => 'cro-ico' ) ); ?></button>
			</div>
		</template>
	</div>

</div>
