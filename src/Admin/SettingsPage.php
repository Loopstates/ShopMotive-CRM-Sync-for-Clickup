<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SettingsPage
 * 
 * Renders the 1:1 identical deep-level ClickSync configuration command center matching the Shopify UI.
 */
class SettingsPage {

	/**
	 * Render the complete settings page HTML.
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
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'ClickSync rules and settings saved successfully.', 'clicksync-wordpress' ) . '</p></div>';
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
		<div class="wrap clicksync-wrap" style="max-width: 1100px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'San Francisco', 'Segoe UI', Roboto, sans-serif;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'ClickSync Settings', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Configure automated ClickUp CRM sync rules, field mappings, status actions, and priority routing.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
				<div>
					<span class="clicksync-badge badge-brand" style="font-size: 13px; padding: 6px 14px; border-radius: 20px;">v1.0.0 Pro Hub</span>
				</div>
			</div>

			<!-- CARD 1: Connection & Workspace Status -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<div className="clicksync-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
						<svg style="width: 18px; height: 18px; fill: #7c3aed;" viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
						<?php esc_html_e( 'ClickSync Status', 'clicksync-wordpress' ); ?>
					</h3>
					<?php if ( ! empty( $settings['connected'] ) ) : ?>
						<span class="clicksync-badge badge-success" style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php esc_html_e( 'Active Connection', 'clicksync-wordpress' ); ?></span>
					<?php else : ?>
						<span class="clicksync-badge badge-warning" style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php esc_html_e( 'Not Connected', 'clicksync-wordpress' ); ?></span>
					<?php endif; ?>
				</div>
				<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">
					<?php esc_html_e( 'Successfully linked to ClickUp CRM. Background WooCommerce events are intercepted and queued instantly with zero impact to storefront performance.', 'clicksync-wordpress' ); ?>
				</p>
				<div>
					<a href="<?php echo esc_url( $connect_url ); ?>" target="_blank" class="button button-primary" style="background: #7c3aed; border-color: #6d28d9; color: white; padding: 6px 16px; height: 36px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; font-size: 13px;">
						<?php esc_html_e( 'Connect / Re-link ClickUp Workspace →', 'clicksync-wordpress' ); ?>
					</a>
				</div>
			</div>

			<!-- CARD 2: Plan & Monthly Usage Quota (3-Column Polaris Grid) -->
			<div class="clicksync-card" id="billing-section" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<h3 style="margin:0; font-size: 16px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
						💳 <?php esc_html_e( 'Plan & Monthly Usage Quota', 'clicksync-wordpress' ); ?>
					</h3>
					<span class="clicksync-badge badge-info" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
						<?php printf( esc_html__( 'Active: %s', 'clicksync-wordpress' ), esc_html( $plan_name ) ); ?>
					</span>
				</div>

				<p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
					<strong><?php esc_html_e( 'Monthly Sync Quota:', 'clicksync-wordpress' ); ?></strong> 
					<?php echo esc_html( number_format( $sync_count ) ); ?> / <?php echo esc_html( number_format( $quota ) ); ?> 
					<?php esc_html_e( 'runs processed.', 'clicksync-wordpress' ); ?>
					(<?php esc_html_e( 'Resets every 30 days. Last reset:', 'clicksync-wordpress' ); ?> <?php echo esc_html( $reset_date ); ?>)
				</p>

				<!-- 3-Column Plan Grid -->
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 16px;">
					
					<!-- Free Plan Card -->
					<div style="background: <?php echo ( 'Free Plan' === $plan_name ) ? '#f8fafc' : '#ffffff'; ?>; border: <?php echo ( 'Free Plan' === $plan_name ) ? '2px solid #10b981' : '1px solid #e2e8f0'; ?>; border-radius: 8px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;"><?php esc_html_e( 'Free Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Free Plan' === $plan_name ) : ?>
									<span class="clicksync-badge" style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
								$0 <span style="font-size: 12px; font-weight: 400; color: #64748b;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) ); ?>" target="_blank" class="button" style="width: 100%; text-align: center; height: 36px; line-height: 34px; box-sizing: border-box;">
							<?php echo ( 'Free Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Downgrade to Free', 'clicksync-wordpress' ); ?>
						</a>
					</div>

					<!-- Growth Plan Card -->
					<div style="background: <?php echo ( 'Growth Plan' === $plan_name ) ? '#f5f3ff' : '#ffffff'; ?>; border: <?php echo ( 'Growth Plan' === $plan_name ) ? '2px solid #7c3aed' : '1px solid #e2e8f0'; ?>; border-radius: 8px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #6d28d9;"><?php esc_html_e( 'Growth Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Growth Plan' === $plan_name ) : ?>
									<span class="clicksync-badge" style="background: #ede9fe; color: #6d28d9; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
								$19.99 <span style="font-size: 12px; font-weight: 400; color: #64748b;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Growth%20Plan' ); ?>" target="_blank" class="button button-primary" style="width: 100%; text-align: center; background: #7c3aed; border-color: #6d28d9; height: 36px; line-height: 34px; box-sizing: border-box;">
							<?php echo ( 'Growth Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Get Growth ($19.99/mo)', 'clicksync-wordpress' ); ?>
						</a>
					</div>

					<!-- Pro Plan Card -->
					<div style="background: <?php echo ( 'Pro Plan' === $plan_name ) ? '#fff0f7' : '#ffffff'; ?>; border: <?php echo ( 'Pro Plan' === $plan_name ) ? '2px solid #db2777' : '1px solid #e2e8f0'; ?>; border-radius: 8px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #be185d;"><?php esc_html_e( 'Pro Plan', 'clicksync-wordpress' ); ?></h4>
								<?php if ( 'Pro Plan' === $plan_name ) : ?>
									<span class="clicksync-badge" style="background: #fce7f3; color: #be185d; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
								<?php endif; ?>
							</div>
							<div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
								$49.99 <span style="font-size: 12px; font-weight: 400; color: #64748b;"><?php esc_html_e( '/month', 'clicksync-wordpress' ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Pro%20Plan' ); ?>" target="_blank" class="button button-primary" style="width: 100%; text-align: center; background: #db2777; border-color: #be185d; height: 36px; line-height: 34px; box-sizing: border-box;">
							<?php echo ( 'Pro Plan' === $plan_name ) ? esc_html__( 'Current Plan', 'clicksync-wordpress' ) : esc_html__( 'Get Pro ($49.99/mo)', 'clicksync-wordpress' ); ?>
						</a>
					</div>

				</div>
			</div>

			<!-- SECTION HEADER: Event Syncing Rules & Configuration Engine -->
			<div style="margin-top: 32px; margin-bottom: 12px;">
				<h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
					⚡ <?php esc_html_e( 'Event Syncing Rules & Configuration Engine', 'clicksync-wordpress' ); ?>
				</h2>
				<p style="font-size: 13px; color: #64748b; margin: 0;">
					<?php esc_html_e( 'Customize split routing behaviors, select destination lists, map custom properties, or bind ClickUp status changes to WooCommerce actions.', 'clicksync-wordpress' ); ?>
				</p>
			</div>

			<!-- CARD 3A: WooCommerce Orders Created Card -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="margin:0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
						🛒 <?php esc_html_e( 'WooCommerce Orders Created', 'clicksync-wordpress' ); ?>
					</h3>
					<label class="clicksync-switch">
						<input type="checkbox" id="rule-orders-toggle" checked>
						<span class="clicksync-slider"></span>
					</label>
				</div>

				<!-- Target List Selector -->
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
					<span style="font-size: 13px; font-weight: 600; color: #334155;">📋 <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?></span>
					<select id="clicksync-list-orders" class="clicksync-select" style="margin: 0; min-width: 260px; height: 36px;">
						<option value=""><?php esc_html_e( 'Select Target List > WooCommerce - Orders', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>

				<!-- Sub-toggles: Refund Sync, Fulfillment Sync, Split Order Routing -->
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
					<div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
						🔄 <?php esc_html_e( 'Order Lifecycle Syncing Options', 'clicksync-wordpress' ); ?>
					</div>
					
					<div style="display: flex; flex-direction: column; gap: 12px;">
						<div style="display: flex; align-items: center; justify-content: space-between;">
							<div>
								<strong style="font-size: 13px; color: #1e293b;"><?php esc_html_e( 'Sync Refund Updates', 'clicksync-wordpress' ); ?></strong>
								<span style="background: #e2e8f0; color: #475569; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">One-Way</span>
								<p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Post refund comments and updated amounts directly to ClickUp order tasks when refunded in WooCommerce.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" checked>
								<span class="clicksync-slider"></span>
							</label>
						</div>

						<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 10px;">
							<div>
								<strong style="font-size: 13px; color: #1e293b;"><?php esc_html_e( 'Sync Fulfillment Statuses', 'clicksync-wordpress' ); ?></strong>
								<span style="background: #dcfce7; color: #15803d; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">2-Way Sync</span>
								<p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Update ClickUp task status upon fulfillment, and fulfill orders in WooCommerce when task status is updated.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox" checked>
								<span class="clicksync-slider"></span>
							</label>
						</div>

						<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 10px;">
							<div>
								<strong style="font-size: 13px; color: #1e293b;"><?php esc_html_e( 'Split Order Routing', 'clicksync-wordpress' ); ?></strong>
								<span style="background: #fce7f3; color: #be185d; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">Pro Feature</span>
								<p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Create separate ClickUp tasks for individual line items inside a multi-product WooCommerce order.', 'clicksync-wordpress' ); ?></p>
							</div>
							<label class="clicksync-switch">
								<input type="checkbox">
								<span class="clicksync-slider"></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Option Pills Toolbar -->
				<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; background: #f1f5f9; padding: 8px 12px; border-radius: 6px; align-items: center;">
					<span style="font-size: 12px; font-weight: 600; color: #475569; margin-right: 4px;"><?php esc_html_e( 'Configure Options:', 'clicksync-wordpress' ); ?></span>
					<button type="button" class="clicksync-pill active" data-target="orders-fields"><?php esc_html_e( 'Custom Fields Mapping', 'clicksync-wordpress' ); ?></button>
					<button type="button" class="clicksync-pill active" data-target="orders-status"><?php esc_html_e( 'Status Mapping', 'clicksync-wordpress' ); ?></button>
					<button type="button" class="clicksync-pill active" data-target="orders-assignee"><?php esc_html_e( 'Assignee Routing', 'clicksync-wordpress' ); ?></button>
					<button type="button" class="clicksync-pill active" data-target="orders-priority"><?php esc_html_e( 'Priority Routing', 'clicksync-wordpress' ); ?></button>
				</div>

				<!-- Sub-section: Custom Fields Mapping -->
				<div id="orders-fields" class="clicksync-section-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
					<div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 10px;">
						🧪 <?php esc_html_e( 'Custom Field Mapping Engine', 'clicksync-wordpress' ); ?>
					</div>
					<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
						<thead>
							<tr style="text-align: left; font-size: 12px; color: #64748b;">
								<th style="padding: 8px;"><?php esc_html_e( 'WooCommerce Field', 'clicksync-wordpress' ); ?></th>
								<th style="padding: 8px;"><?php esc_html_e( 'Target ClickUp Field', 'clicksync-wordpress' ); ?></th>
								<th style="padding: 8px; text-align: right;"><?php esc_html_e( 'Action', 'clicksync-wordpress' ); ?></th>
							</tr>
						</thead>
						<tbody id="orders-field-mappings-tbody">
							<tr>
								<td style="padding: 6px 8px;">
									<select class="clicksync-select" style="margin: 0;">
										<option>Order Number (order_number)</option>
										<option>Total Price (total_price)</option>
										<option>Customer Email (customer.email)</option>
										<option>Customer Phone (customer.phone)</option>
									</select>
								</td>
								<td style="padding: 6px 8px;">
									<select class="clicksync-select clicksync-field-target" style="margin: 0;">
										<option><?php esc_html_e( 'Select ClickUp Field', 'clicksync-wordpress' ); ?></option>
									</select>
								</td>
								<td style="padding: 6px 8px; text-align: right;">
									<button type="button" class="button button-small" style="color: #dc2626;"><?php esc_html_e( 'Remove', 'clicksync-wordpress' ); ?></button>
								</td>
							</tr>
						</tbody>
					</table>
					<button type="button" class="button button-secondary" id="add-orders-field-mapping">+ <?php esc_html_e( 'Add Field Mapping', 'clicksync-wordpress' ); ?></button>
				</div>

				<!-- Sub-section: Status Mapping Rules -->
				<div id="orders-status" class="clicksync-section-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
					<div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 10px;">
						🔄 <?php esc_html_e( 'Status Mapping Rules', 'clicksync-wordpress' ); ?>
					</div>
					<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
						<thead>
							<tr style="text-align: left; font-size: 12px; color: #64748b;">
								<th style="padding: 8px;"><?php esc_html_e( 'ClickUp Status', 'clicksync-wordpress' ); ?></th>
								<th style="padding: 8px;"><?php esc_html_e( 'WooCommerce Order Action', 'clicksync-wordpress' ); ?></th>
								<th style="padding: 8px; text-align: right;"><?php esc_html_e( 'Action', 'clicksync-wordpress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 6px 8px;"><code>Shipped / Completed</code></td>
								<td style="padding: 6px 8px;"><span class="clicksync-badge badge-success" style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;"><?php esc_html_e( 'Mark Order Completed', 'clicksync-wordpress' ); ?></span></td>
								<td style="padding: 6px 8px; text-align: right;"><button type="button" class="button button-small" style="color: #dc2626;"><?php esc_html_e( 'Delete', 'clicksync-wordpress' ); ?></button></td>
							</tr>
						</tbody>
					</table>
					<button type="button" class="button button-secondary">+ <?php esc_html_e( 'Add Status Rule', 'clicksync-wordpress' ); ?></button>
				</div>

				<!-- Sub-section: Assignee Routing Rules -->
				<div id="orders-assignee" class="clicksync-section-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
					<div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 10px;">
						👤 <?php esc_html_e( 'Assignee Routing Rules', 'clicksync-wordpress' ); ?>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 8px; align-items: end;">
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Field', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option>total_price</option><option>billing_address.country</option></select>
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Operator', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option>>=</option><option>equals</option></select>
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Value', 'clicksync-wordpress' ); ?></label>
							<input type="text" class="clicksync-select" style="margin:0;" placeholder="500" />
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Assign To', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option><?php esc_html_e( 'Workspace Member', 'clicksync-wordpress' ); ?></option></select>
						</div>
						<button type="button" class="button button-secondary" style="height: 36px;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
					</div>
				</div>

				<!-- Sub-section: Priority Routing Rules -->
				<div id="orders-priority" class="clicksync-section-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px;">
					<div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 10px;">
						🚩 <?php esc_html_e( 'Priority Routing Rules', 'clicksync-wordpress' ); ?>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 8px; align-items: end;">
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Field', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option>total_price</option></select>
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Operator', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option>>=</option></select>
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Value', 'clicksync-wordpress' ); ?></label>
							<input type="text" class="clicksync-select" style="margin:0;" placeholder="1000" />
						</div>
						<div>
							<label style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Set Priority', 'clicksync-wordpress' ); ?></label>
							<select class="clicksync-select" style="margin:0;"><option>Urgent 🔴</option><option>High 🟡</option></select>
						</div>
						<button type="button" class="button button-secondary" style="height: 36px;"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
					</div>
				</div>

			</div>

			<!-- CARD 3B: WooCommerce Draft Orders Created Card -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="margin:0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
						📝 <?php esc_html_e( 'WooCommerce Draft Orders Created', 'clicksync-wordpress' ); ?>
					</h3>
					<label class="clicksync-switch">
						<input type="checkbox" id="rule-drafts-toggle" checked>
						<span class="clicksync-slider"></span>
					</label>
				</div>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
					<span style="font-size: 13px; font-weight: 600; color: #334155;">📋 <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?></span>
					<select id="clicksync-list-drafts" class="clicksync-select" style="margin: 0; min-width: 260px; height: 36px;">
						<option value=""><?php esc_html_e( 'Select Target List > WooCommerce - Draft Orders', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>
			</div>

			<!-- CARD 3C: WooCommerce Customers Created Card -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="margin:0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
						👤 <?php esc_html_e( 'WooCommerce Customers Created', 'clicksync-wordpress' ); ?>
					</h3>
					<label class="clicksync-switch">
						<input type="checkbox" id="rule-customers-toggle" checked>
						<span class="clicksync-slider"></span>
					</label>
				</div>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
					<span style="font-size: 13px; font-weight: 600; color: #334155;">📋 <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?></span>
					<select id="clicksync-list-customers" class="clicksync-select" style="margin: 0; min-width: 260px; height: 36px;">
						<option value=""><?php esc_html_e( 'Select Target List > WooCommerce - Customers', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>
			</div>

			<!-- CARD 3D: WooCommerce Abandoned Checkouts Card -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="margin:0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
						🛒 <?php esc_html_e( 'WooCommerce Abandoned Checkouts', 'clicksync-wordpress' ); ?>
					</h3>
					<label class="clicksync-switch">
						<input type="checkbox" id="rule-checkouts-toggle" checked>
						<span class="clicksync-slider"></span>
					</label>
				</div>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
					<span style="font-size: 13px; font-weight: 600; color: #334155;">📋 <?php esc_html_e( 'Target ClickUp List:', 'clicksync-wordpress' ); ?></span>
					<select id="clicksync-list-checkouts" class="clicksync-select" style="margin: 0; min-width: 260px; height: 36px;">
						<option value=""><?php esc_html_e( 'Select Target List > WooCommerce - Abandoned Checkouts', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>
			</div>

			<!-- CARD 4: Live Sync Execution Audit Logs -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
				<div style="margin-bottom: 12px;">
					<h3 style="margin:0; font-size: 16px; font-weight: 700; color: #0f172a;">
						📊 <?php esc_html_e( 'Live Sync Execution Audit Logs', 'clicksync-wordpress' ); ?>
					</h3>
					<p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;">
						<?php esc_html_e( 'Recent automated task creations and status updates dispatched from WooCommerce to ClickUp.', 'clicksync-wordpress' ); ?>
					</p>
				</div>

				<table style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr style="background: #f8fafc; text-align: left; font-size: 12px; color: #64748b;">
							<th style="padding: 10px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Event & Entity', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 10px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Status', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 10px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'ClickUp Task', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 10px; border-bottom: 2px solid #e2e8f0; text-align: right;"><?php esc_html_e( 'Timestamp', 'clicksync-wordpress' ); ?></th>
						</tr>
					</thead>
					<tbody id="clicksync-logs-body">
						<tr>
							<td colSpan="4" style="padding: 24px; text-align: center; color: #64748b;">
								<?php esc_html_e( 'No recent sync logs found. Dispatched events will render here in real-time.', 'clicksync-wordpress' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
		<?php
	}
}
