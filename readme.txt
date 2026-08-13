=== ClickSync: Wordpress to ClickUp CRM Sync ===
Contributors: loopstates
Tags: clickup, woocommerce, crm, task management, automation
Requires at least: 5.8
Tested up to: 6.7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automate your WooCommerce workflow by converting orders, refunds, customer updates, and abandoned checkouts directly into ClickUp tasks.

== Description ==

ClickSync is a powerful, lightweight integration connecting your WordPress & WooCommerce store directly to your ClickUp Workspaces. Automatically generate structured ClickUp tasks whenever new orders are placed, customers register, orders are refunded, or checkouts are left incomplete.

This plugin acts as a client interface connecting your WordPress site to the ClickSync Cloud Service (https://clicksync-connect.apps.loopstates.com) which communicates with ClickUp's official API via OAuth 2.0.

= Key Features =

* **Automatic Order Sync:** Instantly convert new WooCommerce orders into ClickUp tasks with customer details, billing, shipping, and line items.
* **Order Status Updates:** Automatically update ClickUp task statuses when WooCommerce orders are processed or completed.
* **Refund & Cancellation Sync:** Sync refund details and revised order totals to ClickUp subtasks.
* **Customer Registration Sync:** Create or update customer contact tasks in ClickUp when new users register.
* **Abandoned Checkout Tracking:** Capture incomplete WooCommerce draft checkouts and send follow-up tasks to ClickUp.
* **3-Column Usage Quota Grid:** Clear, real-time tracking of monthly sync usage and plan tiers directly inside WP Admin.

= Third-Party SaaS Service Disclosure =

ClickSync relies on external cloud services to perform real-time synchronization between WordPress, WooCommerce, and ClickUp:

* **ClickSync Cloud Service:** https://clicksync-connect.apps.loopstates.com
* **ClickUp API Service:** https://api.clickup.com

By using this plugin, store data (orders, refunds, customer details, line items) is transmitted via encrypted HTTPS to ClickSync Cloud and ClickUp to generate and update tasks authorized by your ClickUp account.

* ClickSync Privacy Policy: https://docs.loopstates.com/clicksync/privacy-policy.html
* ClickSync Terms of Use: https://docs.loopstates.com/clicksync/terms.html
* ClickUp Privacy Policy: https://clickup.com/privacy
* ClickUp API Terms: https://clickup.com/terms

== Installation ==

1. Upload the `clicksync-wordpress` folder to the `/wp-content/plugins/` directory, or install directly via the WordPress Admin Plugins menu.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **WooCommerce -> ClickSync** in your WP Admin sidebar.
4. Click **Connect ClickUp Workspace** to authorize your ClickUp account.
5. Enable your desired syncing rules (Orders, Customers, Refunds, Draft Checkouts) and click Save Settings.

== Frequently Asked Questions ==

= Does ClickSync slow down my online store? =
No! ClickSync processes all webhooks and dispatches payloads asynchronously. It adds zero script execution or database query overhead to customer-facing checkout pages.

= Is WooCommerce required to use ClickSync? =
WooCommerce is recommended to enable order, refund, and checkout syncing. Customer registration syncing works across standard WordPress user accounts.

= How does the free plan work? =
ClickSync includes 100 free sync runs every 30 days. You can upgrade to Growth or Pro tiers anytime directly from the WP Admin dashboard.

== Changelog ==

= 1.0.0 =
* Initial public release of ClickSync: Wordpress to ClickUp CRM Sync.
