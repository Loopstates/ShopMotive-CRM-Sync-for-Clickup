<p align="center">
  <a href="https://wordpress.org/plugins/shopmotive-crm-sync-for-clickup/">
    <img src="https://ps.w.org/shopmotive-crm-sync-for-clickup/assets/banner-1544x500.png" alt="ShopMotive: CRM Sync for ClickUp" width="100%">
  </a>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/shopmotive-crm-sync-for-clickup/"><img src="https://img.shields.io/wordpress/plugin/v/shopmotive-crm-sync-for-clickup.svg?color=f97316&label=WordPress.org" alt="WordPress Plugin Version"></a>
  <a href="https://wordpress.org/plugins/shopmotive-crm-sync-for-clickup/"><img src="https://img.shields.io/wordpress/plugin/tested/shopmotive-crm-sync-for-clickup.svg?color=16a34a&label=Tested%20Up%20To" alt="Tested Up To"></a>
  <img src="https://img.shields.io/badge/PHP-%3E%3D%207.4-4f46e5.svg" alt="PHP Requirement">
  <img src="https://img.shields.io/badge/WooCommerce-HPOS%20Compatible-9333ea.svg" alt="WooCommerce HPOS Compatible">
  <a href="https://www.gnu.org/licenses/gpl-2.0.html"><img src="https://img.shields.io/badge/License-GPL--2.0%2B-blue.svg" alt="License: GPL-2.0+"></a>
</p>

---

# ShopMotive: CRM Sync for ClickUp

ShopMotive connects your WooCommerce store to your ClickUp workspace. It automatically synchronizes order transactions, customer accounts, order notes, status transitions, and refund events into structured ClickUp tasks in real time.

Built for high-volume merchants, support desks, fulfillment teams, and enterprise workflows, ShopMotive ensures your CRM and e-commerce store remain aligned without affecting front-end customer checkout speeds.

