<?php
/**
 * Admin campaigns list page
 *
 * @package CRO_Toolkit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Handle actions
if ( isset( $_POST['cro_action'] ) && wp_verify_nonce( $_POST['cro_nonce'], 'cro_campaign_action' ) ) {
	$action = sanitize_text_field( $_POST['cro_action'] );

	if ( 'delete' === $action && isset( $_POST['campaign_id'] ) ) {
		$campaign_id = absint( $_POST['campaign_id'] );
		CRO_Campaign::delete( $campaign_id );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Campaign deleted.', 'cro-toolkit' ) . '</p></div>';
	}
}

// Show error notices (sanitized)
$campaign_error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
if ( $campaign_error === 'duplicate_failed' ) {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to duplicate campaign.', 'cro-toolkit' ) . '</p></div>';
} elseif ( $campaign_error === 'invalid_nonce' ) {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid security check. Please try again.', 'cro-toolkit' ) . '</p></div>';
} elseif ( $campaign_error === 'unauthorized' ) {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'You do not have permission to perform that action.', 'cro-toolkit' ) . '</p></div>';
}

$campaigns = CRO_Campaign::get_all();
?>

	<?php if ( empty( $campaigns ) ) : ?>
		<div class="cro-table-empty-state">
			<span class="cro-table-empty-state__icon" aria-hidden="true"><?php echo CRO_Icons::svg( 'sparkles', array( 'class' => 'cro-ico' ) ); ?></span>
			<h3 class="cro-table-empty-state__title"><?php esc_html_e( 'No campaigns yet', 'cro-toolkit' ); ?></h3>
			<p class="cro-table-empty-state__text"><?php esc_html_e( 'Create your first campaign to show exit intent, scroll, or time-based offers to visitors.', 'cro-toolkit' ); ?></p>
			<p class="cro-mt-2">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cro-campaign-edit' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add New Campaign', 'cro-toolkit' ); ?></a>
			</p>
		</div>
	<?php else : ?>
		<div class="cro-table-wrap">
			<table class="cro-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'cro-toolkit' ); ?></th>
						<th><?php esc_html_e( 'Name', 'cro-toolkit' ); ?></th>
						<th><?php esc_html_e( 'Type', 'cro-toolkit' ); ?></th>
						<th><?php esc_html_e( 'Status', 'cro-toolkit' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'cro-toolkit' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $campaigns as $campaign ) : ?>
						<tr>
							<td><?php echo esc_html( (string) ( $campaign['id'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $campaign['name'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', (string) ( $campaign['campaign_type'] ?? $campaign['type'] ?? 'exit_intent' ) ) ) ); ?></td>
							<td><?php echo esc_html( (string) ( isset( $campaign['status'] ) ? ucfirst( $campaign['status'] ) : '' ) ); ?></td>
							<td class="cro-table-actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=cro-campaign-edit&campaign_id=' . $campaign['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'cro-toolkit' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cro-campaigns&action=duplicate&id=' . $campaign['id'] ), 'cro_duplicate_campaign' ) ); ?>"><?php esc_html_e( 'Duplicate', 'cro-toolkit' ); ?></a>
								<form method="post" class="cro-inline-form">
									<?php wp_nonce_field( 'cro_campaign_action', 'cro_nonce' ); ?>
									<input type="hidden" name="cro_action" value="delete">
									<input type="hidden" name="campaign_id" value="<?php echo esc_attr( $campaign['id'] ); ?>">
									<button type="submit" class="cro-table-action-link delete-link" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'cro-toolkit' ); ?>');"><?php esc_html_e( 'Delete', 'cro-toolkit' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
