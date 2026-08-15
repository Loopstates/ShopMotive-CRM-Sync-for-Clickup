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
		?>
		<style>
			.clicksync-help-container {
				max-width: 1100px;
				margin: 20px auto;
				font-family: 'Inter', sans-serif;
			}
			.clicksync-grid {
				display: grid;
				grid-template-columns: 2fr 1fr;
				gap: 24px;
				margin-top: 20px;
			}
			@media (max-width: 900px) {
				.clicksync-grid {
					grid-template-columns: 1fr;
				}
			}
			.clicksync-help-section {
				background: #ffffff;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				padding: 24px;
				margin-bottom: 24px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.02);
			}
			.clicksync-help-section h3 {
				margin-top: 0;
				margin-bottom: 16px;
				font-size: 18px;
				font-weight: 700;
				color: #0f172a;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.clicksync-help-section h3 svg {
				width: 20px;
				height: 20px;
				fill: #7c3aed;
			}
			.clicksync-step-list {
				list-style: none;
				padding-left: 0;
				margin: 0;
			}
			.clicksync-step-item {
				position: relative;
				padding-left: 36px;
				margin-bottom: 20px;
			}
			.clicksync-step-number {
				position: absolute;
				left: 0;
				top: 2px;
				width: 24px;
				height: 24px;
				background: #f3e8ff;
				color: #7c3aed;
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				font-weight: 700;
				font-size: 12px;
			}
			.clicksync-step-title {
				font-weight: 600;
				font-size: 15px;
				color: #1e293b;
				margin-bottom: 4px;
			}
			.clicksync-step-desc {
				font-size: 13px;
				color: #64748b;
				line-height: 1.6;
			}
			.clicksync-badge-pill {
				display: inline-block;
				padding: 2px 8px;
				border-radius: 4px;
				font-size: 11px;
				font-weight: 600;
				background: #f1f5f9;
				color: #475569;
				margin-left: 6px;
			}
			.clicksync-support-card {
				background: #ffffff;
				border: 1px solid #cbd5e1;
				border-radius: 12px;
				padding: 24px;
				box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
				position: sticky;
				top: 20px;
			}
			.clicksync-input-group {
				margin-bottom: 16px;
			}
			.clicksync-input-group label {
				display: block;
				font-size: 12px;
				font-weight: 600;
				color: #475569;
				margin-bottom: 6px;
			}
			.clicksync-input-field {
				width: 100%;
				height: 40px;
				padding: 8px 12px;
				border: 1px solid #cbd5e1;
				border-radius: 6px;
				font-size: 13px;
				background: #ffffff;
				color: #1e293b;
				outline: none;
				box-sizing: border-box;
				transition: all 0.2s ease;
			}
			.clicksync-input-field:focus {
				border-color: #7c3aed;
				box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
			}
			.clicksync-textarea-field {
				width: 100%;
				height: 120px;
				padding: 10px 12px;
				border: 1px solid #cbd5e1;
				border-radius: 6px;
				font-size: 13px;
				background: #ffffff;
				color: #1e293b;
				outline: none;
				resize: vertical;
				box-sizing: border-box;
				transition: all 0.2s ease;
			}
			.clicksync-textarea-field:focus {
				border-color: #7c3aed;
				box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
			}
			.clicksync-faq-item {
				border-bottom: 1px solid #f1f5f9;
				padding: 16px 0;
			}
			.clicksync-faq-item:last-child {
				border-bottom: none;
			}
			.clicksync-faq-q {
				font-weight: 600;
				font-size: 14px;
				color: #0f172a;
				margin-bottom: 6px;
				display: flex;
				align-items: center;
				gap: 6px;
			}
			.clicksync-faq-q::before {
				content: 'Q:';
				color: #7c3aed;
				font-weight: 700;
			}
			.clicksync-faq-a {
				font-size: 13px;
				color: #64748b;
				line-height: 1.6;
				padding-left: 22px;
			}
		</style>

		<div class="wrap clicksync-help-container">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'Help Center & Documentation', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Learn how to configure workflows, map custom fields, resolve sync errors, and get direct assistance.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="clicksync-grid">
				
				<!-- Left Column: Comprehensive Guides -->
				<div>
					
					<!-- 1. Connection & Initial Setup Guide -->
					<div class="clicksync-help-section">
						<h3>
							<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
							<?php esc_html_e( '1. Connection & Setup Walkthrough', 'clicksync-wordpress' ); ?>
						</h3>
						<ul class="clicksync-step-list">
							<li class="clicksync-step-item">
								<span class="clicksync-step-number">1</span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Authorize ClickUp Workspace', 'clicksync-wordpress' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'Click the "Connect ClickUp Workspace" button on the Settings page. This redirects you to ClickUp\'s OAuth authorization screen. Select the workspaces you want to sync, and authorize permissions. Upon completion, a secure signed connection is established.', 'clicksync-wordpress' ); ?>
								</div>
							</li>
							<li class="clicksync-step-item">
								<span class="clicksync-step-number">2</span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Select Target Workspace Lists', 'clicksync-wordpress' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'ClickSync creates tasks within specific ClickUp lists. Select distinct lists for your Orders, Customers, Refunds, and Abandoned Checkouts. If a list does not exist yet, create it in your ClickUp workspace first, then refresh settings.', 'clicksync-wordpress' ); ?>
								</div>
							</li>
							<li class="clicksync-step-item">
								<span class="clicksync-step-number">3</span>
								<div class="clicksync-step-title"><?php esc_html_e( 'Validate Webhook Registration', 'clicksync-wordpress' ); ?></div>
								<div class="clicksync-step-desc">
									<?php esc_html_e( 'Once saved, ClickSync registers real-time webhook listeners with ClickUp. These webhooks handle bi-directional status updates and comments. Verify your status indicators show "Connected" to ensure communication is open.', 'clicksync-wordpress' ); ?>
								</div>
							</li>
						</ul>
					</div>

					<!-- 2. Status Mapping & Fulfillment Sync -->
					<div class="clicksync-help-section">
						<h3>
							<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
							<?php esc_html_e( '2. WooCommerce Status & Fulfillment Mapping', 'clicksync-wordpress' ); ?>
						</h3>
						<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 16px;">
							<?php esc_html_e( 'Workflows are fully bi-directional. When a WooCommerce order state changes, ClickUp task statuses are updated automatically. When team members drag-and-drop cards inside ClickUp, the WooCommerce order updates accordingly.', 'clicksync-wordpress' ); ?>
						</p>
						<ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.8;">
							<li><strong><?php esc_html_e( 'Custom Status Actions:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'Under Settings -> Order Status Mapping Rules, map WooCommerce order statuses (e.g. processing, completed, on-hold) to your exact ClickUp list statuses.', 'clicksync-wordpress' ); ?></li>
							<li><strong><?php esc_html_e( 'Fulfillment & Shipped Sync:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'Enable "Sync Fulfillment Statuses" to push tracking number details and automatically complete orders when tasks are marked as completed or shipped inside ClickUp.', 'clicksync-wordpress' ); ?></li>
							<li><strong><?php esc_html_e( 'Refunds Safeguard:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'Turning on "Sync Refund Updates" ensures that any returns, partial refunds, or restocks update task priority and append logs dynamically.', 'clicksync-wordpress' ); ?></li>
						</ul>
					</div>

					<!-- 3. Advanced Custom Field Mapping Recipe -->
					<div class="clicksync-help-section">
						<h3>
							<svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
							<?php esc_html_e( '3. Advanced Custom Field Routing Recipes', 'clicksync-wordpress' ); ?>
						</h3>
						<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 12px;">
							<?php esc_html_e( 'Map any core WooCommerce data or custom meta keys into your ClickUp custom fields. Ensure you choose the correct target field types inside ClickUp first:', 'clicksync-wordpress' ); ?>
						</p>
						<table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 12px;">
							<thead>
								<tr style="background: #f8fafc; text-align: left;">
									<th style="padding: 8px 12px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569;"><?php esc_html_e( 'WooCommerce Field Key', 'clicksync-wordpress' ); ?></th>
									<th style="padding: 8px 12px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569;"><?php esc_html_e( 'Recommended ClickUp Type', 'clicksync-wordpress' ); ?></th>
									<th style="padding: 8px 12px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569;"><?php esc_html_e( 'Description', 'clicksync-wordpress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-family: monospace;">total_price</td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge-pill">Number / Currency</span></td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php esc_html_e( 'The gross order total amount.', 'clicksync-wordpress' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-family: monospace;">customer.email</td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge-pill">Email</span></td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php esc_html_e( 'Merchant email to query customers.', 'clicksync-wordpress' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-family: monospace;">payment_method_title</td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge-pill">Short Text</span></td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php esc_html_e( 'Checkout provider used (e.g. Stripe, PayPal).', 'clicksync-wordpress' ); ?></td>
								</tr>
								<tr>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-family: monospace;">_billing_phone</td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0;"><span class="clicksync-badge-pill">Phone</span></td>
									<td style="padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php esc_html_e( 'WooCommerce billing telephone meta data.', 'clicksync-wordpress' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- 4. Comments & User Mappings -->
					<div class="clicksync-help-section">
						<h3>
							<svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
							<?php esc_html_e( '4. Real-time Bi-directional Comments & Users Mapping', 'clicksync-wordpress' ); ?>
						</h3>
						<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 12px;">
							<?php esc_html_e( 'To preserve comment audit trails in ClickUp, ClickSync automatically handles identity resolution:', 'clicksync-wordpress' ); ?>
						</p>
						<ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.8;">
							<li><strong><?php esc_html_e( 'Identity Association:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'Associate your WordPress administrators/shop managers with their ClickUp workspace accounts under the "User Identity Mappings" panel.', 'clicksync-wordpress' ); ?></li>
							<li><strong><?php esc_html_e( 'Comment Authorship:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'Adding order notes or completion comments in WooCommerce attributes the action to the mapped workspace member inside ClickUp. External API updates use your configured fallback user.', 'clicksync-wordpress' ); ?></li>
							<li><strong><?php esc_html_e( 'Feedback Loop Lock:', 'clicksync-wordpress' ); ?></strong> <?php esc_html_e( 'ClickSync includes smart filters to prevent echo feedback loops. Internal metadata keeps WooCommerce notes and ClickUp comment responses from repeating endlessly.', 'clicksync-wordpress' ); ?></li>
						</ol>
					</div>

					<!-- 5. Frequently Asked Questions -->
					<div class="clicksync-help-section">
						<h3>
							<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
							<?php esc_html_e( 'Frequently Asked Questions', 'clicksync-wordpress' ); ?>
						</h3>
						<div class="clicksync-faq-item">
							<div class="clicksync-faq-q"><?php esc_html_e( 'Will ClickSync slow down my customer checkout times?', 'clicksync-wordpress' ); ?></div>
							<div class="clicksync-faq-a"><?php esc_html_e( 'No. ClickSync works 100% asynchronously. The moment checkouts finish, the sync event payload is handed off to our background cloud processor in 0ms, leaving your customer loading speed completely unaffected.', 'clicksync-wordpress' ); ?></div>
						</div>
						<div class="clicksync-faq-item">
							<div class="clicksync-faq-q"><?php esc_html_e( 'What happens if a rate limit or server error blocks ClickUp?', 'clicksync-wordpress' ); ?></div>
							<div class="clicksync-faq-a"><?php esc_html_e( 'Events are ingested into our fail-safe cloud database queue. If ClickUp is offline or rate limits your account, the transaction retries automatically in back-off intervals until task creation succeeds.', 'clicksync-wordpress' ); ?></div>
						</div>
						<div class="clicksync-faq-item">
							<div class="clicksync-faq-q"><?php esc_html_e( 'How do I force manual sync if order parameters change?', 'clicksync-wordpress' ); ?></div>
							<div class="clicksync-faq-a"><?php esc_html_e( 'Go to any WooCommerce order page. The ClickSync Meta Box widget features a "Sync Now" control. Clicking this runs a synchronous manual sync pipeline instantly, pulling updated details into WooCommerce.', 'clicksync-wordpress' ); ?></div>
						</div>
					</div>

				</div>

				<!-- Right Column: Support Ticket Form -->
				<div>
					<div class="clicksync-support-card">
						<h3 style="margin-top: 0; margin-bottom: 8px; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
							<svg style="width: 18px; height: 18px; fill: #7c3aed;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 9h-2V5h2v6zm0 4h-2v-2h2v2z"/></svg>
							<?php esc_html_e( 'Contact Support & Bespoke Requests', 'clicksync-wordpress' ); ?>
						</h3>
						<p style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 20px;">
							<?php esc_html_e( 'Need help setting up mappings, running into bugs, or require customized custom-coded rules? Send us a message, and our engineers will respond directly.', 'clicksync-wordpress' ); ?>
						</p>

						<form id="clicksync-support-contact-form">
							<div class="clicksync-input-group">
								<label><?php esc_html_e( 'Your Name:', 'clicksync-wordpress' ); ?></label>
								<input type="text" name="contact_name" class="clicksync-input-field" required placeholder="e.g. Alex Carter" />
							</div>

							<div class="clicksync-input-group">
								<label><?php esc_html_e( 'Business Email Address:', 'clicksync-wordpress' ); ?></label>
								<input type="email" name="contact_email" class="clicksync-input-field" required placeholder="e.g. alex@company.com" />
							</div>

							<div class="clicksync-input-group">
								<label><?php esc_html_e( 'How can we help?', 'clicksync-wordpress' ); ?></label>
								<select name="contact_subject" class="clicksync-input-field" style="padding-top: 4px; padding-bottom: 4px;" required>
									<option value="Technical Support"><?php esc_html_e( 'Technical Support / Help', 'clicksync-wordpress' ); ?></option>
									<option value="Bespoke Requirement"><?php esc_html_e( 'Bespoke Customization / Custom Rules', 'clicksync-wordpress' ); ?></option>
									<option value="Billing"><?php esc_html_e( 'Billing Inquiry', 'clicksync-wordpress' ); ?></option>
									<option value="General Feedback"><?php esc_html_e( 'General Feedback', 'clicksync-wordpress' ); ?></option>
								</select>
							</div>

							<div class="clicksync-input-group">
								<label><?php esc_html_e( 'Detailed Inquiry:', 'clicksync-wordpress' ); ?></label>
								<textarea name="contact_message" class="clicksync-textarea-field" required placeholder="<?php esc_attr_e( 'Describe your requirements or any error details you encountered...', 'clicksync-wordpress' ); ?>"></textarea>
							</div>

							<button type="submit" class="clicksync-btn-primary" style="width: 100%; height: 42px; font-weight: 700; background: #7c3aed; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; transition: background 0.2s ease;">
								<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
								<span><?php esc_html_e( 'Submit Message', 'clicksync-wordpress' ); ?></span>
							</button>
						</form>
					</div>
				</div>

			</div>

		</div>
		<?php
	}
}