* **WordPress.org Directory**: [wordpress.org/plugins/shopmotive-crm-sync-for-clickup](https://wordpress.org/plugins/shopmotive-crm-sync-for-clickup/)
* **Official Documentation**: [docs.loopstates.com/shopmotive-for-clickup-and-woocommerce](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/)
* **Cloud Portal**: [shopmotive.apps.loopstates.com](https://shopmotive.apps.loopstates.com/)
* **Author**: [Loopstates](https://loopstates.com)

---

## Core Capabilities

* **Automated Order Synchronization**: Converts WooCommerce orders into ClickUp tasks containing order line items, financial totals, customer billing/shipping details, and transaction timestamps.
* **Customer Profile Sync**: Creates or updates dedicated CRM task cards whenever a customer registers or completes a checkout.
* **Status Transitions & Refunds**: Synchronizes order status transitions (Processing, Completed, On-Hold, Cancelled) and creates structured subtasks or comments for refund events.
* **Custom Field Mapping Engine**: Map arbitrary WooCommerce order and customer metadata (including custom checkout fields, tracking numbers, and lifetime spend) directly into ClickUp custom fields.
* **High-Performance Order Storage (HPOS)**: Fully compatible with WooCommerce HPOS (custom order tables) and legacy post-meta architectures.
* **Asynchronous Queue Pipeline**: Dispatches events asynchronously via non-blocking background workers, guaranteeing zero latency during customer checkouts.
* **Cryptographic Payload Signing**: All API handshakes and webhooks are verified via HMAC SHA-256 signatures using dynamic secret keys and timestamp validation.

---

## Architecture

```
[ WooCommerce Store ]
        |
        | (Asynchronous Non-Blocking Event Hook)
        v
[ ShopMotive Client Plugin ]  --->  (HMAC SHA-256 Signed Payload)
        |
        v
[ ShopMotive Cloud Proxy ]   --->  (Queue, Rate-Limit, Decrypt)
        |
        v
[ ClickUp REST API v2 ]      --->  [ ClickUp Workspace / Tasks ]
```

1. **WordPress Client Plugin**: Captures WooCommerce action hooks, normalizes order/customer payloads, signs transactions using merchant secrets, and dispatches requests.
2. **Cloud Sync Proxy**: Manages OAuth 2.0 handshakes, decrypts keys, buffers burst traffic, handles exponential-backoff retries, and enforces API rate limits against ClickUp endpoints.
3. **ClickUp REST API**: Creates tasks, assigns team members, updates custom fields, and logs audit comments in designated ClickUp Spaces, Folders, and Lists.

---

## Requirements

| Component | Minimum Supported | Recommended |
| :--- | :--- | :--- |
| **WordPress** | 6.0 | 6.5+ |
| **PHP** | 7.4 | 8.1 / 8.2 / 8.3 |
| **WooCommerce** | 7.0 | Latest stable |
| **ClickUp** | Free, Unlimited, Business, or Enterprise | Any active workspace |

---

## Installation

### Option 1: WordPress Plugin Directory (Recommended)

1. Navigate to **Plugins -> Add New Plugin** in your WordPress Admin dashboard.
2. Search for `ShopMotive: CRM Sync for ClickUp`.
3. Click **Install Now**, then click **Activate**.
4. Go to **ShopMotive -> Settings** to link your ClickUp workspace.

### Option 2: Manual Installation via Git / Zip

1. Clone or download this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/loopstates/shopmotive-crm-sync-for-clickup.git
   ```
2. Ensure folder permissions allow WordPress to read the plugin files.
3. Activate the plugin in **Plugins -> Installed Plugins**.

---

## Configuration

1. In WordPress Admin, navigate to **ShopMotive -> Settings**.
2. Click **Connect ClickUp** to initiate the OAuth 2.0 authorization handshake.
3. Select the ClickUp Workspace you want to link.
4. Define your synchronization rules:
   * Select the target ClickUp **Space**, **Folder**, and **List** for Orders.
   * Select the target List for Customers.
   * Enable or disable order status tracking and refund events.
5. Save settings to begin immediate synchronization.

---

## Developer Extensibility

ShopMotive provides native WordPress filter and action hooks, enabling developers to customize sync logic, alter payloads, or inject proprietary metadata before dispatch.

### Filter: Modify Order Payload

```php
add_filter( 'shopmotive_order_payload', function( array $payload, int $order_id ) {
    // Add custom ERP accounting identifier to task description
    $payload['description'] .= "\nERP Code: " . get_post_meta( $order_id, '_erp_account_id', true );
    return $payload;
}, 10, 2 );
```

### Filter: Conditionally Bypass Synchronization

```php
add_filter( 'shopmotive_should_sync_order', function( bool $should_sync, \WC_Order $order ) {
    // Skip synchronization for internal test orders
    if ( $order->get_total() == 0 && 'test@example.com' === $order->get_billing_email() ) {
        return false;
    }
    return $should_sync;
}, 10, 2 );
```

### Action: Hook Into Successful Dispatches

```php
add_action( 'shopmotive_payload_dispatched', function( string $event, array $payload, $response ) {
    // Log telemetry to custom audit service
    error_log( sprintf( '[ShopMotive] Event %s successfully queued for ClickUp.', $event ) );
}, 10, 3 );
```

---

## Directory Structure

```
shopmotive-crm-sync-for-clickup/
├── assets/
│   ├── css/                  # Admin styling sheets
│   ├── images/               # Logos and icons
│   └── js/                   # Admin UI controllers and AJAX scripts
├── languages/                # Translation .pot and locale files
├── src/
│   ├── Admin/                # WP Admin menu routes and page views
│   ├── Api/                  # HMAC client dispatcher and webhook receivers
│   ├── Core/                 # Autoloader, settings options, and lifecycle hooks
│   └── Integrations/         # WooCommerce normalizers and HPOS hooks
├── shopmotive-crm-sync-for-clickup.php  # Main plugin bootstrap entry point
├── readme.txt                # Official WordPress.org repository metadata
└── README.md                 # GitHub documentation
```

---

## Security & Privacy Disclosures

* **Data Encryption**: All tokens, API keys, and customer payloads in transit are secured via TLS 1.3 encryption.
* **HMAC Signatures**: Every incoming and outgoing webhook payload is signed with an HMAC SHA-256 hash to prevent replay attacks and tampering.
* **Capability & Nonce Checks**: All WordPress Admin endpoints and AJAX handlers require `manage_woocommerce` or `manage_options` permissions and verify cryptographic nonces.

---

## Support & Contributing

* **Issue Reporting**: For bug reports or technical inquiries, submit an issue on this repository or contact support@loopstates.com.
* **Documentation**: Detailed guides are available in the [ShopMotive Documentation Center](https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/).
* **Commercial & Custom Enterprise Integrations**: Reach out to the engineering team at [Loopstates](https://loopstates.com).

---

## License

This software is released under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
