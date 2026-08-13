# Changelog

All notable changes to **ClickSync: Wordpress to ClickUp CRM Sync** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-08-13

### Added
- **Initial Release**: Launched **ClickSync: Wordpress to ClickUp CRM Sync** connecting WooCommerce & WordPress stores to ClickUp Workspaces.
- **Client-Side Payload Normalization**: Native PHP payload transformer converting WooCommerce orders, refunds, customer updates, and abandoned checkouts into standard Shopify JSON payloads.
- **Shopify Polaris Design System**: Built a custom WP Admin settings page matching Shopify Polaris UI cards, badges, and toggle switches.
- **3-Column Pricing Grid**: Interactive monthly sync usage progress bar and plan comparison grid (Free $0/mo, Growth $19.99/mo, Pro $49.99/mo).
- **HMAC Security**: Cryptographic request signing (`X-ClickSync-Hmac`) and replay attack protection (300s timestamp window) for API pings to `clicksync-connect.apps.loopstates.com`.
- **WooCommerce Event Listeners**: Hooks for `woocommerce_new_order`, `woocommerce_order_status_changed`, `woocommerce_order_refunded`, `woocommerce_created_customer`, and `woocommerce_checkout_order_processed`.
- **Contextual Limit Warnings**: Non-intrusive dismissible admin notice when 100 free monthly sync runs are reached.
- **WordPress.org Readiness**: Full compliance with WordPress plugin guidelines, GPLv2 licensing, `readme.txt` SaaS disclosures, sanitization/escaping, and clean `uninstall.php` handler.
