# Admin Dashboard — Feature Requirements

## Overview

The Admin Dashboard is an internal management panel for the website. It gives the admin a centralized view of all important information: user accounts, orders, and messages from the Contact page.

---

## 1. User Accounts Management

### 1.1 Account Registration Flow (Shopee-style)

When a new user signs up, they will be prompted to fill in their delivery information before they can place an order.

**Required fields at sign-up:**
- Full name
- Email address
- Password
- Mobile number
- Delivery address (house/unit no., street, barangay, city/municipality, province, ZIP code)

> **Note:** The delivery information saved during sign-up will be automatically used on the next order — the user will not need to re-enter it. It can be edited anytime in profile settings.

---

### 1.2 Admin View — Accounts Table

The admin can see all registered users in a table:

| Column | Description |
|---|---|
| User ID | Unique identifier |
| Full Name | User's full name |
| Email | Email address |
| Mobile | Contact number |
| Address | Default delivery address |
| Date Registered | Account creation date |
| Status | Active / Inactive |
| Last Login | Most recent login timestamp |
| Last Logout | Most recent logout timestamp |

**Features:**
- Search and filter by name, email, or status
- View full profile and delivery details per user
- Deactivate or reactivate accounts
- Export user list (CSV)

---

### 1.3 Login / Logout Logs

All login and logout activity is recorded and visible to the admin:

| Column | Description |
|---|---|
| Log ID | Unique log entry |
| User | Name + email |
| Action | Login / Logout |
| Timestamp | Date and time |
| IP Address | (optional) For security monitoring |

**Features:**
- Filter by user, action type, or date range
- Sorted by most recent by default

---

## 2. Orders Overview

### 2.1 Dashboard Summary (At a Glance)

Quick stats displayed at the top of the admin page:

| Metric | Description |
|---|---|
| **Total Orders** | All-time order count |
| **Current / Active Orders** | Orders currently in progress (pending, confirmed, out for delivery) |
| **Completed Orders** | Successfully delivered / fulfilled orders |
| **Cancelled Orders** | Orders that were cancelled |

---

### 2.2 Orders Table

Detailed list of all orders:

| Column | Description |
|---|---|
| Order ID | Unique order number |
| Customer Name | Name of the customer who placed the order |
| Delivery Address | Shipping destination |
| Items Ordered | List of purchased products |
| Total Amount | Total payment |
| Order Date | Date the order was placed |
| Status | Pending / Confirmed / Out for Delivery / Delivered / Cancelled |

**Features:**
- Filter by status, date range, or customer name
- Update order status (e.g., mark as "Out for Delivery" or "Delivered")
- View full order details

---

## 3. Messages (from Contact Page)

### 3.1 Messages Inbox

All messages submitted through the website's Contact page are visible here:

| Column | Description |
|---|---|
| Message ID | Unique identifier |
| Sender Name | Name of the person who sent the message |
| Email | Sender's email address |
| Subject | Message subject |
| Message Preview | First few words of the message |
| Date Sent | Date and time sent |
| Status | Unread / Read / Replied |

**Features:**
- Click to open the full message
- Mark as read / unread
- Reply directly from the admin panel (optional enhancement)
- Filter by status (Unread, Read, Replied)
- Notification badge (e.g., "5 unread messages") visible in the admin sidebar

---

## 4. Admin Navigation (Sidebar)

Suggested sidebar structure for the admin panel:

```
📊 Dashboard        (overview & summary stats)
👥 User Accounts
   └─ All Users
   └─ Login / Logout Logs
📦 Orders
   └─ All Orders
   └─ Active Orders
✉️  Messages
   └─ Inbox
⚙️  Settings        (optional)
```

---

## 5. Technical Notes

- **Authentication:** Admin login must be separate from regular user login. All admin routes should be protected — inaccessible without an active admin session.
- **Auto-fill Delivery Info:** At checkout, the user's saved default address will automatically populate the delivery fields. The user can change it before confirming the order.
- **Real-time Updates (optional):** For messages and active orders, polling or WebSockets can be used to update counts without requiring a page refresh.
- **Responsive Design:** The admin panel should work on tablets and mobile devices for on-the-go management.

---

*Document prepared for development reference. Subject to change based on final design decisions.*
