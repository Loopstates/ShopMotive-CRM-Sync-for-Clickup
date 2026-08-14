<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ErrorsPage
 * 
 * Renders the dedicated Sync Error Center matching Shopify ClickSync (/app.errors).
 */
class ErrorsPage {

	/**
	 * Render the full error center page HTML.
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
							<svg class="clicksync-title-icon" viewBox="0 0 24 24" style="fill: #ef4444; width: 22px; height: 22px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg> <?php esc_html_e( 'Sync Error Center', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Diagnostic trace logs and retry controls for any failed API requests or rate-limited ClickUp calls.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Status Banner -->
			<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; margin-bottom: 20px; color: #991b1b; display: flex; align-items: flex-start; gap: 12px;">
				<svg class="clicksync-title-icon" viewBox="0 0 24 24" style="width: 24px; height: 24px; fill: #ef4444; margin-top: 2px;"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
				<div>
					<strong style="font-size: 14px; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Automatic Fail-Safe Architecture', 'clicksync-wordpress' ); ?></strong>
					<span style="font-size: 13px; color: #b91c1c; line-height: 1.5; display: block;">
						<?php esc_html_e( 'If ClickUp rate limits (e.g. 100 calls/min) temporarily block an event, ClickSync automatically queues and retries execution in the background. Your WooCommerce checkout flow is never blocked.', 'clicksync-wordpress' ); ?>
					</span>
				</div>
			</div>

			<!-- Error Logs Table -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #0f172a;">
					<?php esc_html_e( 'Failed Event Traces & Retries', 'clicksync-wordpress' ); ?>
				</h3>

				<table style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr style="background: #f8fafc; text-align: left; font-size: 12px; color: #64748b;">
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Failed Event', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Error Traceback', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0; text-align: right;"><?php esc_html_e( 'Action', 'clicksync-wordpress' ); ?></th>
						</tr>
					</thead>
					<tbody id="sync-errors-tbody">
						<tr>
							<td colSpan="3" style="padding: 30px; text-align: center; color: #64748b;">
								<?php esc_html_e( 'No error traces found. All sync events are running cleanly!', 'clicksync-wordpress' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
		<?php
	}
}
