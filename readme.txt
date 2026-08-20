=== ClickSync Connect: ClickUp CRM Sync for WooCommerce ===
Contributors: loopstates
Tags: clickup, woocommerce, crm, task management, sync
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect and synchronize WooCommerce order events, notes, and customers directly with ClickUp tasks.

== Description ==

ClickSync Connect is an enterprise-grade, asynchronous synchronization engine designed to seamlessly bridge WooCommerce storefront activity with your ClickUp workspace. The plugin instantly captures e-commerce events—such as new checkouts, user registrations, refunds, order status transitions, and order notes—and queues them for structured syncing into your ClickUp lists.

This plugin serves as the client connector linking your WordPress database with the ClickSync Cloud Service (https://clicksync-connect.apps.loopstates.com). By offloading heavy data serialization and external API request handshakes, ClickSync protects your WooCommerce execution threads and guarantees zero customer checkout delays. All merchant access tokens are stored with AES-256 encryption at rest, and webhook dispatches undergo real-time SHA-256 HMAC signature verification.

== Subscription Plans & Pricing ==

ClickSync Connect offers flexible plans to scale with your business requirements:

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

* **Asynchronous Task Queue (Under 20ms Capture):** Captures WooCommerce checkouts in under 20ms and enqueues them in a local processing table, ensuring customer loading speeds are completely unaffected by external API latency.
* **Premium Subscription Visualizer:** Features rich, professional badges detailing your active ClickSync plan (with custom gold Crown icons for Pro Plan and elegant violet Diamond icons for Growth Plan) and dynamic ClickUp API rate-limit meters.
* **Interactive Help & FAQ Accordions:** Built-in interactive collapsible FAQ dashboard designed for rapid troubleshooting, manual order sync instructions, and direct billing controls.
* **Bi-Directional Order Status Syncing:** Automatically maps WooCommerce order status fields (Processing, Completed, On-hold, Cancelled) to your ClickUp task statuses. Moving a task status inside ClickUp triggers the WooCommerce core order transition instantly.
* **Dynamic Customer Contact Syncing:** Keeps the ClickSync support team updated with your administrator contact details so we can alert you about API handshake errors or expired OAuth tokens before they affect your business.
* **Custom Field Mapping Engine:** Map standard WooCommerce order parameters—including billing details, customer lifetime value, shipping address, or custom checkout attributes—directly to custom text, list, number, or checkbox fields in ClickUp.
* **Line-Item Split Routing:** Choose whether multi-item WooCommerce checkouts generate a single parent task or separate child tasks in ClickUp, enabling you to route different items to separate team members or fulfillment pipelines.
* **Refund & Cancellation Syncing:** Track refunds, order cancellations, and item adjustments as automated updates. Full or partial refunds update order totals and log details directly as task comments.
* **WP Admin Order Sidebar Widget:** Adds a live metadata panel to the WooCommerce Edit Order screen displaying the linked ClickUp Task ID, real-time status updates, active assignees, and direct workspace links.
* **Resilient Fail-Safe Retry System:** Retains webhook payloads during ClickUp outages or API rate limiting (HTTP 429), automatically executing up to 5 scheduled retries to prevent data loss.
* **Custom Quota Request Desk:** Submit custom transaction quota and webhook requests directly from your settings panel to accommodate high-volume holiday sales or promotional traffic.
* **WordPress Developer API:** Built with hooks and filters allowing developers to conditionally bypass sync events, modify outgoing payloads, customize retry intervals, or add partner headers.

== External Services ==

ClickSync Connect utilizes external cloud APIs to manage authentication tokens, decrypt keys, and queue requests safely:

* **ClickSync Cloud Sync Proxy:** https://clicksync-connect.apps.loopstates.com
* **ClickUp REST API Service:** https://api.clickup.com

By activating this connector, e-commerce data (order line items, notes, billing addresses, and customer profiles) is securely sent to the ClickSync Cloud Service and ClickUp APIs via HTTPS to generate tasks.

* ClickSync Privacy Policy: https://docs.loopstates.com/clicksync-woocommerce/privacy-policy.html
* ClickSync Terms: https://docs.loopstates.com/clicksync-woocommerce/plans.html
* ClickUp Terms of Service: https://clickup.com/terms

== Professional Data Safety & Collection Disclosure ==

To guarantee high service reliability, prompt technical support, and prevention of synchronization outages, ClickSync Connect securely synchronizes specific store administrative metadata with our cloud servers:

* **Site Title (Store Name):** Collected to identify your store configuration in our administrative portal.
* **Administrator Email Address:** Retained to automatically notify you in the event of continuous ClickUp API handshake failures or key expirations.
* **Site Owner / Administrator Name:** Used to personalize merchant assistance and authenticate support request tickets.
* **Integration Telemetry (Sync Counts & Log Stats):** Tracks transaction volumes to monitor subscription quotas and alert you before you reach rate limits.

All administrative metadata is stored in our secure, encrypted cloud database. This data is utilized solely for customer support, service health monitoring, and system security, and is never shared with third parties. Telemetry data is retained for the lifetime of your integration and is permanently deleted within 30 days of subscription cancellation.

== Installation ==

1. Upload the `clicksync-connect` folder to the `/wp-content/plugins/` directory, or search and install via the WordPress Admin Plugins manager.
2. Activate the plugin.
3. Go to **WooCommerce -> ClickSync** in your dashboard.
4. Click **Connect ClickUp Workspace** and authorize access via OAuth 2.0.
5. Choose your list destinations, customize sync triggers, and click Save Settings.

== Developer API: Hooks & Filters ==

ClickSync Connect is built with extensibility at its core. You can use standard WordPress filters to customize sync logic:

= 1. Bypass Syncing for Specific Orders =
`
add_filter( 'clicksync_should_sync_order', function( $should_sync, $order_id, $order ) {
    // Example: Bypass sync if order total is under $10.00
    if ( $order && $order->get_total() < 10.00 ) {
        return false;
    }
    return $should_sync;
}, 10, 3 );
`

= 2. Customize Order Sync Payloads =
`
add_filter( 'clicksync_order_payload', function( $payload, $order ) {
    // Example: Append a custom checkout field to order payload
    $payload['meta_data']['custom_gift_wrap_note'] = get_post_meta( $order->get_id(), '_gift_wrap_note', true );
    return $payload;
}, 10, 2 );
`

= 3. Adjust Failed Webhook Retry Attempts =
`
add_filter( 'clicksync_retry_limit', function( $limit ) {
    return 5; // Increase retry tolerance to 5 attempts
} );
`

= 4. Filter HTTP API Request Arguments =
`
add_filter( 'clicksync_api_request_headers', function( $headers, $endpoint ) {
    $headers['X-Developer-Partner-Id'] = 'my_partner_key';
    return $headers;
}, 10, 2 );
`

= 5. Bypass Customer Syncing =
`
add_filter( 'clicksync_should_sync_customer', function( $should_sync, $customer_id, $new_data ) {
    // Example: Bypass sync for test accounts
    if ( isset( $new_data['email'] ) && strpos( $new_data['email'], '@test.com' ) !== false ) {
        return false;
    }
    return $should_sync;
}, 10, 3 );
`

= 6. Customize Customer Payload =
`
add_filter( 'clicksync_customer_payload', function( $payload, $customer_id ) {
    $payload['custom_user_segment'] = 'VIP';
    return $payload;
}, 10, 2 );
`

= 7. Customize Refund Payload =
`
add_filter( 'clicksync_refund_payload', function( $payload, $refund_id, $order ) {
    $payload['refund_reason'] = get_post_meta( $refund_id, '_refund_reason', true );
    return $payload;
}, 10, 3 );
`

= 8. Customize Order Note Payload =
`
add_filter( 'clicksync_order_note_payload', function( $payload, $note_id, $order ) {
    $payload['note_urgency'] = 'high';
    return $payload;
}, 10, 3 );
`

= 9. Customize Checkout Payload =
`
add_filter( 'clicksync_checkout_payload', function( $payload, $order_id, $order ) {
    $payload['cart_hash'] = $order->get_cart_hash();
    return $payload;
}, 10, 3 );
`

= 10. Filter API Retry Delay =
`
add_filter( 'clicksync_retry_delay', function( $delay, $attempts, $topic ) {
    return 10 * $attempts; // Linear backoff of 10s per retry
}, 10, 3 );
`

== Frequently Asked Questions ==

= Does ClickSync Connect affect shop loading speed? =
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
If the ClickUp API is offline or returns a 429 rate limit error, the event payload is retained in a local database queue. ClickSync Connect will automatically retry the dispatch up to 5 times (using an exponential backoff filter) before logging it in the Sync Error Center.

== Changelog ==

= 1.2.0 =
* Added dynamic administrative metadata synchronization (Site Title, Owner Name, Email) for enhanced support.
* Introduced visual plan badging with premium Crown/Diamond icon designs and active ClickUp API rate limit warnings.
* Integrated the collapsible FAQ Help Accordion center and main settings page cancellation modal.
* Implemented the Custom Quota Request CTA overlay to support high-volume WooCommerce checkouts.

= 1.1.0 =
* Introduced developer extensibility filters: clicksync_should_sync_order, clicksync_order_payload, clicksync_retry_limit, and clicksync_api_request_headers.
* Added bi-directional notes synchronization between WooCommerce order comments and ClickUp tasks.

= 1.0.0 =
* Initial WooCommerce-to-ClickUp sync connector release.
* Automated orders status mapping, customer updates, and draft checkouts logging.
