# 📜 SwiftSync Changelog

All notable changes, new feature additions, security enhancements, and improvements for **SwiftSync: CRM Sync for ClickUp and WooCommerce** will be documented in this file.

---

## [1.2.1] - 2026-08-26

### 🚀 Official Release
- **Automated Order-to-Task Conversion:** Automatically creates structured ClickUp task cards when customers place orders on WooCommerce, giving fulfillment teams instant real-time visibility.
- **2-Way Order Status Synchronization:** Moving a task status inside ClickUp (e.g. to "Completed" or "Shipped") automatically updates the WooCommerce order status and triggers customer notifications.
- **360° Customer Profile & CRM Metadata Sync:** Syncs buyer profiles, lifetime order history, total spend, billing details, and shipping addresses directly into custom ClickUp fields.
- **Smart Assignee Routing & Priority Rules:** Automatically assigns high-value orders to specific team account managers and applies priority tags based on order amounts.
- **Bi-Directional Order Notes & Refund Tracking:** Synchronizes internal WooCommerce order comments and customer refund requests directly into ClickUp task comment threads.
- **Zero Checkout Overhead:** Background queue dispatch engine processes event payloads with 0ms impact on WooCommerce checkout loading speeds.
- **Developer API & Filter Hooks:** Includes native WordPress developer filters (`swiftsync_should_sync_order`, `swiftsync_order_payload`, etc.) for custom conditional overrides.
