<?php
/**
 * Admin Presets library page
 *
 * @package CRO_Toolkit
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$presets = class_exists( 'CRO_Presets' ) ? CRO_Presets::get_all() : array();

$preset_applied = isset( $_GET['preset_applied'] ) && (string) $_GET['preset_applied'] === '1';
$applied_message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
$applied_campaign_id = isset( $_GET['campaign_id'] ) ? absint( $_GET['campaign_id'] ) : 0;

$error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
$error_messages = array(
	'invalid_nonce'   => __( 'Invalid security check. Please try again.', 'cro-toolkit' ),
	'unauthorized'    => __( 'You do not have permission to apply presets.', 'cro-toolkit' ),
	'invalid_preset'  => __( 'Preset not found or invalid.', 'cro-toolkit' ),
	'apply_failed'    => __( 'Failed to apply preset.', 'cro-toolkit' ),
);
$feature_labels = array(
	'campaigns'         => __( 'Conversion campaigns', 'cro-toolkit' ),
	'sticky_cart'       => __( 'Sticky add-to-cart', 'cro-toolkit' ),
	'shipping_bar'      => __( 'Free shipping bar', 'cro-toolkit' ),
	'trust_badges'      => __( 'Trust badges', 'cro-toolkit' ),
	'cart_optimizer'    => __( 'Cart optimizer', 'cro-toolkit' ),
	'checkout_optimizer'=> __( 'Checkout optimizer', 'cro-toolkit' ),
	'stock_urgency'     => __( 'Low stock urgency', 'cro-toolkit' ),
);
?>

<div class="cro-admin-presets">
	<p class="cro-presets-intro">
		<?php esc_html_e( 'Apply ready-made configurations for boosters and campaigns. Each preset enables specific features and can create a default campaign.', 'cro-toolkit' ); ?>
	</p>

	<?php if ( $preset_applied && $applied_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $applied_message ); ?>
				<?php if ( $applied_campaign_id ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cro-campaign-edit&campaign_id=' . $applied_campaign_id ) ); ?>"><?php esc_html_e( 'Edit campaign', 'cro-toolkit' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $error && isset( $error_messages[ $error ] ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error_messages[ $error ] ); ?></p></div>
	<?php endif; ?>

	<div class="cro-presets-grid">
		<?php foreach ( $presets as $preset ) : ?>
			<?php
			$features_list = isset( $preset['features'] ) ? (array) $preset['features'] : array();
			$has_campaign  = ! empty( $preset['campaign'] );
			?>
			<div class="cro-preset-card" data-preset-id="<?php echo esc_attr( $preset['id'] ); ?>">
				<div class="cro-preset-card-inner">
					<h3 class="cro-preset-name"><?php echo esc_html( $preset['name'] ); ?></h3>
					<p class="cro-preset-desc"><?php echo esc_html( $preset['description'] ); ?></p>
					<div class="cro-preset-meta">
						<span class="cro-preset-features">
							<?php
							$labels = array();
							foreach ( $features_list as $f ) {
								$labels[] = isset( $feature_labels[ $f ] ) ? $feature_labels[ $f ] : $f;
							}
							echo esc_html( implode( ', ', $labels ) );
							?>
						</span>
						<?php if ( $has_campaign ) : ?>
							<span class="cro-preset-badge"><?php esc_html_e( 'Creates campaign', 'cro-toolkit' ); ?></span>
						<?php endif; ?>
					</div>
					<div class="cro-preset-actions">
						<form method="post" action="" class="cro-preset-apply-form cro-inline-form">
							<?php wp_nonce_field( 'cro_apply_preset', 'cro_preset_nonce' ); ?>
							<input type="hidden" name="cro_apply_preset" value="1" />
							<input type="hidden" name="preset_id" value="<?php echo esc_attr( $preset['id'] ); ?>" />
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply preset', 'cro-toolkit' ); ?></button>
						</form>
						<button type="button" class="button cro-preset-preview-btn" data-preset-id="<?php echo esc_attr( $preset['id'] ); ?>">
							<?php esc_html_e( 'Preview preset', 'cro-toolkit' ); ?>
						</button>
					</div>
				</div>
				<div id="cro-preset-preview-<?php echo esc_attr( $preset['id'] ); ?>" class="cro-preset-preview-content cro-hidden" aria-hidden="true">
					<h4><?php echo esc_html( $preset['name'] ); ?></h4>
					<p><?php echo esc_html( $preset['description'] ); ?></p>
					<p><strong><?php esc_html_e( 'Enables:', 'cro-toolkit' ); ?></strong> <?php echo esc_html( implode( ', ', $labels ) ); ?></p>
					<?php if ( $has_campaign ) : ?>
						<p><strong><?php esc_html_e( 'Creates campaign:', 'cro-toolkit' ); ?></strong> <?php echo esc_html( $preset['campaign']['name'] ?? __( 'Yes', 'cro-toolkit' ) ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $presets ) ) : ?>
		<p><?php esc_html_e( 'No presets available.', 'cro-toolkit' ); ?></p>
	<?php endif; ?>
</div>

<!-- Preview modal -->
<div id="cro-preset-preview-modal" class="cro-preset-modal cro-hidden" role="dialog" aria-labelledby="cro-preset-preview-title" aria-modal="true">
	<div class="cro-preset-modal-backdrop"></div>
	<div class="cro-preset-modal-content">
		<button type="button" class="cro-preset-modal-close" aria-label="<?php esc_attr_e( 'Close', 'cro-toolkit' ); ?>"><?php echo CRO_Icons::svg( 'x', array( 'class' => 'cro-ico' ) ); ?></button>
		<h2 id="cro-preset-preview-title" class="cro-preset-modal-title"><?php esc_html_e( 'Preset preview', 'cro-toolkit' ); ?></h2>
		<div id="cro-preset-preview-body" class="cro-preset-modal-body"></div>
		<div class="cro-preset-modal-footer">
			<button type="button" class="button cro-preset-modal-close-btn"><?php esc_html_e( 'Close', 'cro-toolkit' ); ?></button>
		</div>
	</div>
</div>

<script>
(function() {
	document.querySelectorAll('.cro-preset-preview-btn').forEach(function(btn) {
		btn.addEventListener('click', function() {
			var id = this.getAttribute('data-preset-id');
			var content = document.getElementById('cro-preset-preview-' + id);
			var body = document.getElementById('cro-preset-preview-body');
			var modal = document.getElementById('cro-preset-preview-modal');
			if (content && body && modal) {
				body.innerHTML = content.innerHTML;
				modal.style.display = '';
			}
		});
	});
	function closeModal() {
		document.getElementById('cro-preset-preview-modal').style.display = 'none';
	}
	document.querySelector('.cro-preset-modal-backdrop').addEventListener('click', closeModal);
	document.querySelector('.cro-preset-modal-close').addEventListener('click', closeModal);
	document.querySelector('.cro-preset-modal-close-btn').addEventListener('click', closeModal);
})();
</script>
