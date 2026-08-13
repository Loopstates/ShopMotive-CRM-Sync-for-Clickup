<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LogsPage
 * 
 * Renders the dedicated Sync Audit Logs inspector page matching Shopify ClickSync (/app.logs).
 */
class LogsPage {

	/**
	 * Render the full logs page HTML.
	 */
	public static function render() {
		$account  = Options::get_account();
		$logo_url = CLICKSYNC_URL . 'assets/images/logo.png';
		?>
		<div class="wrap clicksync-wrap" style="max-width: 1100px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, 'San Francisco', 'Segoe UI', Roboto, sans-serif;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							📊 <?php esc_html_e( 'Sync Audit Logs', 'clicksync-wordpress' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Real-time telemetry and audit trail for all events dispatched from WooCommerce to ClickUp.', 'clicksync-wordpress' ); ?>
						</p>
					</div>
				</div>
				<div>
					<button type="button" id="clicksync-refresh-logs" class="button button-secondary" style="height: 36px; font-weight: 600;">
						🔄 <?php esc_html_e( 'Refresh Logs', 'clicksync-wordpress' ); ?>
					</button>
				</div>
			</div>

			<!-- Filter & Action Controls -->
			<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
				<div style="display: flex; gap: 12px; flex: 1; min-width: 280px;">
					<input type="text" id="logs-search" class="clicksync-select" style="margin: 0; flex: 1;" placeholder="<?php esc_attr_e( 'Search by order #, event, or task ID...', 'clicksync-wordpress' ); ?>" />
					<select id="logs-status-filter" class="clicksync-select" style="margin: 0; width: 160px;">
						<option value=""><?php esc_html_e( 'All Statuses', 'clicksync-wordpress' ); ?></option>
						<option value="Success"><?php esc_html_e( 'Success Only', 'clicksync-wordpress' ); ?></option>
						<option value="Failure"><?php esc_html_e( 'Failure Only', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>
				<div>
					<button type="button" class="button" style="height: 36px;" id="btn-export-logs">📥 <?php esc_html_e( 'Export CSV', 'clicksync-wordpress' ); ?></button>
				</div>
			</div>

			<!-- Logs Table -->
			<div class="clicksync-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<table style="width: 100%; border-collapse: collapse;">
					<thead>
						<tr style="background: #f8fafc; text-align: left; font-size: 12px; color: #64748b;">
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Event & Entity', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'Execution Status', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0;"><?php esc_html_e( 'ClickUp Task Link', 'clicksync-wordpress' ); ?></th>
							<th style="padding: 12px; border-bottom: 2px solid #e2e8f0; text-align: right;"><?php esc_html_e( 'Timestamp', 'clicksync-wordpress' ); ?></th>
						</tr>
					</thead>
					<tbody id="full-sync-logs-tbody">
						<tr>
							<td colSpan="4" style="padding: 30px; text-align: center; color: #64748b;">
								<?php esc_html_e( 'Loading audit log history...', 'clicksync-wordpress' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
		<?php
	}
}
