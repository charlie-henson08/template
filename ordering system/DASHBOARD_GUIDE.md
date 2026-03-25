# Dashboard System - Complete Guide

A complete authentication and dashboard system for three types of users: Customers, Producers, and Admins.

## Overview

The new dashboard system includes:
- **User Authentication** (Login/Register)
- **Three User Types** with different permissions and dashboards
- **Loyalty Rewards System** for customers
- **Producer Management** (products, stock, orders)
- **Admin Controls** (approve/manage producers)

## User Types

### 1. **Customers (Users)**
Regular shoppers who can:
- Browse and purchase products
- Track loyalty points and rewards
- View transaction history
- Add items to cart and checkout

**Demo Account:**
- Email: `customer@store.com`
- Password: `password`

### 2. **Producers**
Business partners who can:
- Manage their products
- Edit product descriptions and pricing
- Monitor stock levels
- View orders for their products
- Need admin approval to become active

**Demo Account:**
- Email: `producer@store.com`
- Password: `password`
- Status: Active (pre-approved)

### 3. **Admins**
System administrators who can:
- Approve/reject new producers
- Deactivate or suspend producers
- View producer statistics
- Manage system access

**Demo Account:**
- Email: `admin@store.com`
- Password: `admin123`

## Database Schema

### New Tables

**users** (Customers)
```sql
- id (INT, Primary Key)
- full_name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, hashed)
- loyalty_points (INT, default: 0)
- created_at (TIMESTAMP)
```

**producers**
```sql
- id (INT, Primary Key)
- full_name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, hashed)
- company_name (VARCHAR 100)
- status (ENUM: active, inactive, suspended)
- created_at (TIMESTAMP)
```

**admins**
```sql
- id (INT, Primary Key)
- full_name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, hashed)
- created_at (TIMESTAMP)
```

**loyalty_transactions**
```sql
- id (INT, Primary Key)
- user_id (INT, Foreign Key to users)
- points (INT)
- description (VARCHAR 255)
- created_at (TIMESTAMP)
```

### Updated Tables

**products** (Added columns)
```sql
- stock (INT, default: 100)
- producer_id (INT, Foreign Key to producers)
```

## File Structure

```
ordering system/
├── auth/
│   ├── session.php          # Session management functions
│   ├── login.php            # Login page
│   ├── register.php         # Registration page (Customer/Producer)
│   └── logout.php           # Logout handler
├── dashboards/
│   ├── dashboard.php        # Router (redirects to appropriate dashboard)
│   ├── customer_dashboard.php    # Customer loyalty & profile
│   ├── producer_dashboard.php    # Producer management
│   └── admin_dashboard.php       # Admin controls
└── [existing files...]
```

## Authentication Flow

### Login Process
1. User selects user type (Customer, Producer, Admin)
2. Enters email and password
3. System verifies credentials in appropriate table
4. Creates session with user ID and type
5. Redirects to dashboard

### Registration Process
1. User selects account type
2. Fills in required fields:
   - **Customer:** Full name, email, password
   - **Producer:** Full name, email, password, company name
3. Password hashed using bcrypt
4. Account created:
   - **Customer:** Immediately active
   - **Producer:** Inactive (requires admin approval)
5. Redirects to login page

### Session Management
- Sessions stored in `$_SESSION` array
- Keys used:
  - `user_id` / `user_email` (Customer)
  - `producer_id` / `producer_email` / `company_name` (Producer)
  - `admin_id` / `admin_email` (Admin)
  - `user_type` (all)

## Key Features

### Customer Dashboard

**Profile Information**
- View full name and email
- See total loyalty points
- Quick links to products and cart

**Loyalty Rewards System**
- Real-time points display
- Progress bar to next reward
- Shows:
  - Total points earned
  - Rewards earned (1 reward per 1000 points)
  - Points needed for next reward
- Recent transaction history

**How Loyalty Works**
- 1 point per dollar spent
- 1000 points = 1 reward
- Points displayed in transaction list
- Admin can manually add points via database

### Producer Dashboard

**Tabs:**
1. **Products**
   - View all products by producer
   - Edit product details:
     - Product name
     - Price
     - Stock quantity
   - Modal dialog for editing

2. **Stock Management**
   - View current stock levels
   - Status indicators:
     - **Red:** Low stock (≤10 units)
     - **Yellow:** Medium (≤25 units)
     - **Green:** Healthy (>25 units)
   - Adjust stock levels

3. **Orders**
   - View recent orders for producer's products
   - Shows:
     - Order ID
     - Product name
     - Quantity ordered
     - Product price
     - Order date

**Company Information**
- View manager name
- Company details
- Email contact

### Admin Dashboard

