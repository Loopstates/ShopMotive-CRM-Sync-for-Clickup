<?php

namespace ClickSync\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Widget {

	/**
	 * Render WooCommerce Edit Order ClickSync Meta Box.
	 *
	 * @param \WP_Post|\WC_Order $post Post or Order object.
	 */
	public static function render_order_metabox( $post ) {
		$order_id = $post instanceof \WC_Order ? $post->get_id() : $post->ID;
		$task_id   = get_post_meta( $order_id, '_clicksync_task_id', true );
		$task_url  = get_post_meta( $order_id, '_clicksync_task_url', true );
		$last_sync = get_post_meta( $order_id, '_clicksync_last_sync', true );
		
		$last_sync_formatted = $last_sync ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ) : __( 'Never', 'clicksync-wordpress' );
		?>
		<div class="clicksync-widget-wrapper" data-order-id="<?php echo esc_attr( $order_id ); ?>" data-task-id="<?php echo esc_attr( $task_id ); ?>" style="font-size: 13px; color: #475569; line-height: 1.5;">
			<?php if ( empty( $task_id ) ) : ?>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 12px; text-align: center;">
					<svg style="width: 24px; height: 24px; fill: #64748b; margin-bottom: 6px;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
					<p style="margin: 0; font-weight: 500;"><?php esc_html_e( 'Order not synchronized to ClickUp.', 'clicksync-wordpress' ); ?></p>
				</div>
				<button type="button" class="clicksync-manual-sync-btn button button-primary button-large" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; background: #008060; border-color: #006e52; color: #ffffff; text-shadow: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-weight: 600; height: 36px; line-height: 34px;">
					<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
					<span><?php esc_html_e( 'Sync to ClickUp Now', 'clicksync-wordpress' ); ?></span>
				</button>
			<?php else : ?>
				<div style="margin-bottom: 12px;">
					<a href="<?php echo esc_url( $task_url ); ?>" target="_blank" style="font-weight: 700; text-decoration: none; color: #008060; display: inline-flex; align-items: center; gap: 4px;">
						<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
						<span><?php esc_html_e( 'Open ClickUp Task', 'clicksync-wordpress' ); ?></span>
					</a>
					<span style="display: block; font-size: 11px; color: #64748b; margin-top: 4px;">
						<?php printf( esc_html__( 'Last Sync: %s', 'clicksync-wordpress' ), $last_sync_formatted ); ?>
					</span>
				</div>
				
