# At Rest Order Log

WordPress plugin for tracking WooCommerce order changes with automatic revision history.

---

## 1. Main Functionality

The plugin automatically creates revision snapshots of WooCommerce orders before they are updated. Each revision includes:

-   **Order data**: status, items, quantities, prices, totals, fees
-   **Customer information**: billing address
-   **Metadata**: all custom order meta fields
-   **User tracking**: which admin user made the changes
-   **Timestamp**: when the revision was created

**Storage:**

-   Revisions are stored in a dedicated database table `wp_order_revisions` (not as order meta data)
-   This provides better performance and scalability for orders with many revisions
-   Displays the **20 most recent revisions** to avoid large queries and maintain fast page load times
-   Future improvement may include pagination if needed

**Data Cleanup:**

-   All revision data is **permanently deleted** when the plugin is uninstalled
-   Data is preserved during deactivation

Revisions are displayed in a dedicated meta box on the order edit screen with a collapsible interface for easy review of historical changes.

**Key Features:**

-   Automatic revision creation when clicking "Update" button
-   View last 20 revisions directly in order edit screen
-   See revision count in orders list table
-   Prevent editing of paid orders
-   Track which user made each change
-   Dedicated database table for optimal performance

**Location:**

-   View revisions: Navigate to any order in WooCommerce → Edit Order → scroll to "Order Revision History" meta box
-   See revision counts: WooCommerce → Orders → check "Revisions" column

---

## 2. Settings

**No configuration required!** The plugin works automatically after activation.

**Behavior:**

-   Revisions are created **only for unpaid orders**
-   Paid orders cannot be edited (controlled by `woocommerce_order_is_editable` filter)
-   Revisions are saved **only when clicking "Update" button** in admin panel
-   AJAX updates and autosave operations do NOT create revisions (intentional design choice)

---

## 3. Troubleshooting

---

## 4. Testing Notes

**Test Scenarios:**

1. **Create a revision:**

    - Create a new order (status: Pending payment)
    - Edit order details (change items, quantities, addresses)
    - Click "Update" button
    - Check "Order Revision History" meta box - should show new revision

2. **Verify revision data:**

    - Open a revision by clicking on it
    - Verify all data matches the **previous order state** (NOT the new data, only the old data before the update)
    - Check user information shows correct admin user who made the change

3. **Paid order protection:**

    - Create a revision for an unpaid order
    - Change order status to "Completed" (paid status)
    - This will create the **last revision** of the order while it was still unpaid
    - Try to edit order - fields that affect price should be read-only (except refund operations)
    - For testing: you can change the payment author (this field remains editable)
    - Verify no new revisions are created after payment
    - Try to change status again - this change will **NOT be recorded as a revision** because the previous order version was already paid

4. **Orders list column:**

    - Go to WooCommerce → Orders
    - Check "Revisions" column appears
    - Orders with revisions show count badge
    - Orders without revisions show "No revisions"

**Expected Results:**

-   Revisions created on every order edit (unpaid orders only)
-   All order data captured accurately
-   User info tracked correctly
-   CSS styling applied properly

---

## 5. Changelog

### Version 1.0.0 - 2025-11-20

    * initial functions

### Version 1.0.1 - 2025-11-20

    * add fees

### Version 1.1.0 - 2025-11-21

    * New storage system
