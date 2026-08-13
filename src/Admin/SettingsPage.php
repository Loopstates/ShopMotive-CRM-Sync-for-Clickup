<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SettingsPage
 * 
 * Renders the Polaris-styled ClickSync admin settings interface.
 */
class SettingsPage {

	/**
	 * Render the full settings page HTML.
	 */
	public static function render() {
		// Handle settings form submit
		if ( isset( $_POST['clicksync_save_settings'] ) ) {
			check_admin_referer( 'clicksync_save_settings_action', 'clicksync_nonce' );

			$updated = array(
				'orders_enabled'          => ! empty( $_POST['orders_enabled'] ),
				'customers_enabled'       => ! empty( $_POST['customers_enabled'] ),
				'refunds_enabled'         => ! empty( $_POST['refunds_enabled'] ),
				'draft_checkouts_enabled' => ! empty( $_POST['draft_checkouts_enabled'] ),
			);
			Options::update_settings( $updated );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'ClickSync settings saved successfully.', 'clicksync-wordpress' ) . '</p></div>';
		}

		$settings   = Options::get_settings();
		$account    = Options::get_account();
		$host       = parse_url( site_url(), PHP_URL_HOST );
		$connect_url= CLICKSYNC_CLOUD_URL . '/auth/clickup?shop=' . urlencode( $host );