				<!-- Status Select -->
				<div style="margin-bottom: 10px;">
					<label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; color: #475569;"><?php esc_html_e( 'ClickUp Status:', 'clicksync-wordpress' ); ?></label>
					<select class="clicksync-widget-status-select clicksync-select" style="width: 100%;">
						<option value=""><?php esc_html_e( 'Loading task statuses...', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>

				<!-- Priority Select -->
				<div style="margin-bottom: 10px;">
					<label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; color: #475569;"><?php esc_html_e( 'ClickUp Priority:', 'clicksync-wordpress' ); ?></label>
					<select class="clicksync-widget-priority-select clicksync-select" style="width: 100%;">
						<option value=""><?php esc_html_e( 'Loading priorities...', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>

				<!-- Assignee List Checkboxes -->
				<div style="margin-bottom: 14px;">
					<label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; color: #475569;"><?php esc_html_e( 'ClickUp Assignees:', 'clicksync-wordpress' ); ?></label>
					<div class="clicksync-widget-assignees-list" style="background: #fafafa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; max-height: 120px; overflow-y: auto;">
						<span style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Loading members...', 'clicksync-wordpress' ); ?></span>
					</div>
				</div>

				<button type="button" class="clicksync-manual-sync-btn button button-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; height: 36px; line-height: 34px; font-weight: 600;">
					<svg style="width: 13px; height: 13px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
					<span><?php esc_html_e( 'Force Sync Now', 'clicksync-wordpress' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render WooCommerce Edit User (Customer) ClickSync Meta Box.
	 *
	 * @param \WP_User $user User object.
	 */
	public static function render_customer_metabox( $user ) {
		$customer_id = $user->ID;
		$task_id   = get_user_meta( $customer_id, '_clicksync_task_id', true );
		$task_url  = get_user_meta( $customer_id, '_clicksync_task_url', true );
		$last_sync = get_user_meta( $customer_id, '_clicksync_last_sync', true );
		
		$last_sync_formatted = $last_sync ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ) : __( 'Never', 'clicksync-wordpress' );

		// Fetch WooCommerce Customer Lifetime metrics
		$total_spent = 0;
		$order_count = 0;
		if ( function_exists( 'wc_get_customer_total_spent' ) ) {
			$total_spent = wc_get_customer_total_spent( $customer_id );
		}
		if ( function_exists( 'wc_get_customer_order_count' ) ) {
			$order_count = wc_get_customer_order_count( $customer_id );
		}

		// Fetch 3 most recent orders
		$recent_orders = array();
		if ( function_exists( 'wc_get_orders' ) ) {
			$recent_orders = wc_get_orders( array(
				'customer_id' => $customer_id,
				'limit'       => 3,
				'orderby'     => 'date',
				'order'       => 'DESC',
			) );
		}
		?>
		<div class="clicksync-widget-wrapper" data-customer-id="<?php echo esc_attr( $customer_id ); ?>" data-task-id="<?php echo esc_attr( $task_id ); ?>" style="font-size: 13px; color: #475569; line-height: 1.5; max-width: 320px;">
			
			<!-- WooCommerce Customer Stats Section -->
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 12px; font-size: 12px;">
				<div style="margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
					<span style="font-weight: 600; font-size: 11px; text-transform: uppercase; color: #64748b;"><?php esc_html_e( 'Customer Summary', 'clicksync-wordpress' ); ?></span>
				</div>
				<div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
					<span style="color: #64748b;"><?php esc_html_e( 'Total Spend:', 'clicksync-wordpress' ); ?></span>
					<span style="font-weight: 700; color: #1e293b;"><?php echo function_exists( 'wc_price' ) ? wc_price( $total_spent ) : '$' . number_format( $total_spent, 2 ); ?></span>
				</div>
				<div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
					<span style="color: #64748b;"><?php esc_html_e( 'Total Orders:', 'clicksync-wordpress' ); ?></span>
					<span style="font-weight: 700; color: #1e293b;"><?php echo esc_html( $order_count ); ?></span>
				</div>
				
				<?php if ( ! empty( $recent_orders ) ) : ?>
					<div style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
						<span style="font-weight: 600; font-size: 10px; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Recent Orders', 'clicksync-wordpress' ); ?></span>
						<ul style="margin: 0; padding: 0; list-style: none;">
							<?php foreach ( $recent_orders as $o ) : ?>
								<li style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;">
									<a href="<?php echo esc_url( get_edit_post_link( $o->get_id() ) ); ?>" style="text-decoration: none; color: #008060; font-weight: 500;">
										#<?php echo esc_html( $o->get_order_number() ); ?>
									</a>
									<span style="color: #64748b;"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $o->get_date_created()->getTimestamp() ) ); ?></span>
									<span style="font-weight: 600; color: #1e293b;"><?php echo function_exists( 'wc_price' ) ? wc_price( $o->get_total() ) : '$' . number_format( $o->get_total(), 2 ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( empty( $task_id ) ) : ?>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 12px; text-align: center;">
					<svg style="width: 24px; height: 24px; fill: #64748b; margin-bottom: 6px;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
					<p style="margin: 0; font-weight: 500;"><?php esc_html_e( 'Customer not synchronized to ClickUp.', 'clicksync-wordpress' ); ?></p>
				</div>
				<button type="button" class="clicksync-manual-sync-btn button button-primary button-large" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; background: #008060; border-color: #006e52; color: #ffffff; text-shadow: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-weight: 600; height: 36px; line-height: 34px;">
					<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
					<span><?php esc_html_e( 'Sync to ClickUp Now', 'clicksync-wordpress' ); ?></span>
				</button>
			<?php else : ?>
				<div style="margin-bottom: 12px;">
					<a href="<?php echo esc_url( $task_url ); ?>" target="_blank" style="font-weight: 700; text-decoration: none; color: #008060; display: inline-flex; align-items: center; gap: 4px;">
						<svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
						<span><?php esc_html_e( 'Open ClickUp Task', 'clicksync-wordpress' ); ?></span>
					</a>
					<span style="display: block; font-size: 11px; color: #64748b; margin-top: 4px;">
						<?php printf( esc_html__( 'Last Sync: %s', 'clicksync-wordpress' ), $last_sync_formatted ); ?>
					</span>
				</div>
				
				<!-- Status Select -->
				<div style="margin-bottom: 10px;">
					<label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; color: #475569;"><?php esc_html_e( 'ClickUp Status:', 'clicksync-wordpress' ); ?></label>
					<select class="clicksync-widget-status-select clicksync-select" style="width: 100%;">
						<option value=""><?php esc_html_e( 'Loading task statuses...', 'clicksync-wordpress' ); ?></option>
					</select>
				</div>

				<button type="button" class="clicksync-manual-sync-btn button button-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; height: 36px; line-height: 34px; font-weight: 600;">
					<svg style="width: 13px; height: 13px; fill: currentColor;" viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
					<span><?php esc_html_e( 'Force Sync Now', 'clicksync-wordpress' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}
}
