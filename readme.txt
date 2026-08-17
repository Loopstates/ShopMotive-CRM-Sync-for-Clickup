=== ClickSync Connect: WooCommerce to ClickUp CRM Sync ===
Contributors: loopstates
Tags: clickup, woocommerce, crm, task management, developer-api
Requires at least: 5.8
Tested up to: 6.7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect and synchronize WooCommerce store events, order notes, checkout metadata, and customers directly with ClickUp tasks.

== Description ==

ClickSync Connect is an asynchronous sync engine designed to bridge WooCommerce storefront activity with ClickUp workspaces. The plugin captures e-commerce events—such as new checkouts, registration forms, refunds, status transitions, and order notes—and queues them for structured syncing into your ClickUp lists.

This plugin serves as the client connector linking your WordPress database with the ClickSync Cloud Service (https://clicksync-connect.apps.loopstates.com). By offloading serialization and API request handshakes, ClickSync protects WooCommerce execution threads and prevents transaction bottlenecks during peak checkout periods. All merchant access tokens are stored with AES-256 encryption at rest, and webhook dispatches undergo real-time SHA-256 HMAC signature verification.

= Key Features =

* **Asynchronous Task Queue (Under 20ms Capture):** Captures webhook signals in under 20ms and stores them in a processing database queue, preventing database locks and ensuring zero script overhead on client-facing checkout pages.
* **Bi-Directional Order Status Syncing:** Maps WooCommerce status fields (Processing, Completed, On-hold, Cancelled) to your ClickUp task statuses. Moving a task status inside ClickUp automatically triggers the WooCommerce core order transition.
* **Custom Field Mapping Engine:** Map standard WooCommerce order parameters—including billing details, customer lifetime value, shipping address, or custom checkout attributes—directly to custom text, list, number, or checkbox fields in ClickUp.
* **Line-Item Split Routing:** Choose whether multi-item WooCommerce checkouts generate a single parent task or separate child tasks in ClickUp, enabling you to route different items to separate team members or fulfillment pipelines.
* **Refund & Cancellation Syncing:** Track refunds, order cancellations, and item adjustments as automated updates. Full or partial refunds update order totals and log details directly as task comments.
* **Customer Registration & CRM Profiles:** Sync WordPress user creation events and WooCommerce billing records to dedicated ClickUp customer tasks, keeping customer logs and profiles synchronized.
* **WP Admin Order Sidebar Widget:** Adds a live metadata panel to the WooCommerce Edit Order screen displaying the linked ClickUp Task ID, real-time status updates, active assignees, and direct workspace links.
* **Resilient Fail-Safe Retry System:** Retains webhook payloads during ClickUp outages or API rate limiting (HTTP 429), automatically executing up to 5 scheduled retries to prevent data loss.
* **WordPress Developer API:** Built with hooks and filters allowing developers to conditionally bypass sync events, modify outgoing payloads, customize retry intervals, or add partner headers.

= Third-Party SaaS Service Disclosure =

ClickSync Connect utilizes external cloud APIs to manage authentication tokens, decrypt keys, and queue requests safely:

* **ClickSync Cloud Sync Proxy:** https://clicksync-connect.apps.loopstates.com
* **ClickUp REST API Service:** https://api.clickup.com

By activating this connector, e-commerce data (order line items, notes, billing addresses, and customer profiles) is securely sent to the ClickSync Cloud Service and ClickUp APIs via HTTPS to generate tasks.

* ClickSync Privacy Policy: https://docs.loopstates.com/clicksync-woocommerce/privacy-policy.html
* ClickSync Terms: https://docs.loopstates.com/clicksync-woocommerce/plans.html
* ClickUp Terms of Service: https://clickup.com/terms

== Installation ==

1. Upload the `clicksync-wordpress` folder to the `/wp-content/plugins/` directory, or search and install via the WordPress Admin Plugins manager.
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

== Frequently Asked Questions ==

= Does ClickSync Connect affect shop loading speed? =
No. Webhook captures are completed in under 20ms and enqueued immediately. Data payload processing and ClickUp API requests are handled asynchronously in the background.

= Where are API keys and tokens stored? =
Client tokens are encrypted using AES-256-CBC and kept in the WordPress database option tables. No raw, unencrypted tokens are exposed.

= Is WooCommerce required? =
WooCommerce is required for checkout, order, and refund triggers. Standard customer registration hooks will function with default WordPress user creation events.

== Changelog ==

= 1.1.0 =
* Introduced developer extensibility filters: clicksync_should_sync_order, clicksync_order_payload, clicksync_retry_limit, and clicksync_api_request_headers.
* Added bi-directional notes synchronization between WooCommerce order comments and ClickUp tasks.

= 1.0.0 =
* Initial WooCommerce-to-ClickUp sync connector release.
* Automated orders status mapping, customer updates, and draft checkouts logging.