**Statistics**
- Total number of producers
- Active producers count
- Pending approval count

**Producer Management**
- View all registered producers
- Status badges (Active/Inactive/Suspended)
- Action buttons for each producer:
  - **Approve:** Activate inactive producer
  - **Deactivate:** Set to inactive (pause access)
  - **Suspend:** Suspend bad actors

**Producer Card Info**
- Company name
- Manager name
- Email
- Registration date
- Current status
- Action buttons

## Security Features

1. **Password Hashing**
   - Uses PHP bcrypt (PASSWORD_BCRYPT)
   - password_hash() for storage
   - password_verify() for authentication

2. **Session Security**
   - Session-based authentication
   - Redirects to login if not authenticated
   - Logout destroys session completely

3. **Input Validation**
   - Email validation
   - Password requirements (min 6 chars)
   - HTML special character escaping

4. **Database Security**
   - Prepared statements with bound parameters
   - Protection against SQL injection
   - UNIQUE constraints on emails

## API Endpoints

### Checkout Endpoint
- **File:** `api/checkout.php`
- **Method:** POST
- **Authentication:** None required (localStorage cart)
- **Payload:** JSON cart data
- **Response:** JSON success/error

Example Request:
```javascript
fetch('api/checkout.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        cart: [
            {id: 1, name: "Product", price: 99.99, quantity: 1}
        ]
    })
})
```

## Usage Guide

### For Customers

1. **Register/Login**
   - Click "Register" to create account
   - Click "Login" to access existing account
   - Select "Customer" tab

2. **Shopping**
   - Browse products on main page
   - Add items to cart
   - View cart and checkout

3. **Loyalty**
   - Click "Profile" (after login)
   - View loyalty points progress
   - See transaction history

### For Producers

1. **Register**
   - Click "Register"
   - Select "Producer" tab
   - Fill company name
   - Wait for admin approval

2. **After Approval**
   - Login with producer credentials
   - Access "Producer Dashboard"
   - View "Products" tab to edit
   - View "Stock" tab to manage levels
   - View "Orders" tab to see purchases

3. **Edit Products**
   - Click "Edit" button on product
   - Update name, price, stock
   - Click "Save Changes"

### For Admins

1. **Login**
   - Click "Login"
   - Select "Admin" tab
   - Use admin credentials

2. **Manage Producers**
   - View all producer applications
   - Click "Approve" to activate
   - Click "Deactivate" to pause
   - Click "Suspend" to revoke access

## Demo Workflow

### Complete Shopping Experience

1. **Setup Database**
   - Visit: `http://localhost/...../db_init.php`
   - All demo accounts created

2. **Login as Customer**
   - Go to Login page
   - Select "Customer" tab
   - Email: `customer@store.com` / Password: `password`
   - View loyalty dashboard

3. **Browse Products**
   - Click "Products" or main page
   - With 500 starting points visible

4. **Make a Purchase**
   - Click "Add to Cart"
   - Go to Cart page
   - Checkout
   - See order saved to database

5. **Admin Approval Flow (Optional)**
   - Register as producer (not pre-approved)
   - Login as admin
   - Approve new producer
   - Login as producer
   - Manage products

## Customization

### Change Loyalty Points Per Dollar
In `customer_dashboard.php`:
```php
$pointsForReward = 1000; // Change this number
```

### Change Producer Access Control
In `admin_dashboard.php`:
- Modify the status options (active, inactive, suspended)
- Add more detailed producer information

### Modify Password Requirements
In `auth/register.php`:
```php
if (strlen($password) < 6) // Change minimum length
```

### Add Email Notifications
Can be added to:
- `auth/register.php` - Welcome email
- `auth/login.php` - Login attempt email
- `dashboards/admin_dashboard.php` - Producer approval email

## Troubleshooting

**"Unknown database" error:**
- Run `db_init.php` to create all tables

**Can't login:**
- Check email is correct
- Ensure table was created
- Verify credentials were entered correctly

**Producer can't login:**
- Admin must approve first
- Check status in admin dashboard

**Session not persisting:**
- Check if cookies are enabled
- Verify PHP session handler working
- Clear browser cache

**Database errors:**
- Ensure MySQL is running
- Check database credentials in `db_config.php`
- Verify all tables created via `db_init.php`

## Future Enhancements

- Email notifications for important events
- Password reset functionality
- Two-factor authentication
- Producer analytics and reports
- Customer purchase history
- Coupon and discount system
- Product reviews from customers
- Automated loyalty point expiration
- Producer approval notifications
- Order status tracking

## License

Educational project. Feel free to modify and extend as needed.

---

**Last Updated:** March 2026
**Version:** 2.0 (with Dashboard System)