		$plan_name  = $account['plan_name'] ?? 'Free Plan';
		$sync_count = (int) ( $account['monthly_sync_count'] ?? 0 );
		$quota      = (int) ( $account['monthly_quota'] ?? 100 );
		$reset_date = $account['last_sync_reset'] ?? date( 'm/d/Y' );
		$logo_url   = CLICKSYNC_URL . 'assets/images/logo.png';
		?>
		<div class="wrap clicksync-wrap">
			<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 36px; height: 36px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);" />
				<h1 style="font-family: -apple-system, BlinkMacSystemFont, 'San Francisco', 'Segoe UI', Roboto, sans-serif; font-weight: 700; font-size: 22px; margin: 0; color: #1e293b;">
					<?php esc_html_e( 'ClickSync: Wordpress to ClickUp CRM Sync', 'clicksync-wordpress' ); ?>
				</h1>
			</div>

			<!-- CARD 1: Connection & Workspace Status -->
			<div class="clicksync-card">
				<div class="clicksync-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
						<svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
						<?php esc_html_e( 'ClickUp Integration Status', 'clicksync-wordpress' ); ?>
					</h3>
					<?php if ( ! empty( $settings['connected'] ) ) : ?>
						<span class="clicksync-badge badge-success"><?php esc_html_e( 'Connected', 'clicksync-wordpress' ); ?></span>
					<?php else : ?>
						<span class="clicksync-badge badge-warning"><?php esc_html_e( 'Not Connected', 'clicksync-wordpress' ); ?></span>
					<?php endif; ?>
				</div>
				<p className="clicksync-desc" style="font-size: 13px; color: #6d7175; margin-bottom: 16px;">
					<?php esc_html_e( 'Link your ClickUp Workspace to enable real-time synchronization of WooCommerce orders, customer updates, refunds, and abandoned checkouts.', 'clicksync-wordpress' ); ?>
				</p>
				<div>
					<a href="<?php echo esc_url( $connect_url ); ?>" target="_blank" class="clicksync-btn-primary" style="text-decoration: none; display: inline-block;">
						<?php esc_html_e( 'Connect ClickUp Workspace →', 'clicksync-wordpress' ); ?>
					</a>
				</div>
			</div>

			<!-- CARD 2: Plan & Monthly Usage Quota (3-Column Polaris Grid) -->
			<div class="clicksync-card" id="billing-section">
				<div class="clicksync-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #202223;">
						💳 <?php esc_html_e( 'Plan & Monthly Usage Quota', 'clicksync-wordpress' ); ?>
					</h3>
					<span class="clicksync-badge badge-info">
						<?php printf( esc_html__( 'Active: %s', 'clicksync-wordpress' ), esc_html( $plan_name ) ); ?>
					</span>
				</div>

				<p className="clicksync-desc" style="font-size: 13px; color: #6d7175; margin-bottom: 16px;">
					<strong><?php esc_html_e( 'Monthly Sync Quota:', 'clicksync-wordpress' ); ?></strong> 
					<?php echo esc_html( number_format( $sync_count ) ); ?> / <?php echo esc_html( number_format( $quota ) ); ?> 
					<?php esc_html_e( 'runs processed.', 'clicksync-wordpress' ); ?>
					(<?php esc_html_e( 'Resets every 30 days. Last reset:', 'clicksync-wordpress' ); ?> <?php echo esc_html( $reset_date ); ?>)
				</p>

				<!-- 3-Column Plan Grid -->
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px;">
					
					<!-- Free Plan Card -->
					<div style="background: <?php echo ( 'Free Plan' === $plan_name ) ? '#f4f6f8' : '#ffffff'; ?>; border: <?php echo ( 'Free Plan' === $plan_name ) ? '2px solid #008060' : '1px solid #e1e3e5'; ?>; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #202223;"><?php esc_html_e( 'Free Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Free Plan' === $plan_name ) : ?>
									<span class="clicksync-badge badge-success"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
								$0 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) ); ?>" target="_blank" class="clicksync-btn-secondary" style="width: 100%; text-align: center; text-decoration: none; box-sizing: border-box;">
							<?php echo ( 'Free Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Downgrade to Free', 'clicksync-wordpress' ); ?>
						</a>
					</div>

					<!-- Growth Plan Card -->
					<div style="background: <?php echo ( 'Growth Plan' === $plan_name ) ? '#f5f3ff' : '#ffffff'; ?>; border: <?php echo ( 'Growth Plan' === $plan_name ) ? '2px solid #4c1d95' : '1px solid #e1e3e5'; ?>; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #4c1d95;"><?php esc_html_e( 'Growth Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Growth Plan' === $plan_name ) : ?>
									<span class="clicksync-badge badge-brand"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
								$19.99 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) ); ?>" target="_blank" class="clicksync-btn-primary" style="width: 100%; text-align: center; background: #4c1d95; text-decoration: none; box-sizing: border-box;">
							<?php echo ( 'Growth Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Get Growth ($19.99/mo)', 'clicksync-wordpress' ); ?>
						</a>
					</div>

					<!-- Pro Plan Card -->
					<div style="background: <?php echo ( 'Pro Plan' === $plan_name ) ? '#fff0f7' : '#ffffff'; ?>; border: <?php echo ( 'Pro Plan' === $plan_name ) ? '2px solid #ff007f' : '1px solid #e1e3e5'; ?>; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #c026d3;"><?php esc_html_e( 'Pro Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Pro Plan' === $plan_name ) : ?>
									<span class="clicksync-badge badge-warning"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
								$49.99 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) ); ?>" target="_blank" class="clicksync-btn-primary" style="width: 100%; text-align: center; background: #ff007f; text-decoration: none; box-sizing: border-box;">
							<?php echo ( 'Pro Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Get Pro ($49.99/mo)', 'clicksync-wordpress' ); ?>
						</a>
					</div>

				</div>
			</div>

			<!-- CARD 3: Event Syncing Rules -->
			<form method="post" action="">
				<?php wp_nonce_field( 'clicksync_save_settings_action', 'clicksync_nonce' ); ?>
				
				<div class="clicksync-card">
					<div class="clicksync-header" style="margin-bottom: 16px;">
						<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #202223;">
							⚡ <?php esc_html_e( 'Event Syncing Rules', 'clicksync-wordpress' ); ?>
						</h3>
					</div>

					<div style="display: flex; flex-direction: column; gap: 16px;">
						<!-- Rule 1: Orders -->
						<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
							<div>
								<strong style="font-size: 14px; color: #1e293b;"><?php esc_html_e( 'WooCommerce Orders Created & Updated', 'clicksync-wordpress' ); ?></strong>
								<p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Automatically convert new orders into ClickUp tasks and update task status on fulfillment.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" name="orders_enabled" value="1" <?php checked( ! empty( $settings['orders_enabled'] ) ); ?>>
								<span class="clicksync-slider"></span>
							</label>
						</div>

						<!-- Rule 2: Customers -->
						<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
							<div>
								<strong style="font-size: 14px; color: #1e293b;"><?php esc_html_e( 'Customer Registration & Updates', 'clicksync-wordpress' ); ?></strong>
								<p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Create or update customer contact tasks in ClickUp when new users register.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" name="customers_enabled" value="1" <?php checked( ! empty( $settings['customers_enabled'] ) ); ?>>
								<span class="clicksync-slider"></span>
							</label>
						</div>

						<!-- Rule 3: Refunds -->
						<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
							<div>
								<strong style="font-size: 14px; color: #1e293b;"><?php esc_html_e( 'Order Refunds & Cancellations', 'clicksync-wordpress' ); ?></strong>
								<p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Sync refund details and updated order totals directly to task subtasks.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" name="refunds_enabled" value="1" <?php checked( ! empty( $settings['refunds_enabled'] ) ); ?>>
								<span class="clicksync-slider"></span>
							</label>
						</div>

						<!-- Rule 4: Draft / Abandoned Checkouts -->
						<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
							<div>
								<strong style="font-size: 14px; color: #1e293b;"><?php esc_html_e( 'Draft Orders & Abandoned Checkouts', 'clicksync-wordpress' ); ?></strong>
								<p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Sync incomplete checkouts and pending draft orders into ClickUp follow-up tasks.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" name="draft_checkouts_enabled" value="1" <?php checked( ! empty( $settings['draft_checkouts_enabled'] ) ); ?>>
								<span class="clicksync-slider"></span>
							</label>
						</div>
					</div>

					<div style="margin-top: 20px;">
						<input type="submit" name="clicksync_save_settings" class="clicksync-btn-primary" value="<?php esc_attr_e( 'Save Settings', 'clicksync-wordpress' ); ?>">
					</div>
				</div>
			</form>

		</div>
		<?php
	}
}
