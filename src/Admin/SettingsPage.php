<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SettingsPage
 * 
 * Renders the 100% pixel-perfect deep level configuration command center matching the Shopify UI.
 */
class SettingsPage {

	/**
	 * Render the full settings page HTML.
	 */
	public static function render() {
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
		$is_ssl      = is_ssl() ? 'https' : 'http';
		$connect_url= CLICKSYNC_CLOUD_URL . '/auth/clickup?shop=' . urlencode( $host ) . '&protocol=' . $is_ssl;

		$plan_name  = $account['plan_name'] ?? 'Free Plan';
		$sync_count = (int) ( $account['monthly_sync_count'] ?? 0 );
		$quota      = (int) ( $account['monthly_quota'] ?? 100 );
		$reset_date = $account['last_sync_reset'] ?? date( 'm/d/Y' );
		?>
		<div class="wrap clicksync-wrap" style="max-width: 1050px; margin: 20px auto;">
			
			<!-- Header Status Banner -->
			<div id="clicksync-connection-status-block" data-connect-url="<?php echo esc_url( $connect_url ); ?>">
				<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
						<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg style="width: 18px; height: 18px; fill: #6A2B8F;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
							<?php esc_html_e( 'ClickSync Status', 'clicksync-wordpress' ); ?>
						</h3>
						<span class="clicksync-badge" style="background: #f4f6f8; color: #6d7175; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php esc_html_e( 'Checking Status...', 'clicksync-wordpress' ); ?></span>
					</div>
					<p style="font-size: 13px; color: #6d7175; margin-bottom: 16px; line-height: 1.5;">
						<?php esc_html_e( 'Connecting to ClickSync Cloud Service telemetry...', 'clicksync-wordpress' ); ?>
					</p>
				</div>
			</div>

			<!-- Onboarding Wizard Container -->
			<div id="clicksync-onboarding-container" style="display: none;"></div>

			<!-- main container (disabled until ClickUp workspace connection is established) -->
			<div id="clicksync-settings-main-container" class="clicksync-settings-disabled">

				<!-- Pricing & Usage Quota Block -->
				<div id="billing-section" class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
						<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.1-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg> <?php esc_html_e( 'Plan & Monthly Usage Quota', 'clicksync-wordpress' ); ?>
						</h3>
						<span class="clicksync-badge badge-info" style="background: #e2f1f8; color: #005a87; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
							<?php printf( esc_html__( 'Active: %s', 'clicksync-wordpress' ), esc_html( $plan_name ) ); ?>
						</span>
					</div>

					<p style="font-size: 13px; color: #6d7175; margin-bottom: 16px;">
						<strong><?php esc_html_e( 'Monthly Sync Quota:', 'clicksync-wordpress' ); ?></strong> 
						<span id="clicksync-usage-count"><?php echo esc_html( number_format( $sync_count ) ); ?></span> / <span id="clicksync-usage-quota"><?php echo esc_html( number_format( $quota ) ); ?></span> 
						<?php esc_html_e( 'runs processed. (Resets every 30 days. Last reset:', 'clicksync-wordpress' ); ?> <?php echo esc_html( $reset_date ); ?>)
					</p>

