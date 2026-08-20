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
		<div class="wrap clicksync-wrap" style="max-width: 1100px; margin: 20px auto;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'User Guide & Documentation', 'clicksync-connect' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Complete documentation, ClickUp rate limit explanations, and troubleshooting instructions.', 'clicksync-connect' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- 1. Quick Setup Walkthrough -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
					<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 2c-.07 0-.13.01-.2.01L9.12 5.61C9.65 6.13 10.3 6.55 11 6.83V9h2V6.83c.7-.28 1.35-.7 1.88-1.22l-2.68-3.6c-.07-.01-.13-.01-.2-.01zM7 9H5c-1.1 0-2 .9-2 2v6h2v4h4v-4h2v-2H9v-2h4v-2c0-.55-.45-1-1-1H7zm12 0h-2c-.55 0-1 .45-1 1v2h4v2h-4v2h2v4h4v-4h2v-6c0-1.1-.9-2-2-2z"/></svg> <?php esc_html_e( 'Quick 3-Step Setup Walkthrough', 'clicksync-connect' ); ?>
				</h3>
				<ol style="margin: 0; padding-left: 20px; font-size: 14px; color: #334155; line-height: 1.8;">
					<li>
						<strong><?php esc_html_e( 'Connect ClickUp Workspace:', 'clicksync-connect' ); ?></strong>
						<?php esc_html_e( 'Go to ClickSync -> Settings and click "Connect ClickUp Workspace". Authorize ClickSync to access your ClickUp lists and spaces.', 'clicksync-connect' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Select Target Destination Lists:', 'clicksync-connect' ); ?></strong>
						<?php esc_html_e( 'Assign specific ClickUp lists for WooCommerce Orders, Customers, Abandoned Checkouts, and Refunds.', 'clicksync-connect' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Configure Custom Fields & Status Actions:', 'clicksync-connect' ); ?></strong>
						<?php esc_html_e( 'Map WooCommerce fields (e.g. order_number, total_price, customer.email) to ClickUp Custom Fields and set up automated status actions.', 'clicksync-connect' ); ?>
					</li>
				</ol>
			</div>

			<!-- 2. Storefront Performance Guarantee -->
			<div class="clicksync-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 20px;">
				<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #0f172a;">
					<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg> <?php esc_html_e( 'Zero Storefront Impact Guarantee', 'clicksync-connect' ); ?>
				</h3>
				<p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.6;">
					<?php esc_html_e( 'ClickSync uses non-blocking background dispatches. When customers complete checkout on your WooCommerce store, the sync payload is handed off asynchronously to our cloud engine in 0ms. Your storefront speed and customer checkout experience remain 100% fast and unaffected.', 'clicksync-connect' ); ?>
				</p>
			</div>

			<!-- 3. FAQ Section -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 16px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
					<svg class="clicksync-title-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg> <?php esc_html_e( 'Frequently Asked Questions', 'clicksync-connect' ); ?>
				</h3>

				<div style="display: flex; flex-direction: column; gap: 16px;">
					<div>
						<strong style="font-size: 14px; color: #0f172a;"><?php esc_html_e( 'Q: What happens if ClickUp rate limits my account?', 'clicksync-connect' ); ?></strong>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_html_e( 'ClickSync automatically catches 429 rate limits, queues the payload, and retries until the task is created in ClickUp.', 'clicksync-connect' ); ?></p>
					</div>

					<div>
						<strong style="font-size: 14px; color: #0f172a;"><?php esc_html_e( 'Q: Can I map custom order fields or checkout notes?', 'clicksync-connect' ); ?></strong>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_html_e( 'Yes! Under Settings -> Custom Field Mapping Engine, you can map any order, customer, or shipping property to your ClickUp fields.', 'clicksync-connect' ); ?></p>
					</div>
				</div>
			</div>

			<!-- 4. Contact & Customization Support Form -->
			<div class="clicksync-card" id="clicksync-contact-form-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 24px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 8px 0; font-size: 17px; font-weight: 700; color: #0f172a;">
					<?php esc_html_e( 'Contact Support & Customization Requests', 'clicksync-connect' ); ?>
				</h3>
				<p style="font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5;">
					<?php esc_html_e( 'Have technical questions, run into issues, or require a bespoke integration customization? Send us a message, and our team will get back to you directly.', 'clicksync-connect' ); ?>
				</p>

				<form id="clicksync-support-contact-form" style="display: flex; flex-direction: column; gap: 14px; max-width: 600px;">
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
						<div>
							<label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;"><?php esc_html_e( 'Your Name:', 'clicksync-connect' ); ?></label>
							<input type="text" name="contact_name" class="clicksync-select" style="margin: 0; width: 100%; height: 38px;" required placeholder="e.g. John Doe" />
						</div>
						<div>
							<label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;"><?php esc_html_e( 'Your Email:', 'clicksync-connect' ); ?></label>
							<input type="email" name="contact_email" class="clicksync-select" style="margin: 0; width: 100%; height: 38px;" required placeholder="e.g. john@example.com" />
						</div>
					</div>

					<div>
						<label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;"><?php esc_html_e( 'Inquiry Subject:', 'clicksync-connect' ); ?></label>
						<select name="contact_subject" class="clicksync-select" style="margin: 0; width: 100%; height: 38px;" required>
							<option value="Technical Support"><?php esc_html_e( 'Technical Support / Help', 'clicksync-connect' ); ?></option>
							<option value="Bespoke Requirement"><?php esc_html_e( 'Bespoke Customization / Custom Request', 'clicksync-connect' ); ?></option>
							<option value="Billing"><?php esc_html_e( 'Billing Inquiry', 'clicksync-connect' ); ?></option>
							<option value="General feedback"><?php esc_html_e( 'General Feedback', 'clicksync-connect' ); ?></option>
						</select>
					</div>

					<div>
						<label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;"><?php esc_html_e( 'Your Message:', 'clicksync-connect' ); ?></label>
						<textarea name="contact_message" class="clicksync-select" style="margin: 0; width: 100%; height: 120px; padding: 10px; resize: vertical;" required placeholder="<?php esc_attr_e( 'Describe your requirements or request in detail...', 'clicksync-connect' ); ?>"></textarea>
					</div>

					<div>
						<button type="submit" class="clicksync-btn-primary" style="height: 40px; padding: 0 24px; font-weight: 700; background: #7c3aed; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
							<svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
							<span><?php esc_html_e( 'Send Message', 'clicksync-connect' ); ?></span>
						</button>
					</div>
			</div>

			<div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 24px; text-align: center; font-size: 13px; color: #64748b;">
				<p style="margin: 0 0 12px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
					A <a href="https://loopstates.com" target="_blank" style="color: inherit; text-decoration: none; font-weight: inherit;">Loopstates</a> Product.
				</p>
				<div style="margin-top: 12px; display: flex; justify-content: center;">
					<a href="https://loopstates.com" target="_blank" style="display: inline-block;">
						<img src="https://loopstates.com/logo.png" alt="Loopstates" style="height: 24px; width: auto; display: block; margin: 0 auto;" />
					</a>
				</div>
			</div>

		</div>
		<?php
	}
}
