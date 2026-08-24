<?php
/**
 * Class HelpPage
 * 
 * Renders the comprehensive Help, Documentation, and Support Desk page.
 */

namespace ClickSync\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HelpPage {

	/**
	 * Render the Help & Documentation page HTML.
	 */
	public static function render() {
		$logo_url = CLICKSYNC_URL . 'assets/images/logo.png';
		$account = \ClickSync\Core\Options::get_account();
		$secret_key = isset( $account['secret_key'] ) ? $account['secret_key'] : '';
		$cancel_url = admin_url( 'admin.php?page=swiftsync&clicksync_secret_key=' . urlencode( $secret_key ) . '&clicksync_action=cancel_subscription' );
		?>

		<div class="wrap clicksync-help-container clicksync-wrap">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e1e3e5;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="SwiftSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #202223; line-height: 1.2;">
							<?php esc_html_e( 'Help Center & Documentation', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #6d7175;">
							<?php esc_html_e( 'Learn how to configure workflows, map custom fields, resolve sync errors, and get direct assistance.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="clicksync-grid">
				
				<!-- Left Column: Comprehensive Guides -->
				<div>
					
					<!-- 1. Connection & Initial Setup Guide -->
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
						<h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #202223;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
							<?php esc_html_e( '1. Connection & Setup Walkthrough', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<ul class="clicksync-step-list">
							<li class="clicksync-step-item">
								<span class="clicksync-step-badge"><?php esc_html_e( 'Step 1', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Authorize ClickUp Workspace', 'swiftsync-connect-clickup-with-woocommerce' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'Navigate to the Settings page and click "Connect ClickUp Workspace". This redirects you to ClickUp\'s authorization portal. Select the workspace team you wish to connect, and approve permissions. This creates a secure, encrypted token connection.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</li>
							<li class="clicksync-step-item">
								<span class="clicksync-step-badge"><?php esc_html_e( 'Step 2', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Bind Target Workspace Lists', 'swiftsync-connect-clickup-with-woocommerce' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'Select the specific ClickUp lists where WooCommerce Orders, Customers, Refunds, and Abandoned Checkouts should sync. Keeping separate lists organizes your CRM workspace cleanly.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</li>
							<li class="clicksync-step-item">
								<span class="clicksync-step-badge"><?php esc_html_e( 'Step 3', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Validate Webhook Communications', 'swiftsync-connect-clickup-with-woocommerce' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'SwiftSync handles all synchronization tasks asynchronously in the background. Saving settings registers webhooks automatically. If the connection panel shows "Connected", bi-directional note and status sync is active.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</li>
						</ul>
					</div>

					<!-- 2. Status Mapping & Fulfillment Sync -->
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
						<h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #202223;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
							<?php esc_html_e( '2. WooCommerce Status & Fulfillment Mapping', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<p style="font-size: 13px; color: #6d7175; line-height: 1.6; margin-bottom: 16px;">
							<?php esc_html_e( 'SwiftSync workflows are fully bi-directional. Order state edits in WooCommerce update task fields automatically. Likewise, moving tasks or updating columns inside ClickUp propagates back to WooCommerce.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</p>
						<ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #6d7175; line-height: 1.8;">
							<li><strong><?php esc_html_e( 'Custom Status Actions:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'Map individual WooCommerce order statuses (e.g. processing, completed, refund-requested) to your specific ClickUp list statuses.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
							<li><strong><?php esc_html_e( 'Fulfillment & Shipped Sync:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'Turn on "Sync Fulfillment Statuses" to push tracking number details and automatically complete orders when tasks are marked as completed or shipped inside ClickUp.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
							<li><strong><?php esc_html_e( 'Refund Tracking:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'Activating "Sync Refund Updates" pushes partial returns, totals, and item details to priority tasks automatically.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
						</ul>
					</div>

					<!-- 3. Advanced Custom Field Mapping Recipe -->
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
						<h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #202223;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
							<?php esc_html_e( '3. Advanced Custom Field Routing Recipes', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<p style="font-size: 13px; color: #6d7175; line-height: 1.6; margin-bottom: 16px;">
							<?php esc_html_e( 'Map any core WooCommerce data fields or custom checkout meta keys into ClickUp Custom Fields. Set your matching field types in ClickUp first:', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</p>
						<table class="wp-list-table widefat fixed striped" style="width: 100%; border: 1px solid #e1e3e5; border-collapse: collapse; font-size: 12px; box-shadow: none; margin: 0;">
							<thead>
								<tr>
									<th style="padding: 10px 14px; font-weight: 600; color: #202223; font-size: 12px;"><?php esc_html_e( 'WooCommerce Field Key', 'swiftsync-connect-clickup-with-woocommerce' ); ?></th>
									<th style="padding: 10px 14px; font-weight: 600; color: #202223; font-size: 12px;"><?php esc_html_e( 'Recommended ClickUp Type', 'swiftsync-connect-clickup-with-woocommerce' ); ?></th>
									<th style="padding: 10px 14px; font-weight: 600; color: #202223; font-size: 12px;"><?php esc_html_e( 'Description', 'swiftsync-connect-clickup-with-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="padding: 10px 14px; font-family: monospace;">total_price</td>
									<td style="padding: 10px 14px;"><span class="clicksync-badge-pill">Number / Currency</span></td>
									<td style="padding: 10px 14px; color: #6d7175;"><?php esc_html_e( 'The gross order total amount.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 10px 14px; font-family: monospace;">customer.email</td>
									<td style="padding: 10px 14px;"><span class="clicksync-badge-pill">Email</span></td>
									<td style="padding: 10px 14px; color: #6d7175;"><?php esc_html_e( 'Merchant email to query customers.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 10px 14px; font-family: monospace;">payment_method_title</td>
									<td style="padding: 10px 14px;"><span class="clicksync-badge-pill">Short Text</span></td>
									<td style="padding: 10px 14px; color: #6d7175;"><?php esc_html_e( 'Checkout provider used (e.g. Stripe, PayPal).', 'swiftsync-connect-clickup-with-woocommerce' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 10px 14px; font-family: monospace;">_billing_phone</td>
									<td style="padding: 10px 14px;"><span class="clicksync-badge-pill">Phone</span></td>
									<td style="padding: 10px 14px; color: #6d7175;"><?php esc_html_e( 'WooCommerce billing telephone meta data.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- 4. Comments & User Mappings -->
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
						<h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #202223;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
							<?php esc_html_e( '4. Real-time Bi-directional Comments & Users Mapping', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<p style="font-size: 13px; color: #6d7175; line-height: 1.6; margin-bottom: 12px;">
							<?php esc_html_e( 'To preserve comment audit trails in ClickUp, SwiftSync automatically handles identity resolution:', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</p>
						<ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #6d7175; line-height: 1.8;">
							<li><strong><?php esc_html_e( 'Identity Association:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'Associate your WordPress administrators/shop managers with their ClickUp workspace accounts under the "User Identity Mappings" panel.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
							<li><strong><?php esc_html_e( 'Comment Authorship:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'Adding order notes or completion comments in WooCommerce attributes the action to the mapped workspace member inside ClickUp. External API updates use your configured fallback user.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
							<li><strong><?php esc_html_e( 'Feedback Loop Lock:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></strong> <?php esc_html_e( 'SwiftSync includes smart filters to prevent echo feedback loops. Internal metadata keeps WooCommerce notes and ClickUp comment responses from repeating endlessly.', 'swiftsync-connect-clickup-with-woocommerce' ); ?></li>
						</ol>
					</div>

					<!-- 5. Frequently Asked Questions -->
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
						<h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #202223;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
							<?php esc_html_e( 'Frequently Asked Questions', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<div class="clicksync-faq-item">
							<details style="outline: none;">
								<summary class="clicksync-faq-q">
									<?php esc_html_e( 'Will SwiftSync slow down my customer checkout times?', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</summary>
								<div class="clicksync-faq-a" style="margin-top: 8px;">
									<?php esc_html_e( 'No. SwiftSync works 100% asynchronously. The moment checkouts finish, the sync event payload is handed off to our background cloud processor in 0ms, leaving your customer loading speed completely unaffected.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</details>
						</div>
						<div class="clicksync-faq-item">
							<details style="outline: none;">
								<summary class="clicksync-faq-q">
									<?php esc_html_e( 'What happens if a rate limit or server error blocks ClickUp?', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</summary>
								<div class="clicksync-faq-a" style="margin-top: 8px;">
									<?php esc_html_e( 'Events are ingested into our fail-safe cloud database queue. If ClickUp is offline or rate limits your account, the transaction retries automatically in back-off intervals until task creation succeeds.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</details>
						</div>
						<div class="clicksync-faq-item">
							<details style="outline: none;">
								<summary class="clicksync-faq-q">
									<?php esc_html_e( 'How do I force manual sync if order parameters change?', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</summary>
								<div class="clicksync-faq-a" style="margin-top: 8px;">
									<?php esc_html_e( 'Go to any WooCommerce order page. The SwiftSync Meta Box widget features a "Sync Now" control. Clicking this runs a synchronous manual sync pipeline instantly, pulling updated details into WooCommerce.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</details>
						</div>
						<div class="clicksync-faq-item" style="border-bottom: none; padding-bottom: 0;">
							<details style="outline: none;">
								<summary class="clicksync-faq-q">
									<?php esc_html_e( 'How do I manage or cancel my billing subscription?', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</summary>
								<div class="clicksync-faq-a" style="margin-top: 8px;">
									<?php esc_html_e( 'Your subscription plan can be managed directly. If you wish to cancel or modify your active SwiftSync plan subscription, ', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
									<a href="<?php echo esc_url( $cancel_url ); ?>" style="color: #008060; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
										<?php esc_html_e( 'click here to request cancellation', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
										<svg style="width: 12px; height: 12px; fill: #008060;" viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
									</a>.
									<?php esc_html_e( ' Stored integration metadata and site telemetry on the cloud will be fully deleted within 30 days of cancellation.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
								</div>
							</details>
						</div>
					</div>

				</div>

				<!-- Right Column: Support Ticket Form -->
				<div class="clicksync-help-sticky-col">
					<div class="clicksync-card" style="border: 1px solid #e1e3e5; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #ffffff; padding: 24px;">
						<h3 style="margin-top: 0; margin-bottom: 8px; font-size: 16px; font-weight: 700; color: #202223; display: flex; align-items: center; gap: 8px;">
							<svg style="width: 18px; height: 18px; fill: #008060;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 9h-2V5h2v6zm0 4h-2v-2h2v2z"/></svg>
							<?php esc_html_e( 'Contact Support', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</h3>
						<p style="font-size: 12px; color: #6d7175; line-height: 1.5; margin-bottom: 20px;">
							<?php esc_html_e( 'Need help setting up mappings, running into bugs, or require customized custom-coded rules? Send us a message, and our engineers will respond directly.', 'swiftsync-connect-clickup-with-woocommerce' ); ?>
						</p>

						<form id="clicksync-support-contact-form" style="margin: 0;">
							<div class="clicksync-help-input-group">
								<span style="display: block; font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 6px;"><?php esc_html_e( 'Your Name:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<input type="text" name="contact_name" style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #e1e3e5; border-radius: 6px; font-size: 13px; color: #202223; background: #ffffff; box-shadow: none; box-sizing: border-box; outline: none;" required placeholder="e.g. Alex Carter" />
							</div>

							<div class="clicksync-help-input-group">
								<span style="display: block; font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 6px;"><?php esc_html_e( 'Business Email Address:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<input type="email" name="contact_email" style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #e1e3e5; border-radius: 6px; font-size: 13px; color: #202223; background: #ffffff; box-shadow: none; box-sizing: border-box; outline: none;" required placeholder="e.g. alex@company.com" />
							</div>

							<div class="clicksync-help-input-group">
								<span style="display: block; font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 6px;"><?php esc_html_e( 'How can we help?', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<select name="contact_subject" style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #e1e3e5; border-radius: 6px; font-size: 13px; color: #202223; background: #ffffff; box-shadow: none; box-sizing: border-box; outline: none; display: block;" required>
									<option value="Technical Support"><?php esc_html_e( 'Technical Support', 'swiftsync-connect-clickup-with-woocommerce' ); ?></option>
									<option value="Bespoke Development"><?php esc_html_e( 'Bespoke Development', 'swiftsync-connect-clickup-with-woocommerce' ); ?></option>
									<option value="Billing"><?php esc_html_e( 'Billing Inquiry', 'swiftsync-connect-clickup-with-woocommerce' ); ?></option>
									<option value="General Feedback"><?php esc_html_e( 'General Feedback', 'swiftsync-connect-clickup-with-woocommerce' ); ?></option>
								</select>
							</div>

							<div class="clicksync-help-input-group">
								<span style="display: block; font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 6px;"><?php esc_html_e( 'Detailed Inquiry:', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
								<textarea name="contact_message" style="width: 100%; height: 120px; padding: 10px 12px; border: 1px solid #e1e3e5; border-radius: 6px; font-size: 13px; color: #202223; background: #ffffff; box-shadow: none; box-sizing: border-box; outline: none; line-height: 1.5; resize: vertical;" required placeholder="<?php esc_attr_e( 'Describe your requirements or any error details you encountered...', 'swiftsync-connect-clickup-with-woocommerce' ); ?>"></textarea>
							</div>

							<button type="submit" class="clicksync-btn-primary" style="width: 100%; height: 38px; font-weight: 600; background: #008060; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: background 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-top: 4px;">
								<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
								<span><?php esc_html_e( 'Submit Message', 'swiftsync-connect-clickup-with-woocommerce' ); ?></span>
							</button>
						</form>
					</div>
			</div>
		</div>

			<div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 24px; text-align: center; font-size: 13px; color: #64748b;">
				<p style="margin: 0 0 12px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
					A <a href="https://loopstates.com" target="_blank" style="color: inherit; text-decoration: none; font-weight: inherit;">Loopstates</a> Product.
				</p>
				<div style="margin-top: 12px; display: flex; justify-content: center;">
					<a href="https://loopstates.com" target="_blank" style="display: inline-block;">
						<img src="<?php echo esc_url( CLICKSYNC_URL . 'assets/images/loopstates.png' ); ?>" alt="Loopstates" style="height: 24px; width: auto; display: block; margin: 0 auto;" />
					</a>
				</div>
			</div>

		</div>
		<?php
	}
}
