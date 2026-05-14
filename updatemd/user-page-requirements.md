# User Page — Feature Requirements

## Overview

The User Page is the account dashboard visible to logged-in customers. It gives users control over their profile, delivery address, order history, and messages — similar to how Shopee or Lazada handles the customer-side experience.

---

## 1. Account / Profile

### 1.1 View & Edit Profile

Users can view and update their personal information at any time.

**Editable fields:**
- Full name
- Email address
- Mobile number
- Profile photo (optional)
- Password (change password flow with current + new password)

---

### 1.2 Delivery Address

Users can manage their saved delivery addresses.

**Features:**
- View default delivery address
- Edit existing address
- Add multiple addresses (e.g., home, office)
- Set a default address — this will auto-fill at checkout
- Delete an address

**Address fields:**
- Full name (recipient)
- Mobile number
- House/unit no., street
- Barangay
- City / Municipality
- Province
- ZIP code

---

## 2. My Orders

A dedicated section where users can track all their orders.

### 2.1 Order Tabs

| Tab | Description |
|---|---|
| **All** | Complete order history |
| **Pending** | Order placed, waiting for confirmation |
| **Confirmed** | Order confirmed by the admin |
| **Out for Delivery** | Order is on its way |
| **Delivered** | Successfully received |
| **Cancelled** | Orders that were cancelled |

### 2.2 Order Card (per order)

Each order displays:
- Order ID
- Product name(s) and quantity
- Total amount
- Order date
- Current status (with a visual status tracker/progress bar)
- Estimated delivery date (if applicable)

**Features:**
- Click to view full order details
- Cancel order (only while status is still "Pending")
- Re-order (quickly add the same items to cart)

---

## 3. Messages / Support

Users can view messages they previously sent through the Contact page, along with any replies from the admin.

| Column | Description |
|---|---|
| Subject | Topic of the message |
| Date Sent | When the message was submitted |
| Status | Pending / Replied |
| Admin Reply | Visible once the admin has responded |

**Features:**
- Read-only view of sent messages and replies
- Option to send a new message (links to Contact page or opens a modal)

---

## 4. User Page Navigation

Suggested layout for the user account sidebar or tab navigation:

```
👤 My Profile
   └─ Personal Information
   └─ Change Password
📍 My Addresses
📦 My Orders
   └─ All
   └─ Pending
   └─ Out for Delivery
   └─ Delivered
   └─ Cancelled
✉️  My Messages
🚪 Log Out
```

---

## 5. Technical Notes

- **Protected Route:** The user page is only accessible when logged in. Unauthenticated users are redirected to the login page.
- **Auto-fill at Checkout:** The default saved address automatically populates the checkout delivery fields. Users can switch to another saved address before confirming.
- **Order Status Sync:** Order statuses on the user side reflect real-time updates made by the admin on the Admin Dashboard.
- **Responsive Design:** The user page must be fully functional on mobile, as most users are expected to browse and order via phone.

---

*Document prepared for development reference. Subject to change based on final design decisions.*
