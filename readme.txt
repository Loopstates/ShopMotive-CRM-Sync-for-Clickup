=== ShopMotive: CRM Sync for ClickUp ===
Contributors: loopstates
Tags: clickup, woocommerce, crm, order sync, customer sync
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect and synchronize WooCommerce order events, customer profiles, notes, and refunds directly into ClickUp tasks.

== Description ==

Built by [Loopstates](https://loopstates.com), ShopMotive connects your WooCommerce store to your ClickUp workspace. It automatically converts WooCommerce orders, customer profiles, refunds, and order notes into ClickUp tasks so your fulfillment, support, and sales teams can manage store operations directly inside ClickUp.

When an order is placed or updated in WooCommerce, ShopMotive captures the event and generates or updates a corresponding task in ClickUp. You can map WooCommerce fields (such as billing details, customer lifetime spend, discount codes, and shipping addresses) to ClickUp custom fields, automatically assign tasks to team members based on order thresholds, and keep order statuses synchronized between both platforms.

Event processing is executed in the background to ensure customer checkouts remain fast and responsive. The plugin connects securely with the [ShopMotive Cloud Service](https://shopmotive.apps.loopstates.com) to process API handshakes and deliver payloads reliably without impacting your website's performance. Full setup guides and tutorials are available on the [ShopMotive Documentation Portal](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/).

== Subscription Plans & Pricing ==

ShopMotive Connect offers flexible plans to scale with your business requirements. For full plan comparisons, pricing tiers, and quota upgrades, visit the [ShopMotive Pricing & Plans](https://shopmotive.apps.loopstates.com/#pricing) page.

* **Free Plan**
    * **Sync Quota:** 100 tasks/month
    * **Store Connections:** Single Store
    * **Sync Direction:** WP -> ClickUp Only (Unidirectional)
    * **Order & Customer Sync:** Yes
    * **WooCommerce Sidebar Widgets:** Yes
    * **WordPress User Mapping:** Yes
    * **Bi-directional Notes/Comments:** WP -> ClickUp Only (Unidirectional)
    * **Bi-directional Status Updates:** WP -> ClickUp Only (Unidirectional)

* **Growth Plan**
    * **Sync Quota:** 1,000 tasks/month
    * **Store Connections:** Single Store
    * **Sync Direction:** Bi-directional
    * **Order & Customer Sync:** Yes
    * **WooCommerce Sidebar Widgets:** Yes
    * **WordPress User Mapping:** Yes
    * **Bi-directional Notes/Comments:** Yes
    * **Bi-directional Status Updates:** Yes
    * **Custom Field Mapping:** Yes
    * **Refund Sync Rules:** Yes

* **Pro Plan**
    * **Sync Quota:** 10,000 tasks/month
    * **Store Connections:** Up to 5 Stores
    * **Sync Direction:** Bi-directional
    * **Order & Customer Sync:** Yes
    * **WooCommerce Sidebar Widgets:** Yes
    * **WordPress User Mapping:** Yes
    * **Bi-directional Notes/Comments:** Yes
    * **Bi-directional Status Updates:** Yes
    * **Custom Field Mapping:** Yes
    * **Refund Sync Rules:** Yes
    * **Line-Item Split Routing:** Yes

== Key Features ==

* **Automated Order-to-Task Conversion:** Instantly creates structured ClickUp task cards the second a customer places an order on WooCommerce, giving your fulfillment team real-time visibility without refreshing WordPress.
* **2-Way Order Status Control:** Move a task card to "Processing", "Shipped", or "Completed" inside ClickUp, and ShopMotive automatically updates the order status in WooCommerce and notifies the customer.
* **360° Customer Lifetime Value & CRM Sync:** Sync buyer profiles, total order history, lifetime spend, billing details, and shipping addresses into custom ClickUp fields for VIP customer management.
* **Smart Team & Priority Routing:** Automatically assign high-value orders (e.g. over $200) to specific team members and apply "Urgent" or "High" priority tags to expedite fulfillment.
* **Bi-Directional Order Notes & Refund Tracking:** Keep your support team and warehouse in sync. Internal WooCommerce order notes and customer refund requests automatically sync to task comment threads in ClickUp.
* **Zero Checkout Speed Impact:** Runs completely in the background without adding a single millisecond of delay to your WooCommerce checkout flow or store loading speeds.
* **Multi-Item Order Split Subtasks:** Optionally break multi-item orders into linked child subtasks in ClickUp so different warehouse departments can fulfill items simultaneously.
* **WP Admin Order Sidebar Widget:** Manage linked ClickUp task IDs, check live status updates, and trigger manual syncs directly from the WooCommerce Edit Order screen.
* **Fail-Safe Automatic Retries:** Retains order dispatches during temporary ClickUp API outages and automatically retries dispatches so no order is ever missed.

== External Services ==

ShopMotive: CRM Sync for ClickUp utilizes external cloud APIs to manage authentication tokens, decrypt keys, and queue requests safely:

* **ShopMotive Cloud Sync Proxy:** [https://shopmotive.apps.loopstates.com](https://shopmotive.apps.loopstates.com)
* **ClickUp REST API Service:** [https://api.clickup.com](https://api.clickup.com)

By activating this connector, e-commerce data (order line items, notes, billing addresses, and customer profiles) is securely sent to the ShopMotive Cloud Service and ClickUp APIs via HTTPS to generate tasks.

* **Documentation & Help:** [ShopMotive Documentation](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/)
* **Pricing & Plans:** [ShopMotive Pricing](https://shopmotive.apps.loopstates.com/#pricing)
* **Privacy Policy:** [ShopMotive Privacy Policy](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/privacy-policy.html)
* **Terms of Service:** [ShopMotive Terms](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/plans.html)
* **ClickUp Terms:** [ClickUp Terms of Service](https://clickup.com/terms)

== Professional Data Safety & Collection Disclosure ==

To guarantee high service reliability, prompt technical support, and prevention of synchronization outages, ShopMotive Connect securely synchronizes specific store administrative metadata with our cloud servers:

* **Site Title (Store Name):** Collected to identify your store configuration in our administrative portal.
* **Administrator Email Address:** Retained to automatically notify you in the event of continuous ClickUp API handshake failures or key expirations.
* **Site Owner / Administrator Name:** Used to personalize merchant assistance and authenticate support request tickets.
* **Integration Telemetry (Sync Counts & Log Stats):** Tracks transaction volumes to monitor subscription quotas and alert you before you reach rate limits.

All administrative metadata is stored in our secure, encrypted cloud database. This data is utilized solely for customer support, service health monitoring, and system security, and is never shared with third parties. Telemetry data is retained for the lifetime of your integration and is permanently deleted within 30 days of subscription cancellation.

== Installation ==

1. Upload the `shopmotive-crm-sync-for-clickup` folder to the `/wp-content/plugins/` directory, or search and install via the WordPress Admin Plugins manager.
2. Activate the plugin.
3. Go to **ShopMotive -> Settings** in your dashboard.
4. Click **Connect ClickUp Workspace** and authorize access via OAuth 2.0.
5. Choose your list destinations, customize sync triggers, and click Save Settings.

== Developer API: Hooks & Filters ==

ShopMotive Connect is built with extensibility at its core. You can use standard WordPress filters to customize sync logic:

= 1. Bypass Syncing for Specific Orders =
`
add_filter( 'shopmotive_should_sync_order', function( $should_sync, $order_id, $order ) {
    // Example: Bypass sync if order total is under $10.00
    if ( $order && $order->get_total() < 10.00 ) {
        return false;
    }
    return $should_sync;
}, 10, 3 );
`

= 2. Customize Order Sync Payloads =
`
add_filter( 'shopmotive_order_payload', function( $payload, $order ) {
    // Example: Append a custom checkout field to order payload
    $payload['meta_data']['custom_gift_wrap_note'] = get_post_meta( $order->get_id(), '_gift_wrap_note', true );
    return $payload;
}, 10, 2 );
`

= 3. Adjust Failed Webhook Retry Attempts =
`
add_filter( 'shopmotive_retry_limit', function( $limit ) {
    return 5; // Increase retry tolerance to 5 attempts
} );
`

= 4. Filter HTTP API Request Arguments =
`
add_filter( 'shopmotive_api_request_headers', function( $headers, $endpoint ) {
    $headers['X-Developer-Partner-Id'] = 'my_partner_key';
    return $headers;
}, 10, 2 );
`

= 5. Bypass Customer Syncing =
`
add_filter( 'shopmotive_should_sync_customer', function( $should_sync, $customer_id, $new_data ) {
    // Example: Bypass sync for test accounts
    if ( isset( $new_data['email'] ) && strpos( $new_data['email'], '@test.com' ) !== false ) {
        return false;
    }
    return $should_sync;
}, 10, 3 );
`

= 6. Customize Customer Payload =
`
add_filter( 'shopmotive_customer_payload', function( $payload, $customer_id ) {
    $payload['custom_user_segment'] = 'VIP';
    return $payload;
}, 10, 2 );
`

= 7. Customize Refund Payload =
`
add_filter( 'shopmotive_refund_payload', function( $payload, $refund_id, $order ) {
    $payload['refund_reason'] = get_post_meta( $refund_id, '_refund_reason', true );
    return $payload;
}, 10, 3 );
`

= 8. Customize Order Note Payload =
`
add_filter( 'shopmotive_order_note_payload', function( $payload, $note_id, $order ) {
    $payload['note_urgency'] = 'high';
    return $payload;
}, 10, 3 );
`

= 9. Customize Checkout Payload =
`
add_filter( 'shopmotive_checkout_payload', function( $payload, $order_id, $order ) {
    $payload['cart_hash'] = $order->get_cart_hash();
    return $payload;
}, 10, 3 );
`

= 10. Filter API Retry Delay =
`
add_filter( 'shopmotive_retry_delay', function( $delay, $attempts, $topic ) {
    return 10 * $attempts; // Linear backoff of 10s per retry
}, 10, 3 );
`

== Frequently Asked Questions ==

= Does ShopMotive Connect affect shop loading speed? =
No. Webhook captures are completed in under 20ms and enqueued immediately. Data payload processing and ClickUp API requests are handled asynchronously in the background.

= Where are API keys and tokens stored? =
Client tokens are encrypted using AES-256-CBC and kept in the WordPress database option tables. No raw, unencrypted tokens are exposed.

= Is WooCommerce required? =
WooCommerce is required for checkout, order, and refund triggers. Standard customer registration hooks will function with default WordPress user creation events.

= How does the bi-directional order status sync work? =
When you map your WooCommerce statuses (Processing, Completed, etc.) to your ClickUp task statuses, any transition of a task status in ClickUp will send a secured API callback webhook to your WordPress site, which automatically updates the WooCommerce order status and logs an administrator note.

= Can I map specific WordPress users to ClickUp workspace members? =
Yes. The plugin includes a "User Identity Mappings" screen where you can map WordPress administrators and shop managers to their ClickUp users. Order notes and comments will be attributed to the mapped user.

= How does the fail-safe retry mechanism behave during ClickUp API outages? =
If the ClickUp API is offline or returns a 429 rate limit error, the event payload is retained in a local database queue. ShopMotive Connect will automatically retry the dispatch up to 5 times (using an exponential backoff filter) before logging it in the Sync Errors.

== Screenshots ==

1. Initial Connection Screen - Simple one-click OAuth 2.0 authorization to link your ClickUp workspace.
2. ClickUp Authorization Portal - Secure OAuth 2.0 workspace authorization and token exchange.
3. Integration Settings & Automation Engine - Configure order/customer sync triggers, custom field mappings, assignee routing rules, task priorities, and multi-store settings.
4. Real-Time Sync Logs - Live telemetry dashboard monitoring all dispatches, execution statuses, direct ClickUp task links, and CSV export.
5. Sync Errors & Diagnostic Traces - Dedicated error trace log with automatic retries and one-click manual retry controls.
6. Help Center & In-App Documentation - Setup guides, fulfillment mapping recipes, custom field reference, and integrated technical support.

== Changelog ==

= 1.2.1 =
* Official initial release for WooCommerce-to-ClickUp CRM, customer profile, and order task synchronization.
* Automated real-time order status synchronization between ClickUp task columns and WooCommerce.
* Custom field mapping engine for lifetime spend, billing details, custom checkout notes, and shipping addresses.
* Smart team assignee routing and priority tagging based on cart total thresholds.
* Bi-directional order note comments and refund tracking dispatches.
* Non-blocking background event dispatch queue ensuring 0ms impact on customer checkout speeds.

= 1.2.0 =
* Added dynamic administrative metadata synchronization (Site Title, Owner Name, Email) for enhanced support.
* Introduced visual plan badging with premium Crown/Diamond icon designs and active ClickUp API rate limit warnings.
* Integrated the collapsible FAQ Help Accordion center and main settings page cancellation modal.
* Implemented the Custom Quota Request CTA overlay to support high-volume WooCommerce checkouts.

= 1.1.0 =
* Introduced developer extensibility filters: shopmotive_should_sync_order, shopmotive_order_payload, shopmotive_retry_limit, and shopmotive_api_request_headers.
* Added bi-directional notes synchronization between WooCommerce order comments and ClickUp tasks.

= 1.0.0 =
* Initial WooCommerce-to-ClickUp sync connector release.
* Automated orders status mapping, customer updates, and draft checkouts logging.
