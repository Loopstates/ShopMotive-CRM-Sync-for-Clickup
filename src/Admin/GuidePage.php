<?php

namespace ClickSync\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class GuidePage
 * 
 * Renders the dedicated User Guide & Documentation page matching Shopify ClickSync (/app.guide).
 */
class GuidePage {

	/**
	 * Render the full user guide page HTML.
	 */
	public static function render() {
		$logo_url = CLICKSYNC_URL . 'assets/images/logo.png';
		?>
		<div class="wrap clicksync-wrap" style="max-width: 1100px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'San Francisco', 'Segoe UI', Roboto, sans-serif;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							📖 <?php esc_html_e( 'User Guide & Documentation', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Complete documentation, ClickUp rate limit explanations, and troubleshooting instructions.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- 1. Quick Setup Walkthrough -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
					🚀 <?php esc_html_e( 'Quick 3-Step Setup Walkthrough', 'clicksync-wordpress' ); ?>
				</h3>
				<ol style="margin: 0; padding-left: 20px; font-size: 14px; color: #334155; line-height: 1.8;">
					<li>
						<strong><?php esc_html_e( 'Connect ClickUp Workspace:', 'clicksync-wordpress' ); ?></strong>
						<?php esc_html_e( 'Go to ClickSync → Settings and click "Connect ClickUp Workspace". Authorize ClickSync to access your ClickUp lists and spaces.', 'clicksync-wordpress' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Select Target Destination Lists:', 'clicksync-wordpress' ); ?></strong>
						<?php esc_html_e( 'Assign specific ClickUp lists for WooCommerce Orders, Customers, Abandoned Checkouts, and Refunds.', 'clicksync-wordpress' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Configure Custom Fields & Status Actions:', 'clicksync-wordpress' ); ?></strong>
						<?php esc_html_e( 'Map WooCommerce fields (e.g. order_number, total_price, customer.email) to ClickUp Custom Fields and set up automated status actions.', 'clicksync-wordpress' ); ?>
					</li>
				</ol>
			</div>

			<!-- 2. Storefront Performance Guarantee -->
			<div class="clicksync-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 20px;">
				<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #0f172a;">
					⚡ <?php esc_html_e( 'Zero Storefront Impact Guarantee', 'clicksync-wordpress' ); ?>
				</h3>
				<p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.6;">
					<?php esc_html_e( 'ClickSync uses non-blocking background dispatches. When customers complete checkout on your WooCommerce store, the sync payload is handed off asynchronously to our cloud engine in 0ms. Your storefront speed and customer checkout experience remain 100% fast and unaffected.', 'clicksync-wordpress' ); ?>
				</p>
			</div>

			<!-- 3. FAQ Section -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
					❓ <?php esc_html_e( 'Frequently Asked Questions', 'clicksync-wordpress' ); ?>
				</h3>

				<div style="display: flex; flex-direction: column; gap: 16px;">
					<div>
						<strong style="font-size: 14px; color: #0f172a;"><?php esc_html_e( 'Q: What happens if ClickUp rate limits my account?', 'clicksync-wordpress' ); ?></strong>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_html_e( 'ClickSync automatically catches 429 rate limits, queues the payload, and retries until the task is created in ClickUp.', 'clicksync-wordpress' ); ?></p>
					</div>

					<div>
						<strong style="font-size: 14px; color: #0f172a;"><?php esc_html_e( 'Q: Can I map custom order fields or checkout notes?', 'clicksync-wordpress' ); ?></strong>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_html_e( 'Yes! Under Settings → Custom Field Mapping Engine, you can map any order, customer, or shipping property to your ClickUp fields.', 'clicksync-wordpress' ); ?></p>
					</div>
				</div>
			</div>

		</div>
		<?php
	}
}
