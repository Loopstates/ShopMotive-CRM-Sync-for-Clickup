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
		<div class="wrap clicksync-wrap" style="max-width: 1050px; margin: 20px auto;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="SwiftSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'Sync Logs', 'swiftsync-crm-sync-for-clickup' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Real-time telemetry and logs for all events dispatched from WooCommerce to ClickUp.', 'swiftsync-crm-sync-for-clickup' ); ?>
						</p>
					</div>
				</div>
				<div>
					<button type="button" id="clicksync-refresh-logs" class="clicksync-btn-secondary" style="height: 36px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
						<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg> <?php esc_html_e( 'Refresh Logs', 'swiftsync-crm-sync-for-clickup' ); ?>
					</button>
				</div>
			</div>

			<!-- Filter & Action Controls -->
			<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px 24px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div style="display: flex; gap: 12px; flex: 1; min-width: 280px;">
					<input type="text" id="logs-search" class="clicksync-select" style="margin: 0; flex: 1;" placeholder="<?php esc_attr_e( 'Search by order #, event, or task ID...', 'swiftsync-crm-sync-for-clickup' ); ?>" />
					<select id="logs-status-filter" class="clicksync-select" style="margin: 0; width: 160px;">
						<option value=""><?php esc_html_e( 'All Statuses', 'swiftsync-crm-sync-for-clickup' ); ?></option>
						<option value="Success"><?php esc_html_e( 'Success Only', 'swiftsync-crm-sync-for-clickup' ); ?></option>
						<option value="Failure"><?php esc_html_e( 'Failure Only', 'swiftsync-crm-sync-for-clickup' ); ?></option>
					</select>
				</div>
				<div>
					<button type="button" class="clicksync-btn-secondary" style="height: 36px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;" id="btn-export-logs">
						<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg> <?php esc_html_e( 'Export CSV', 'swiftsync-crm-sync-for-clickup' ); ?>
					</button>
				</div>
			</div>

			<!-- Logs Table -->
			<div class="clicksync-card-table" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px;">
				<table class="wp-list-table widefat fixed striped" style="width: 100%; border: none; margin: 0; border-collapse: collapse; box-shadow: none;">
					<thead>
						<tr>
							<th style="width: 45%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc;"><?php esc_html_e( 'Event & Entity', 'swiftsync-crm-sync-for-clickup' ); ?></th>
							<th style="width: 15%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc;"><?php esc_html_e( 'Execution Status', 'swiftsync-crm-sync-for-clickup' ); ?></th>
							<th style="width: 20%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc;"><?php esc_html_e( 'ClickUp Task Link', 'swiftsync-crm-sync-for-clickup' ); ?></th>
							<th style="width: 20%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc; text-align: right;"><?php esc_html_e( 'Timestamp', 'swiftsync-crm-sync-for-clickup' ); ?></th>
						</tr>
					</thead>
					<tbody id="full-sync-logs-tbody">
						<tr>
							<td colSpan="4" style="padding: 30px 24px; text-align: center; color: #64748b;">
								<?php esc_html_e( 'Loading sync log history...', 'swiftsync-crm-sync-for-clickup' ); ?>
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Pagination controls -->
				<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding: 16px 24px; background: #ffffff;">
					<div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: #64748b; white-space: nowrap;">
						<span><?php esc_html_e( 'Show', 'swiftsync-crm-sync-for-clickup' ); ?></span>
						<select id="logs-per-page" class="clicksync-select" style="width: 70px; height: 32px; padding: 4px; margin: 0 4px; font-size: 12px; display: inline-block; vertical-align: middle;">
							<option value="10">10</option>
							<option value="25" selected>25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						<span><?php esc_html_e( 'per page', 'swiftsync-crm-sync-for-clickup' ); ?></span>
					</div>
					<div style="display: flex; align-items: center; gap: 6px;" id="logs-pagination-controls"></div>
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