					<!-- 3-Column Plan Grid -->
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px;">
						<!-- Free Plan -->
						<div id="plan-card-free" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
							<div>
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
									<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #202223;"><?php esc_html_e( 'Free Plan', 'clicksync-wordpress' ); ?></h4>
									<span class="clicksync-badge plan-active-badge" style="display: none; background: #e3f1df; color: #008060; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 10px;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								</div>
								<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
									$0 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
								</div>
							</div>
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) ); ?>" target="_blank" class="clicksync-btn-secondary" style="width: 100%; text-align: center; text-decoration: none; box-sizing: border-box; height: 36px; line-height: 34px;">
								<?php esc_html_e( 'Get Free', 'clicksync-wordpress' ); ?>
							</a>
						</div>

						<!-- Growth Plan -->
						<div id="plan-card-growth" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
							<div>
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
									<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #4c1d95;"><?php esc_html_e( 'Growth Plan', 'clicksync-wordpress' ); ?></h4>
									<span class="clicksync-badge plan-active-badge" style="display: none; background: #efe6fc; color: #6d28d9; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 10px;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								</div>
								<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
									$19.99 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
								</div>
							</div>
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Growth%20Plan' ); ?>" target="_blank" class="clicksync-btn-primary" style="width: 100%; text-align: center; background: #4c1d95; text-decoration: none; box-sizing: border-box; height: 36px; line-height: 34px;">
								<?php esc_html_e( 'Get Growth ($19.99/mo)', 'clicksync-wordpress' ); ?>
							</a>
						</div>

						<!-- Pro Plan -->
						<div id="plan-card-pro" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
							<div>
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
									<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #c026d3;"><?php esc_html_e( 'Pro Plan', 'clicksync-wordpress' ); ?></h4>
									<span class="clicksync-badge plan-active-badge" style="display: none; background: #fff4e5; color: #b97a00; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 10px;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								</div>
								<div style="font-size: 20px; font-weight: 700; color: #202223; margin-bottom: 16px;">
									$49.99 <span style="font-size: 12px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
								</div>
							</div>
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Pro%20Plan' ); ?>" target="_blank" class="clicksync-btn-primary" style="width: 100%; text-align: center; background: #ff007f; text-decoration: none; box-sizing: border-box; height: 36px; line-height: 34px;">
								<?php esc_html_e( 'Get Pro ($49.99/mo)', 'clicksync-wordpress' ); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- SECTION TITLE: Event Syncing Rules -->
				<h2 style="font-size: 18px; font-weight: 600; color: #202223; margin: 32px 0 8px 0; display: flex; align-items: center; gap: 8px;">
					<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> <?php esc_html_e( 'Event Syncing Rules', 'clicksync-wordpress' ); ?>
				</h2>
				<p style="font-size: 14px; color: #6d7175; margin-bottom: 20px;">
					<?php esc_html_e( 'Customize split routing behaviors, select templates, map custom properties, or bind ClickUp status changes to WooCommerce actions. Possibilities are endless. Literally!', 'clicksync-wordpress' ); ?>
				</p>

				<!-- ========================================== -->
				<!-- CARD 1: WooCommerce Order Created Card -->
				<!-- ========================================== -->
				<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					
					<!-- Header with Toggle Switch -->
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
						<span style="font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg> <?php esc_html_e( 'WooCommerce Order Created', 'clicksync-wordpress' ); ?>
						</span>
						<label class="clicksync-switch">
							<input type="checkbox" id="orders-toggle" checked>
							<span class="clicksync-slider"></span>
						</label>
					</div>

					<!-- Target ClickUp List Selector Bar -->
					<div style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border-radius: 4px; border: 1px solid #edeeef; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
						<span style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" style="width: 15px; height: 15px;" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?>
						</span>
						<select id="clicksync-list-orders" class="clicksync-select" style="margin: 0; min-width: 240px; height: 36px;">
							<option value=""><?php esc_html_e( 'Loading lists...', 'clicksync-wordpress' ); ?></option>
						</select>
					</div>

					<!-- Order Lifecycle Syncing Options Card -->
					<div style="margin-bottom: 16px; padding: 16px; background: #f4f6f8; border-radius: 6px; border: 1px solid #e1e3e5;">
						<div style="font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #202223; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> <?php esc_html_e( 'Order Lifecycle Syncing Options', 'clicksync-wordpress' ); ?>
						</div>
						
						<div style="display: flex; flex-direction: column; gap: 12px;">
							<!-- Refund Syncing -->
							<div style="display: flex; align-items: center; justify-content: space-between;">
								<div>
									<div style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
										<svg class="clicksync-title-icon" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg> <?php esc_html_e( 'Sync Refund Updates', 'clicksync-wordpress' ); ?>
										<span style="background: #e1e3e5; color: #202223; font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 4px;">ONE-WAY</span>
									</div>
									<div style="font-size: 12px; color: #6d7175; margin-top: 2px;">
										<?php esc_html_e( 'Post detailed refund comments and line items to ClickUp order tasks when orders are refunded in WooCommerce.', 'clicksync-wordpress' ); ?>
									</div>
								</div>
								<label class="clicksync-switch">
									<input type="checkbox" checked>
									<span class="clicksync-slider"></span>
								</label>
							</div>

							<!-- Fulfillment Status Syncing -->
							<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e1e3e5; padding-top: 12px;">
								<div>
									<div style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
										<svg class="clicksync-title-icon" style="width: 15px; height: 15px;" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zm-14 9c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-3c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm11 3c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-3c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm2-2H8V6h9v6z"/></svg> <?php esc_html_e( 'Sync Fulfillment Statuses', 'clicksync-wordpress' ); ?>
										<span style="background: #d4eed8; color: #108043; font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 4px;">2-WAY SYNC</span>
									</div>
									<div style="font-size: 12px; color: #6d7175; margin-top: 2px;">
										<?php esc_html_e( 'Automatically update ClickUp task statuses upon fulfillment, and fulfill orders in WooCommerce when a ClickUp task status is mapped.', 'clicksync-wordpress' ); ?>
									</div>
								</div>
								<label class="clicksync-switch">
									<input type="checkbox" checked>
									<span class="clicksync-slider"></span>
								</label>
							</div>

							<!-- Split Order Routing -->
							<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e1e3e5; padding-top: 12px;">
								<div>
									<div style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
										<svg class="clicksync-title-icon" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.1-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg> <?php esc_html_e( 'Split Order Routing', 'clicksync-wordpress' ); ?>
										<span style="background: #e1e3e5; color: #202223; font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 4px;">ONE-WAY</span>
									</div>
									<div style="font-size: 12px; color: #6d7175; margin-top: 2px;">
										<?php esc_html_e( 'Create separate ClickUp tasks for individual line items inside a single order rather than creating one unified task.', 'clicksync-wordpress' ); ?>
									</div>
								</div>
								<label class="clicksync-switch">
									<input type="checkbox">
									<span class="clicksync-slider"></span>
								</label>
							</div>
						</div>
					</div>

					<!-- Option Pills Toolbar -->
					<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; background: #f4f6f8; padding: 8px 12px; border-radius: 6px; border: 1px solid #e1e3e5; align-items: center;">
						<span style="font-size: 12px; font-weight: 600; color: #6d7175; margin-right: 4px;"><?php esc_html_e( 'Configure Options:', 'clicksync-wordpress' ); ?></span>
						<button type="button" class="clicksync-option-pill active" data-target="orders-assignee-block"><?php esc_html_e( 'Assignee Routing', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill active" data-target="orders-priority-block"><?php esc_html_e( 'Priority Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill active" data-target="orders-tagging-block"><?php esc_html_e( 'Tagging Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill active" data-target="orders-customfields-block"><?php esc_html_e( 'Custom Fields', 'clicksync-wordpress' ); ?></button>
					</div>

					<!-- 1. Assignee Routing Rules Sub-section -->
					<div id="orders-assignee-block" style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg> <?php esc_html_e( 'Assignee Routing Rules', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No assignee routing rules configured yet. Order tasks will remain unassigned by default.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="id">WooCommerce Order ID</option>
									<option value="total_price">Total Price</option>
									<option value="customer.email">Customer Email</option>
									<option value="billing_address.country">Billing Country</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="contains">contains</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. VIP-Buyer" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Assign To:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select clicksync-assignees-dropdown" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading members...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 2. Task Priority Rules Sub-section -->
					<div id="orders-priority-block" style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> <?php esc_html_e( 'Task Priority Rules', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No priority routing rules configured yet. Order tasks will default to no priority.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="total_price">Total Price</option>
									<option value="id">WooCommerce Order ID</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="equals">is equal to</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. 500" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Set Priority:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="1">Urgent</option>
									<option value="2">High</option>
									<option value="3">Normal</option>
									<option value="4">Low</option>
								</select>
							</div>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 3. Task Tagging Rules Sub-section -->
					<div id="orders-tagging-block" style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg> <?php esc_html_e( 'Task Tagging Rules', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No tagging rules configured yet. Add rules below to automatically assign tags in ClickUp.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="id">WooCommerce Order ID</option>
									<option value="total_price">Total Price</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Apply Tag:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express-Order" />
							</div>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 4. Custom Field Mappings Sub-section -->
					<div id="orders-customfields-block" style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Custom Field Mappings', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No custom field mappings defined yet for this rule.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center;">
							<select class="clicksync-select" style="margin: 0; height: 36px;">
								<option value="id">WooCommerce Order ID</option>
								<option value="total_price">Total Price (total_price)</option>
								<option value="customer.email">Customer Email (customer.email)</option>
							</select>
							<select class="clicksync-select clicksync-field-target" style="margin: 0; height: 36px;">
								<option value="">Inv_Email (email)</option>
							</select>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Mapping', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 5. ClickUp Status to WooCommerce Actions Sub-section -->
					<div style="padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
							<div style="font-size: 14px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
								<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> <?php esc_html_e( 'ClickUp Status to WooCommerce Actions', 'clicksync-wordpress' ); ?>
							</div>
							<label style="font-size: 12px; color: #6d7175; display: flex; align-items: center; gap: 4px;">
								<input type="checkbox" /> <?php esc_html_e( 'Show Advanced WooCommerce Actions', 'clicksync-wordpress' ); ?>
							</label>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No status action mappings configured.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center;">
							<select class="clicksync-select clicksync-statuses-dropdown" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading statuses...', 'clicksync-wordpress' ); ?></option>
							</select>
							<select class="clicksync-select" style="margin: 0; height: 36px;">
								<option value="fulfill">Fulfill WooCommerce Order (Complete Order)</option>
								<option value="cancel">Cancel WooCommerce Order</option>
							</select>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Action', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

				</div>


				<!-- ========================================== -->
				<!-- CARD 3: WooCommerce Customer Created -->
				<!-- ========================================== -->
				<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
						<span style="font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg> <?php esc_html_e( 'WooCommerce Customer Created', 'clicksync-wordpress' ); ?>
						</span>
						<label class="clicksync-switch">
							<input type="checkbox" checked>
							<span class="clicksync-slider"></span>
						</label>
					</div>

					<div style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border-radius: 4px; border: 1px solid #edeeef; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
						<span style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" style="width: 15px; height: 15px;" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?>
						</span>
						<select id="clicksync-list-customers" class="clicksync-select" style="margin: 0; min-width: 240px; height: 36px;">
							<option value=""><?php esc_html_e( 'Loading lists...', 'clicksync-wordpress' ); ?></option>
						</select>
					</div>

					<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; background: #f4f6f8; padding: 8px 12px; border-radius: 6px; border: 1px solid #e1e3e5; align-items: center;">
						<span style="font-size: 12px; font-weight: 600; color: #6d7175; margin-right: 4px;"><?php esc_html_e( 'Configure Options:', 'clicksync-wordpress' ); ?></span>
						<button type="button" class="clicksync-option-pill active"><?php esc_html_e( 'Tagging Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill active"><?php esc_html_e( 'Custom Fields', 'clicksync-wordpress' ); ?></button>
					</div>

					<!-- Task Tagging Rules -->
					<div style="margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg> <?php esc_html_e( 'Task Tagging Rules', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No tagging rules configured yet. Add rules below to automatically assign tags in ClickUp.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;"><option>Customer ID</option></select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select" style="margin: 0; height: 36px;"><option>is equal to</option></select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Apply Tag:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express-Order" />
							</div>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- Custom Field Mappings -->
					<div style="padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Custom Field Mappings', 'clicksync-wordpress' ); ?>
						</div>
						<p style="font-size: 12px; color: #6d7175; margin-bottom: 12px; font-style: italic;">
							<?php esc_html_e( 'No custom field mappings defined yet for this rule.', 'clicksync-wordpress' ); ?>
						</p>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center;">
							<select class="clicksync-select" style="margin: 0; height: 36px;"><option>Customer ID</option></select>
							<select class="clicksync-select clicksync-field-target" style="margin: 0; height: 36px;"><option>Customer Email (email)</option></select>
							<button type="button" class="clicksync-btn-secondary" style="height: 36px; white-space: nowrap;"><?php esc_html_e( 'Add Mapping', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>
				</div>



			</div> <!-- end main container -->

		</div>
		<?php
	}
}
