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
		$user_mappings_data = Options::get_user_mappings();
		$saved_mappings = $user_mappings_data['mappings'] ?? array();
		$fallback_clickup_id = $user_mappings_data['fallback_clickup_user_id'] ?? '';

		$user_query = new \WP_User_Query( array(
			'role__in' => array( 'administrator', 'shop_manager' ),
			'orderby'  => 'display_name',
			'order'    => 'ASC'
		) );
		$wp_users = $user_query->get_results();

		$host       = parse_url( site_url(), PHP_URL_HOST );
		$is_ssl      = is_ssl() ? 'https' : 'http';
		$connect_url= CLICKSYNC_CLOUD_URL . '/auth/clickup?shop=' . urlencode( $host ) . '&protocol=' . $is_ssl;

		$plan_name  = $account['plan_name'] ?? 'Free Plan';
		$sync_count = (int) ( $account['monthly_sync_count'] ?? 0 );
		$quota      = (int) ( $account['monthly_quota'] ?? 100 );
		$reset_date = $account['last_sync_reset'] ?? date( 'm/d/Y' );
		?>
		<div class="wrap clicksync-wrap" style="max-width: 1050px; margin: 20px auto;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( CLICKSYNC_URL . 'assets/images/logo.png' ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'ClickSync Settings', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Configure automated CRM syncing, custom fields routing, status mappings, and subscription settings.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
			</div>
			
			<!-- Header Status Banner -->
			<div id="clicksync-connection-status-block" 
				data-connect-url="<?php echo esc_url( $connect_url ); ?>"
				data-site-email="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
				data-site-title="<?php echo esc_attr( get_option( 'blogname' ) ); ?>"
				data-site-owner="<?php 
					$current_user = wp_get_current_user();
					echo esc_attr( $current_user ? $current_user->display_name : 'WordPress Admin' ); 
				?>">
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


			<!-- Info / Speed Note Card -->
			<div class="clicksync-card" style="background: #f5f3f9; border: 1px solid #e2dff0; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); font-size: 13px; color: #475569; line-height: 1.6;">
				<div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start;">
					<svg style="width: 16px; height: 16px; fill: #64748b; margin-top: 4px; flex-shrink: 0;" viewBox="0 0 24 24"><path d="M9 21c0 .55.45 1 1 1h4c.55 0 1-.45 1-1v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.86-3.14-7-7-7zm2.85 11.1l-.85.6V16h-4v-2.3l-.85-.6C8.8 12.19 8 10.66 8 9c0-2.21 1.79-4 4-4s4 1.79 4 4c0 1.66-.8 3.19-2.15 4.1z"/></svg>
					<div>
						<strong><?php esc_html_e( 'Why does this page load/save slowly?', 'clicksync-wordpress' ); ?></strong>
						<?php esc_html_e( 'To build your custom field lists, this editor makes real-time, grouped API calls to fetch custom schemas directly from your ClickUp project. Please be patient while saving mappings.', 'clicksync-wordpress' ); ?>
					</div>
				</div>
				<div style="display: flex; gap: 12px; align-items: flex-start;">
					<svg style="width: 16px; height: 16px; fill: #64748b; margin-top: 4px; flex-shrink: 0;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
					<div>
						<strong><?php esc_html_e( 'Storefront Speed:', 'clicksync-wordpress' ); ?></strong>
						<?php esc_html_e( 'Once your mapping is saved, all synchronization runs 100% asynchronously in the background. It adds zero script execution or database overhead to your customer-facing store or WooCommerce checkout flow. Your site speed remains fast and unaffected!', 'clicksync-wordpress' ); ?>
					</div>
				</div>
			</div>

			<!-- Onboarding Wizard Container -->
			<div id="clicksync-onboarding-container" style="display: none;"></div>

			<!-- main container (disabled until ClickUp workspace connection is established) -->
			<div id="clicksync-settings-main-container" class="clicksync-settings-disabled" style="display: none;">

				<!-- Pricing & Usage Quota Block (Slick, Slim Bar) -->
				<div id="billing-section" class="clicksync-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
						<div style="display: flex; flex-direction: column; gap: 4px;">
							<div style="display: flex; align-items: center; gap: 8px;">
								<span style="font-size: 14px; font-weight: 600; color: #202223;"><?php esc_html_e( 'Subscription Plan:', 'clicksync-wordpress' ); ?></span>
								<span id="clicksync-active-plan-badge" class="clicksync-badge badge-info" style="background: #e2f1f8; color: #005a87; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">
									<?php echo esc_html( $plan_name ); ?>
								</span>
							</div>
							<div style="font-size: 12px; color: #6d7175;">
								<strong><?php esc_html_e( 'Usage Quota:', 'clicksync-wordpress' ); ?></strong> 
								<span id="clicksync-usage-count"><?php echo esc_html( number_format( $sync_count ) ); ?></span> / <span id="clicksync-usage-quota"><?php echo esc_html( number_format( $quota ) ); ?></span> runs. (Reset: <span id="clicksync-usage-reset"><?php echo esc_html( ! empty( $reset_date ) ? date( 'Y-m-d H:i:s', strtotime( $reset_date ) ) : __( 'Pending Sync', 'clicksync-wordpress' ) ); ?></span>)
							</div>
						</div>
						<div style="display: flex; align-items: center; gap: 10px;">
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Free%20Plan' ); ?>" id="clicksync-activate-free-btn" class="clicksync-btn-primary clicksync-btn-green" style="border: none; text-decoration: none; display: inline-flex; align-items: center; height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; line-height: 32px; box-sizing: border-box;">
								<?php esc_html_e( 'Activate Free Plan', 'clicksync-wordpress' ); ?>
							</a>
							<button type="button" id="clicksync-toggle-upgrade-btn" class="clicksync-btn-secondary" style="height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; border: 1px solid #dcdfe3; background: #ffffff; color: #202223; line-height: 30px; box-sizing: border-box;">
								<?php esc_html_e( 'Upgrade Plan', 'clicksync-wordpress' ); ?>
								<svg style="width: 10px; height: 10px; fill: currentColor; margin-left: 2px;" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>
							</button>
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Pro%20Plan' ); ?>" id="clicksync-upgrade-to-pro-btn" class="clicksync-btn-primary clicksync-btn-pro" style="display: none; border: none; text-decoration: none; align-items: center; height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; line-height: 32px; box-sizing: border-box;">
								<?php esc_html_e( 'Upgrade to Pro', 'clicksync-wordpress' ); ?>
							</a>
							<a href="mailto:support@loopstates.com" id="clicksync-custom-quota-btn" class="clicksync-btn-secondary" style="display: none; height: 32px; font-size: 12px; padding: 0 12px; border-radius: 6px; font-weight: 500; align-items: center; gap: 4px; cursor: pointer; border: 1px solid #dcdfe3; background: #ffffff; color: #202223; line-height: 30px; box-sizing: border-box; text-decoration: none;">
								<?php esc_html_e( 'Request Custom Quota', 'clicksync-wordpress' ); ?>
							</a>
						</div>
					</div>
					<?php 
					$progress_pct = $quota > 0 ? min( 100, max( 0, ( $sync_count / $quota ) * 100 ) ) : 0;
					$bar_color = '#10b981';
					if ( $progress_pct >= 80 ) {
						$bar_color = '#ef4444';
					} elseif ( $progress_pct >= 50 ) {
						$bar_color = '#f59e0b';
					}
					?>
					<div class="clicksync-quota-progress-container" style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 14px;">
						<div id="clicksync-quota-progress-bar" style="width: <?php echo esc_attr( $progress_pct ); ?>%; height: 100%; background: <?php echo esc_attr( $bar_color ); ?>; border-radius: 3px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></div>
					</div>

					<!-- Paid Plans Drawer (Hidden by default, slides down on click) -->
					<div id="clicksync-upgrade-drawer" style="display: none; border-top: 1px solid #e1e3e5; margin-top: 16px; padding-top: 16px;">
						<div style="display: flex; gap: 16px; justify-content: space-between; width: 100%; box-sizing: border-box; margin-bottom: 12px;">
							<!-- Growth Pill -->
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Growth%20Plan' ); ?>" target="_blank" id="plan-card-growth" class="clicksync-premium-pill clicksync-pill-growth">
								<span class="plan-badge">Growth</span>
								<span class="plan-details">1,000 tasks/mo at $19.99/mo</span>
								<span class="plan-action">Upgrade &rarr;</span>
							</a>

							<!-- Pro Pill -->
							<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . urlencode( $host ) . '&plan=Pro%20Plan' ); ?>" target="_blank" id="plan-card-pro" class="clicksync-premium-pill clicksync-pill-pro">
								<span class="plan-badge">Pro</span>
								<span class="plan-details">10,000 tasks/mo at $49.99/mo</span>
								<span class="plan-action">Upgrade &rarr;</span>
							</a>
						</div>
						
						<div style="text-align: center; font-size: 12px; margin-top: 12px;">
							<a href="https://clicksync-connect.apps.loopstates.com/" target="_blank" style="color: #7c3aed; text-decoration: underline; font-weight: 500;">
								<?php esc_html_e( 'Compare plan features &rarr;', 'clicksync-wordpress' ); ?>
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
										<span id="clicksync-refunds-lock-tooltip" class="clicksync-unidirectional-tooltip" tabindex="0" style="display: inline-flex; align-items: center; color: #ef4444; cursor: help; margin-left: 4px;" data-tooltip="<?php esc_attr_e( 'This feature is locked on the Free Plan. Upgrade to a Growth or Pro Plan to enable refund syncing.', 'clicksync-wordpress' ); ?>">
											<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
										</span>
									</div>
									<div style="font-size: 12px; color: #6d7175; margin-top: 2px;">
										<?php esc_html_e( 'Post detailed refund comments and line items to ClickUp order tasks when orders are refunded in WooCommerce.', 'clicksync-wordpress' ); ?>
									</div>
								</div>
								<label class="clicksync-switch">
									<input type="checkbox" id="orders-sync-refunds" <?php checked( ! empty( $settings['refunds_enabled'] ) ); ?>>
									<span class="clicksync-slider"></span>
								</label>
							</div>

							<!-- Split Order Routing -->
							<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e1e3e5; padding-top: 12px;">
								<div>
									<div style="font-size: 13px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 6px;">
										<svg class="clicksync-title-icon" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.1-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg> <?php esc_html_e( 'Split Order Routing', 'clicksync-wordpress' ); ?>
										<span id="clicksync-split-routing-lock-tooltip" class="clicksync-unidirectional-tooltip" tabindex="0" style="display: inline-flex; align-items: center; color: #ef4444; cursor: help; margin-left: 4px;" data-tooltip="<?php esc_attr_e( 'This feature is locked on the Free Plan. Upgrade to the Pro Plan to enable line-item split routing.', 'clicksync-wordpress' ); ?>">
											<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
										</span>
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
						<button type="button" class="clicksync-option-pill" data-target="orders-list-routing-block" data-field="listRulesEnabled"><?php esc_html_e( 'Regional List Routing', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-assignee-block" data-field="assigneeRulesEnabled"><?php esc_html_e( 'Assignee Routing', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-priority-block" data-field="priorityRulesEnabled"><?php esc_html_e( 'Priority Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-tagging-block" data-field="tagRulesEnabled"><?php esc_html_e( 'Tagging Rules', 'clicksync-wordpress' ); ?></button>
						<button type="button" class="clicksync-option-pill" data-target="orders-customfields-block" data-field="fieldMappingsEnabled"><?php esc_html_e( 'Custom Fields', 'clicksync-wordpress' ); ?></button>
					</div>

					<!-- Regional List Routing Rules Sub-section -->
					<div id="orders-list-routing-block" style="display: none; margin-bottom: 16px; padding: 16px; background: #f9fafb; border-radius: 6px; border: 1px solid #edeeef;">
						<div style="font-size: 14px; font-weight: 600; color: #202223; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
							<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.53c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg> <?php esc_html_e( 'Regional List Routing Rules', 'clicksync-wordpress' ); ?>
						</div>
						
						<div id="orders-list-routing-rules-list" style="margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;"></div>

						<div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto; gap: 10px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 12px;">
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'If WooCommerce Field:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-list-routing-field clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading fields...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Operator:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-list-routing-operator clicksync-select" style="margin: 0; height: 36px;">
									<option value="equals">is equal to</option>
									<option value="not_equals">is not equal to</option>
									<option value="contains">contains</option>
									<option value="not_contains">does not contain</option>
									<option value="starts_with">starts with</option>
									<option value="ends_with">ends with</option>
								</select>
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Compare Value:', 'clicksync-wordpress' ); ?></label>
								<input type="text" class="clicksync-list-routing-value clicksync-select" style="margin: 0; height: 36px;" placeholder="e.g. US" />
							</div>
							<div>
								<label style="display: block; font-size: 11px; font-weight: 500; color: #6d7175; margin-bottom: 4px;"><?php esc_html_e( 'Route to List:', 'clicksync-wordpress' ); ?></label>
								<select class="clicksync-list-routing-list clicksync-select" style="margin: 0; height: 36px;">
									<option value=""><?php esc_html_e( 'Loading lists...', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>
							<button type="button" class="clicksync-add-list-routing-rule-btn clicksync-btn-secondary" style="height: 36px; white-space: nowrap;" data-event="orders/create"><?php esc_html_e( 'Add Rule', 'clicksync-wordpress' ); ?></button>
						</div>
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
									<option value="1">🔴 Urgent</option>
									<option value="2">🟡 High</option>
									<option value="3">🔵 Normal</option>
									<option value="4">⚪ Low</option>
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
								<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> <?php esc_html_e( 'Automatic Order Status Sync', 'clicksync-wordpress' ); ?>
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

				<!-- User Identity Mappings Card -->
				<div class="clicksync-card" id="clicksync-user-mappings-card" style="background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
						<svg class="clicksync-title-icon" style="fill: #7c3aed; width: 20px; height: 20px;" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
						<h3 style="margin: 0; font-size: 16px; font-weight: 600;"><?php esc_html_e( 'User Identity Mappings', 'clicksync-wordpress' ); ?></h3>
					</div>
					<p style="font-size: 13px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">
						Map your WordPress administrators and shop managers to their respective ClickUp workspace accounts. This ensures that WooCommerce order updates and notes made by these users show their names in ClickUp, and comments created by them in ClickUp sync back to WooCommerce correctly.
					</p>

					<!-- User Mappings Table -->
					<div style="margin-bottom: 16px;">
						<table class="wp-list-table widefat fixed striped" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: none;">
							<thead>
								<tr>
									<th style="font-weight: 600; padding: 10px 14px; font-size: 12px; color: #475569;"><?php esc_html_e( 'WordPress User (Admin / Shop Manager)', 'clicksync-wordpress' ); ?></th>
									<th style="font-weight: 600; padding: 10px 14px; font-size: 12px; color: #475569;"><?php esc_html_e( 'Mapped ClickUp Workspace Member', 'clicksync-wordpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! empty( $wp_users ) ) : ?>
									<?php foreach ( $wp_users as $user ) : ?>
										<?php 
										$current_mapped = $saved_mappings[ $user->ID ] ?? '';
										?>
										<tr class="clicksync-user-mapping-row" data-wp-user-id="<?php echo esc_attr( $user->ID ); ?>">
											<td style="padding: 10px 14px; font-size: 13px; font-weight: 500; vertical-align: middle;">
												<strong><?php echo esc_html( $user->display_name ); ?></strong> 
												<span style="font-size: 11px; color: #64748b; font-weight: normal; margin-left: 4px;">(<?php echo esc_html( $user->user_email ); ?>)</span>
											</td>
											<td style="padding: 6px 14px;">
												<select class="clicksync-member-mapping-select clicksync-select" style="margin: 0; width: 100%; height: 32px;" data-selected="<?php echo esc_attr( $current_mapped ); ?>">
													<option value=""><?php esc_html_e( '-- Choose Workspace Member --', 'clicksync-wordpress' ); ?></option>
												</select>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else : ?>
									<tr>
										<td colspan="2" style="padding: 14px; text-align: center; color: #64748b;"><?php esc_html_e( 'No administrators or shop managers found.', 'clicksync-wordpress' ); ?></td>
									</tr>
								<?php endif; ?>
								<!-- Fallback user row -->
								<tr class="clicksync-fallback-mapping-row">
									<td style="padding: 10px 14px; font-size: 13px; font-weight: 600; vertical-align: middle; background: #fafafa;">
										<span><?php esc_html_e( 'Fallback / Unmapped Users', 'clicksync-wordpress' ); ?></span>
										<span style="font-size: 11px; color: #b45309; background: #fffbeb; font-weight: 600; padding: 1px 6px; border-radius: 4px; margin-left: 8px;">REQUIRED</span>
									</td>
									<td style="padding: 6px 14px; background: #fafafa;">
										<select id="clicksync-fallback-member-select" class="clicksync-select" style="margin: 0; width: 100%; height: 32px;" data-selected="<?php echo esc_attr( $fallback_clickup_id ); ?>">
											<option value=""><?php esc_html_e( '-- Choose Workspace Member --', 'clicksync-wordpress' ); ?></option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Multi-Store Network Connections Card (Pro Only) -->
				<div class="clicksync-card" id="clicksync-multistore-card" style="display: none; background: #ffffff; border: 1px solid #e1e3e5; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
						<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg style="width: 18px; height: 18px; fill: #6A2B8F;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.53c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
							<span><?php esc_html_e( 'Multi-Store Network Sync', 'clicksync-wordpress' ); ?></span>
							<span id="clicksync-multistore-lock-tooltip" class="clicksync-unidirectional-tooltip" tabindex="0" style="display: none; align-items: center; color: #ef4444; cursor: help; margin-left: 4px;" data-tooltip="<?php esc_attr_e( 'This feature is locked on Free and Growth plans. Upgrade to the Pro Plan to enable multi-store network sync.', 'clicksync-wordpress' ); ?>">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							</span>
						</h3>
					</div>
					<p style="font-size: 13px; color: #6d7175; margin-bottom: 12px; line-height: 1.5;">
						<?php esc_html_e( 'The Pro Plan allows connecting up to 5 stores or WordPress Multisite nodes to the same ClickUp workspace. Below are the connected stores sharing this subscription:', 'clicksync-wordpress' ); ?>
					</p>
					<div id="clicksync-multistore-list-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; max-height: 250px; overflow-y: auto;">
						<table style="width: 100%; border-collapse: collapse; font-size: 13px;">
							<thead>
								<tr style="border-bottom: 1px solid #e2e8f0; text-align: left; color: #64748b;">
									<th style="padding: 6px 12px 6px 0; font-weight: 600;">Store Domain</th>
									<th style="padding: 6px 12px; font-weight: 600;">Status</th>
									<th style="padding: 6px 0 6px 12px; font-weight: 600; text-align: right;">Connection Role</th>
								</tr>
							</thead>
							<tbody id="clicksync-multistore-list">
								<!-- Populated dynamically via AJAX -->
							</tbody>
						</table>
					</div>
				</div>

				<!-- Floating / Action Footer to Save Settings -->
				<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; margin-top: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
					<div>
						<h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Save Integration Configurations', 'clicksync-wordpress' ); ?></h4>
						<p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;"><?php esc_html_e( 'Commit all dropdown target list selections and active switch configuration options to the database.', 'clicksync-wordpress' ); ?></p>
					</div>
					<div>
						<button type="button" id="clicksync-save-all-settings" class="clicksync-btn-primary" style="height: 40px; padding: 0 24px; font-weight: 700; background: #008060; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
							<svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> <?php esc_html_e( 'Save Settings', 'clicksync-wordpress' ); ?>
						</button>
					</div>
				</div>

				<!-- Custom Quota Request Modal -->
				<div id="clicksync-quota-modal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
					<div class="clicksync-card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); margin: 0 16px; padding: 24px; box-sizing: border-box; position: relative; animation: clicksync-modal-fade 0.2s ease-out;">
						<button type="button" id="clicksync-close-quota-modal" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
						
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
							<div style="background: #eff6ff; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: #2563eb;">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
							</div>
							<h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Request Custom Quota', 'clicksync-wordpress' ); ?></h3>
						</div>

						<p style="font-size: 13px; color: #475569; margin: 0 0 16px 0; line-height: 1.5;">
							<?php esc_html_e( 'Need higher monthly sync limits? Describe your monthly order volume or business requirements below, and our team will customize your plan limits.', 'clicksync-wordpress' ); ?>
						</p>

						<form id="clicksync-quota-request-form">
							<div style="margin-bottom: 16px;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;"><?php esc_html_e( 'Detailed Requirements:', 'clicksync-wordpress' ); ?></label>
								<textarea id="clicksync-quota-message" class="clicksync-select" style="width: 100%; height: 100px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; resize: vertical; box-sizing: border-box;" required placeholder="<?php esc_attr_e( 'e.g. We expect 15,000 orders per month and need a custom volume plan...', 'clicksync-wordpress' ); ?>"></textarea>
							</div>

							<div style="display: flex; justify-content: flex-end; gap: 8px;">
								<button type="button" id="clicksync-cancel-quota-modal" class="clicksync-btn-secondary" style="height: 36px; padding: 0 16px; font-size: 13px; font-weight: 500;"><?php esc_html_e( 'Cancel', 'clicksync-wordpress' ); ?></button>
								<button type="submit" class="clicksync-btn-primary" style="height: 36px; padding: 0 16px; font-size: 13px; font-weight: 600; background: #2563eb; color: #ffffff; border: none; border-radius: 6px; cursor: pointer;"><?php esc_html_e( 'Submit Request', 'clicksync-wordpress' ); ?></button>
							</div>
						</form>
					</div>
				</div>

				<!-- Cancel Subscription Modal -->
				<div id="clicksync-cancel-modal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
					<div class="clicksync-card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); margin: 0 16px; padding: 24px; box-sizing: border-box; position: relative; animation: clicksync-modal-fade 0.2s ease-out;">
						<button type="button" id="clicksync-close-cancel-modal" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
						
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
							<div style="background: #fef2f2; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
							</div>
							<h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Cancel Your ClickSync Subscription', 'clicksync-wordpress' ); ?></h3>
						</div>

						<p style="font-size: 13px; color: #475569; margin: 0 0 16px 0; line-height: 1.5;">
							<?php esc_html_e( 'We are sorry to see you go. Canceling will immediately suspend your synchronization rules and restrict access to the plugin settings page.', 'clicksync-wordpress' ); ?>
						</p>

						<form id="clicksync-cancel-subscription-form">
							<div style="margin-bottom: 16px;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;"><?php esc_html_e( 'Reason for Canceling:', 'clicksync-wordpress' ); ?></label>
								<select id="clicksync-cancel-reason" class="clicksync-select" style="width: 100% !important; max-width: 100% !important; height: 40px !important; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box !important;" required>
									<option value=""><?php esc_html_e( '-- Select Reason --', 'clicksync-wordpress' ); ?></option>
									<option value="Too Expensive"><?php esc_html_e( 'Too Expensive / High Price', 'clicksync-wordpress' ); ?></option>
									<option value="Missing Features"><?php esc_html_e( 'Missing Essential Features', 'clicksync-wordpress' ); ?></option>
									<option value="Difficult Setup"><?php esc_html_e( 'Too Complicated / Difficult Setup', 'clicksync-wordpress' ); ?></option>
									<option value="Temporary Project"><?php esc_html_e( 'Temporary Project or Site Closed', 'clicksync-wordpress' ); ?></option>
									<option value="Other"><?php esc_html_e( 'Other Reason', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>

							<div style="margin-bottom: 16px;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;"><?php esc_html_e( 'Additional Feedback (Optional):', 'clicksync-wordpress' ); ?></label>
								<textarea id="clicksync-cancel-feedback" class="clicksync-textarea" style="width: 100% !important; max-width: 100% !important; height: 80px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; resize: vertical; box-sizing: border-box !important;" placeholder="<?php esc_attr_e( 'Please tell us what we could do to make the plugin better...', 'clicksync-wordpress' ); ?>"></textarea>
							</div>

							<!-- Notice -->
							<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px; font-size: 11px; color: #991b1b; line-height: 1.4; margin-bottom: 16px;">
								<?php esc_html_e( 'Please note: For security and data compliance purposes, the complete removal of your integration logs, site telemetry, and database records may take up to 30 days.', 'clicksync-wordpress' ); ?>
							</div>

							<div style="font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 12px;">
								<?php esc_html_e( 'Are you sure you want to cancel?', 'clicksync-wordpress' ); ?>
							</div>

							<div style="display: flex; justify-content: flex-end; gap: 8px;">
								<button type="submit" class="clicksync-btn-secondary" style="height: 36px; padding: 0 16px; font-size: 13px; font-weight: 500; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer;"><?php esc_html_e( 'Confirm Cancellation', 'clicksync-wordpress' ); ?></button>
								<button type="button" id="clicksync-cancel-keep-btn" class="clicksync-btn-primary" style="height: 36px; padding: 0 16px; font-size: 13px; font-weight: 600; background: #008060; color: #ffffff; border: none; border-radius: 6px; cursor: pointer;"><?php esc_html_e( 'Keep Subscription', 'clicksync-wordpress' ); ?></button>
							</div>
						</form>
					</div>
				</div>

				<style>
					@keyframes clicksync-modal-fade {
						from { opacity: 0; transform: scale(0.95); }
						to { opacity: 1; transform: scale(1); }
					}
				</style>

			</div> <!-- end main container -->

		</div>
		<?php
	}
}
