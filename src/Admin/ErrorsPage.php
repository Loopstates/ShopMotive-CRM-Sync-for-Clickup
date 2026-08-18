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
		<div class="wrap clicksync-wrap" style="max-width: 1050px; margin: 20px auto;">
			
			<!-- Page Header -->
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="ClickSync" style="width: 42px; height: 42px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);" />
					<div>
						<h1 style="font-weight: 700; font-size: 24px; margin: 0; color: #0f172a; line-height: 1.2;">
							<?php esc_html_e( 'Sync Error Center', 'clicksync-connect' ); ?>
						</h1>
						<p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Diagnostic trace logs and retry controls for any failed API requests or rate-limited ClickUp calls.', 'clicksync-connect' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Filter & Action Controls -->
			<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px 24px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div style="display: flex; gap: 12px; flex: 1; min-width: 280px;">
					<input type="text" id="errors-search" class="clicksync-select" style="margin: 0; flex: 1;" placeholder="<?php esc_attr_e( 'Search by event or error description...', 'clicksync-connect' ); ?>" />
				</div>
				<div>
					<button type="button" class="clicksync-btn-secondary" style="height: 36px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;" id="btn-export-errors">
						<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg> <?php esc_html_e( 'Export CSV', 'clicksync-connect' ); ?>
					</button>
				</div>
			</div>

			<!-- Retry & Deletion Info Slim Banner -->
			<div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 6px; padding: 10px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 12px; color: #991b1b; line-height: 1.4;">
				<svg class="clicksync-title-icon" viewBox="0 0 24 24" style="width: 14px; height: 14px; fill: #ef4444; flex-shrink: 0; margin: 0;"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
				<span>
					<strong><?php esc_html_e( 'Sync Retries & Retention Policy:', 'clicksync-connect' ); ?></strong>
					<?php esc_html_e( 'Failed syncs are automatically retried 3 times in the background. If they still fail, they appear here. Error logs are kept for 7 days for manual retry before automatic deletion.', 'clicksync-connect' ); ?>
				</span>
			</div>

			<!-- Error Logs Table -->
			<div class="clicksync-card-table" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px;">
				<div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #ffffff;">
					<h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
						<?php esc_html_e( 'Failed Event Traces & Retries', 'clicksync-connect' ); ?>
					</h3>
				</div>

				<table class="wp-list-table widefat fixed striped" style="width: 100%; border: none; margin: 0; border-collapse: collapse; box-shadow: none;">
					<thead>
						<tr>
							<th style="width: 40%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc;"><?php esc_html_e( 'Failed Event', 'clicksync-connect' ); ?></th>
							<th style="width: 45%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc;"><?php esc_html_e( 'Error Traceback', 'clicksync-connect' ); ?></th>
							<th style="width: 15%; padding: 12px 24px; font-weight: 600; font-size: 13px; color: #475569; border-bottom: 1px solid #cbd5e1; background: #f8fafc; text-align: right;"><?php esc_html_e( 'Action', 'clicksync-connect' ); ?></th>
						</tr>
					</thead>
					<tbody id="sync-errors-tbody">
						<tr>
							<td colSpan="3" style="padding: 30px 24px; text-align: center; color: #64748b;">
								<?php esc_html_e( 'No error traces found. All sync events are running cleanly!', 'clicksync-connect' ); ?>
							</td>
						</tr>
					</tbody>
				</table>

				<!-- Pagination controls -->
				<div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding: 16px 24px; background: #ffffff;">
					<div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: #64748b; white-space: nowrap;">
						<span><?php esc_html_e( 'Show', 'clicksync-connect' ); ?></span>
						<select id="errors-per-page" class="clicksync-select" style="width: 70px; height: 32px; padding: 4px; margin: 0 4px; font-size: 12px; display: inline-block; vertical-align: middle;">
							<option value="10">10</option>
							<option value="25" selected>25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						<span><?php esc_html_e( 'per page', 'clicksync-connect' ); ?></span>
					</div>
					<div style="display: flex; align-items: center; gap: 6px;" id="errors-pagination-controls"></div>
				</div>
			</div>

		</div>
		<?php
	}
}
