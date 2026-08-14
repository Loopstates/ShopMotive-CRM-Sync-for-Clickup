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

				<!-- Pricing & Usage Quota Block (Slick, Slim Bar) -->
				<div id="billing-section" class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
						<div style="display: flex; flex-direction: column; gap: 4px;">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span style="font-size: 14px; font-weight: 600; color: #202223;"><?php esc_html_e( 'Subscription Plan:', 'clicksync-wordpress' ); ?></span>
								<span class="clicksync-badge badge-info" style="background: #e2f1f8; color: #005a87; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">
									<?php echo esc_html( $plan_name ); ?>
								</span>
							</div>
							<div style="font-size: 12px; color: #6d7175;">
								<strong><?php esc_html_e( 'Usage Quota:', 'clicksync-wordpress' ); ?></strong> 
								<span id="clicksync-usage-count"><?php echo esc_html( number_format( $sync_count ) ); ?></span> / <span id="clicksync-usage-quota"><?php echo esc_html( number_format( $quota ) ); ?></span> runs. (Reset: <?php echo esc_html( $reset_date ); ?>)
							</div>
						</div>
						<div style="display: flex; align-items: center; gap: 10px;">
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Free%20Plan' ); ?>" id="clicksync-activate-free-btn" class="clicksync-btn-primary" style="background: #10b981; color: white; border: none; text-decoration: none; display: inline-flex; align-items: center; height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; line-height: 32px; box-sizing: border-box;">
								<?php esc_html_e( 'Activate Free Plan', 'clicksync-wordpress' ); ?>
							</a>
							<button type="button" id="clicksync-toggle-upgrade-btn" class="clicksync-btn-secondary" style="height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; border: 1px solid #dcdfe3; background: #ffffff; color: #202223; line-height: 30px; box-sizing: border-box;">
								<?php esc_html_e( 'Upgrade Plan', 'clicksync-wordpress' ); ?>
								<svg style="width: 10px; height: 10px; fill: currentColor; margin-left: 2px;" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
							</button>
						</div>
					</div>

					<!-- Paid Plans Drawer (Hidden by default, slides down on click) -->
					<div id="clicksync-upgrade-drawer" style="display: none; border-top: 1px solid #e1e3e5; margin-top: 16px; padding-top: 16px;">
						<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 12px;">
							<!-- Growth Plan -->
							<div id="plan-card-growth" style="background: #fdfdfd; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
								<div>
									<h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: #4c1d95; display: flex; align-items: center; gap: 6px;">
										<?php esc_html_e( 'Growth Plan', 'clicksync-wordpress' ); ?>
										<span class="clicksync-badge plan-active-badge" style="display: none !important; background: #efe6fc; color: #6d28d9; font-size: 9px; font-weight: 600; padding: 1px 4px; border-radius: 8px;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
									</h4>
									<div style="font-size: 16px; font-weight: 700; color: #202223;">
										$19.99<span style="font-size: 11px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/mo', 'clicksync-wordpress' ); ?></span>
									</div>
								</div>
								<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Growth%20Plan' ); ?>" target="_blank" class="clicksync-btn-primary" style="background: #4c1d95; color: white; text-decoration: none; display: inline-flex; align-items: center; height: 32px; font-size: 11px; padding: 0 12px; border-radius: 6px; font-weight: 500; line-height: 32px; box-sizing: border-box;">
									<?php esc_html_e( 'Select Growth', 'clicksync-wordpress' ); ?>
								</a>
							</div>

							<!-- Pro Plan -->
							<div id="plan-card-pro" style="background: #fdfdfd; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
								<div>
									<h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: #c026d3; display: flex; align-items: center; gap: 6px;">
										<?php esc_html_e( 'Pro Plan', 'clicksync-wordpress' ); ?>
										<span class="clicksync-badge plan-active-badge" style="display: none !important; background: #fff4e5; color: #b97a00; font-size: 9px; font-weight: 600; padding: 1px 4px; border-radius: 8px;"><?php esc_html_e( 'Active', 'clicksync-wordpress' ); ?></span>
									</h4>
									<div style="font-size: 16px; font-weight: 700; color: #202223;">
										$49.99<span style="font-size: 11px; font-weight: 400; color: #6d7175;"><?php esc_html_e( '/mo', 'clicksync-wordpress' ); ?></span>
									</div>
								</div>
								<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Pro%20Plan' ); ?>" target="_blank" class="clicksync-btn-primary" style="background: #ff007f; color: white; text-decoration: none; display: inline-flex; align-items: center; height: 32px; font-size: 11px; padding: 0 12px; border-radius: 6px; font-weight: 500; line-height: 32px; box-sizing: border-box;">
									<?php esc_html_e( 'Select Pro', 'clicksync-wordpress' ); ?>
								</a>
							</div>
						</div>
						
						<div style="text-align: right; font-size: 12px;">
							<a href="https://clicksync-connect.apps.loopstates.com/pricing" target="_blank" style="color: #7c3aed; text-decoration: underline; font-weight: 500;">
								<?php esc_html_e( 'View plan features and pricing comparison details on our website ->', 'clicksync-wordpress' ); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Onboarding Plan Activation Notice Banner -->
				<div id="clicksync-rules-lock-banner" style="display: none; background: #fbfbfb; border: 1px solid #dcdfe3; border-left: 4px solid #7c3aed; padding: 12px 16px; border-radius: 6px; font-size: 13px; color: #5c5f62; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
					<svg style="width: 16px; height: 16px; fill: #7c3aed; flex-shrink: 0;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
					<span><?php esc_html_e( 'Please select or activate a subscription plan above to enable configuration rules.', 'clicksync-wordpress' ); ?></span>
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
				<div class="clicksync-card" id="clicksync-orders-rule-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					
					<!-- Header with Toggle Switch -->
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
						<span style="font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg> <?php esc_html_e( 'WooCommerce Order Created', 'clicksync-wordpress' ); ?>
						</span>
						<label class="clicksync-switch">
							<input type="checkbox" id="orders-toggle" class="clicksync-rule-toggle" checked>
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
									<input type="checkbox" id="orders-sync-refunds" checked>
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
									<input type="checkbox" id="orders-sync-fulfillment" checked>
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
									<input type="checkbox" id="orders-split-routing">
									<span class="clicksync-slider"></span>
								</label>
							</div>
						</div>
					</div>

					<!-- Option Pills Toolbar -->
					<div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; background: #f4f6f8; padding: 8px 12px; border-radius: 6px; border: 1px solid #e1e3e5; align-items: center;">
						<span style="font-size: 12px; font-weight: 600; color: #6d7175; margin-right: 4px;"><?php esc_html_e( 'Configure Options:', 'clicksync-wordpress' ); ?></span>
						<button type="button" class="clicksync-option-pill" data-target="orders-assignee-block" data-field="assigneeRulesEnabled"><?php esc_html_e( 'Assignee Routing', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-priority-block" data-field="priorityRulesEnabled"><?php esc_html_e( 'Priority Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-tagging-block" data-field="tagRulesEnabled"><?php esc_html_e( 'Tagging Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-customfields-block" data-field="fieldMappingsEnabled"><?php esc_html_e( 'Custom Fields', 'clicksync-wordpress' ); ?></button>
					</div>

					<!-- 1. Assignee Routing Rules Sub-section -->
					<div id="orders-assignee-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg> <?php esc_html_e( 'Assignee Routing Rules', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="orders-assignee-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-assignee-field clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-assignee-operator clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
									<option value="not_equals">is not equal to</option>
									<option value="contains">contains</option>
									<option value="not_contains">does not contain</option>
									<option value="starts_with">starts with</option>
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="less_than_or_equal">is less than or equal to</option>
									<option value="greater_than">is greater than</option>
									<option value="less_than">is less than</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-assignee-value clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. VIP-Buyer" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Assign To:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-select clicksync-assignees-dropdown" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading members...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<button type="button" class="clicksync-add-assignee-rule-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 2. Task Priority Rules Sub-section -->
					<div id="orders-priority-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> <?php esc_html_e( 'Task Priority Rules', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="orders-priority-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-priority-field clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-priority-operator clicksync-select" style="margin: 0; height: 36px;">
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="equals">is equal to</option>
									<option value="not_equals">is not equal to</option>
									<option value="contains">contains</option>
									<option value="not_contains">does not contain</option>
									<option value="starts_with">starts with</option>
									<option value="less_than_or_equal">is less than or equal to</option>
									<option value="greater_than">is greater than</option>
									<option value="less_than">is less than</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-priority-value clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. 500" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Set Priority:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-priority-level clicksync-select" style="margin: 0; height: 36px;">
									<option value="1">Urgent</option>
									<option value="2">High</option>
									<option value="3">Normal</option>
									<option value="4">Low</option>
								</select>
							</div>
							<button type="button" class="clicksync-add-priority-rule-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 3. Task Tagging Rules Sub-section -->
					<div id="orders-tagging-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg> <?php esc_html_e( 'Task Tagging Rules', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="orders-tagging-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-tag-field clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-tag-operator clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
									<option value="not_equals">is not equal to</option>
									<option value="contains">contains</option>
									<option value="not_contains">does not contain</option>
									<option value="starts_with">starts with</option>
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="less_than_or_equal">is less than or equal to</option>
									<option value="greater_than">is greater than</option>
									<option value="less_than">is less than</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-tag-value clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Apply Tag:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-tag-tag clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. Express-Order" />
							</div>
							<button type="button" class="clicksync-add-tag-rule-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 4. Custom Field Mappings Sub-section -->
					<div id="orders-customfields-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Custom Field Mappings', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="orders-customfields-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<select class="clicksync-field-field clicksync-select" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
							</select>
							<select class="clicksync-select clicksync-field-target" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading custom fields...', 'clicksync-wordpress' ); ?></option>
							</select>
							<button type="button" class="clicksync-add-field-mapping-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Mapping', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- 5. ClickUp Status to WooCommerce Actions Sub-section -->
					<div style="padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
							<div style="font-size: 14px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
								<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> <?php esc_html_e( 'ClickUp Status to WooCommerce Actions', 'clicksync-wordpress' ); ?>
							</div>
						</div>
						
						<div id="orders-status-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<select class="clicksync-select clicksync-statuses-dropdown" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading statuses...', 'clicksync-wordpress' ); ?></option>
							</select>
							<select class="clicksync-status-action clicksync-select" style="margin: 0; height: 36px;">
								<option value="fulfill">Fulfill WooCommerce Order (Complete Order)</option>
								<option value="cancel">Cancel WooCommerce Order</option>
							</select>
							<button type="button" class="clicksync-add-status-mapping-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Action', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

				</div>


				<!-- ========================================== -->
				<!-- CARD 3: WooCommerce Customer Created -->
				<!-- ========================================== -->
				<div class="clicksync-card" id="clicksync-customers-rule-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
						<span style="font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg> <?php esc_html_e( 'WooCommerce Customer Created', 'clicksync-wordpress' ); ?>
						</span>
						<label class="clicksync-switch">
							<input type="checkbox" id="customers-toggle" class="clicksync-rule-toggle" checked>
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
						<button type="button" class="clicksync-option-pill" data-target="customers-tagging-block" data-field="tagRulesEnabled"><?php esc_html_e( 'Tagging Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="customers-customfields-block" data-field="fieldMappingsEnabled"><?php esc_html_e( 'Custom Fields', 'clicksync-wordpress' ); ?></button>
					</div>

					<!-- Task Tagging Rules -->
					<div id="customers-tagging-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg> <?php esc_html_e( 'Task Tagging Rules', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="customers-tagging-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-tag-field clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-tag-operator clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
									<option value="not_equals">is not equal to</option>
									<option value="contains">contains</option>
									<option value="not_contains">does not contain</option>
									<option value="starts_with">starts with</option>
									<option value="greater_than_or_equal">is greater than or equal to</option>
									<option value="less_than_or_equal">is less than or equal to</option>
									<option value="greater_than">is greater than</option>
									<option value="less_than">is less than</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-tag-value clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. VIP-Customer" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Then Apply Tag:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-tag-tag clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. VIP" />
							</div>
							<button type="button" class="clicksync-add-tag-rule-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="customers/create"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>

					<!-- Custom Field Mappings -->
					<div id="customers-customfields-block" style="display: none; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg> <?php esc_html_e( 'Custom Field Mappings', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="customers-customfields-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<select class="clicksync-field-field clicksync-select" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
							</select>
							<select class="clicksync-select clicksync-field-target" style="margin: 0; height: 36px;">
								<option value=""><?php esc_html_e( 'Loading custom fields...', 'clicksync-wordpress' ); ?></option>
							</select>
							<button type="button" class="clicksync-add-field-mapping-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="customers/create"><?php esc_html_e( 'Add Mapping', 'clicksync-wordpress' ); ?></button>
						</div>
					</div>
				</div>

				<!-- Floating / Action Footer to Save Settings -->
				<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; margin-top: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
					<div>
						<h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Save Integration Configurations', 'clicksync-wordpress' ); ?></h4>
						<p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Commit all dropdown target list selections and active switch configuration options to the database.', 'clicksync-wordpress' ); ?></p>
					</div>
					<div>
						<button type="button" id="clicksync-save-all-settings" class="clicksync-btn-primary" style="height: 40px; padding: 0 24px; font-weight: 700; background: #7c3aed; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
							<svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg> <?php esc_html_e( 'Save Settings', 'clicksync-wordpress' ); ?>
						</button>
					</div>
				</div>

			</div> <!-- end main container -->

		</div>
		<?php
	}
}
