<?php
/**
 * Trust badges template
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="cro-trust-badges">
	<div class="cro-trust-badge">
		<span class="cro-trust-badge-icon"><?php echo CRO_Icons::svg( 'lock', array( 'class' => 'cro-ico' ) ); ?></span>
		<span class="cro-trust-badge-text"><?php esc_html_e( 'Secure Checkout', 'cro-toolkit' ); ?></span>
	</div>
	<div class="cro-trust-badge">
		<span class="cro-trust-badge-icon"><?php echo CRO_Icons::svg( 'truck', array( 'class' => 'cro-ico' ) ); ?></span>
		<span class="cro-trust-badge-text"><?php esc_html_e( 'Free Shipping', 'cro-toolkit' ); ?></span>
	</div>
	<div class="cro-trust-badge">
		<span class="cro-trust-badge-icon"><?php echo CRO_Icons::svg( 'undo', array( 'class' => 'cro-ico' ) ); ?></span>
		<span class="cro-trust-badge-text"><?php esc_html_e( 'Easy Returns', 'cro-toolkit' ); ?></span>
	</div>
</div>
