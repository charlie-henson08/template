# Quick Start Guide - Dashboard System

Get up and running in 5 minutes!

## Step 1: Initialize Database (IMPORTANT)

Visit this URL in your browser to create all necessary tables and load demo data:

```
http://localhost/template/template/ordering%20system/db_init.php
```

You should see:
```
✓ Products table ready
✓ Orders table ready
✓ Order_items table ready
✓ Users table ready
✓ Producers table ready
✓ Admins table ready
✓ Loyalty_transactions table ready
✓ Sample products inserted
✓ Demo admin created
✓ Demo customer created
✓ Demo producer created
✓ Database initialized successfully!
```

## Step 2: Access the System

Main site: `http://localhost/template/template/ordering%20system/`

## Step 3: Login with Demo Accounts

### Test as Customer
1. Click "Login"
2. Select "Customer" tab
3. **Email:** `customer@store.com`
4. **Password:** `password`
5. You'll see your Loyalty Dashboard with 500 starting points

### Test as Producer  
1. Click "Login"
2. Select "Producer" tab
3. **Email:** `producer@store.com`
4. **Password:** `password`
5. View your products, stock, and orders

### Test as Admin
1. Click "Login"
2. Select "Admin" tab
3. **Email:** `admin@store.com`
4. **Password:** `admin123`
5. Manage producers (approve/reject/suspend)

## Step 4: Test Complete Flow

### As Customer:
1. Login
2. Click "Products" in navigation
3. Add items to cart
4. Go to "Cart" 
5. Click "Proceed to Checkout"
6. See order confirmation

### As Producer:
1. Login as producer
2. Click "Producer Dashboard"
3. View your products in "Products" tab
4. Click "Edit" to change product details
5. Check "Stock" tab to see inventory
6. Check "Orders" tab to see purchases

### As Admin:
1. Login as admin
2. Click "Admin Dashboard"
3. See producer statistics
4. Approve/Deactivate/Suspend producers

## File Locations

After running `db_init.php`, you'll have:

**Core Files:**
- `index.php` - Product listing
- `cart.php` - Shopping cart
- `db_init.php` - Database setup (run once)

**Authentication:**
- `auth/login.php` - Login page
- `auth/register.php` - Registration
- `auth/session.php` - Session management
- `auth/logout.php` - Logout

**Dashboards:**
- `dashboards/dashboard.php` - Router
- `dashboards/customer_dashboard.php` - Loyalty & profile
- `dashboards/producer_dashboard.php` - Product management
- `dashboards/admin_dashboard.php` - Producer management

**APIs:**
- `api/checkout.php` - Checkout endpoint

**Styling:**
- `css/style.css` - All styles
- `js/cart.js` - Shopping cart logic

## Key Features

### Customer Dashboard
- ✅ Loyalty points tracker
- ✅ Progress bar to next reward
- ✅ Transaction history
- ✅ Profile information

### Producer Dashboard
- ✅ Edit product descriptions
- ✅ Manage stock levels
- ✅ View orders
- ✅ Edit pricing

### Admin Dashboard
- ✅ View producer applications
- ✅ Approve/reject producers
- ✅ Suspend bad actors
- ✅ View statistics

## Demo Data

**Demo Customers:**
- customer@store.com / password (500 loyalty points)

**Demo Producers:**
- producer@store.com / password (active, owns 3 sample products)

**Demo Admins:**
- admin@store.com / admin123

**Sample Products:**
- Laptop ($999.99)
- Mouse ($29.99)
- Keyboard ($79.99)
- Monitor ($299.99)
- Headphones ($129.99)
- Webcam ($89.99)

## Common Tasks

### Reset Everything
1. Delete the `ordering_system` database in phpMyAdmin
2. Visit `db_init.php` again
3. All tables and demo data recreated

### Add a New Customer
1. Click "Register"
2. Select "Customer"
3. Fill in info
4. Login immediately

### Add a New Producer
1. Click "Register"
2. Select "Producer"
3. Fill in info + company name
4. Status: Inactive (awaiting approval)
5. Login as admin
6. Find producer and click "Approve"

### Change Loyalty Points
1. In database directly, update `users` table
2. Or add transaction in `loyalty_transactions` table

## Default Ports

- **HTTP:** http://localhost/template/template/ordering%20system/
- **phpMyAdmin:** http://localhost/phpmyadmin

## Troubleshooting

**Error: "Unknown database"**
- Run `db_init.php` first

**"Password is incorrect"**
- Double-check email matches exactly
- Default passwords are case-sensitive
- Customer/Producer: `password`
- Admin: `admin123`

**Products don't show**
- Database not initialized
- Run `db_init.php`

**Can't login as producer**
- Status must be "active"
- Ask admin to approve if "inactive"
- Admin can't be deactivated (only approve others)

**Loyalty points not updating**
- Currently manual (not auto-added after purchase)
- Can be added manually via database

## Next Steps

For more detailed information, see:
- [DASHBOARD_GUIDE.md](DASHBOARD_GUIDE.md) - Complete feature documentation
- [README.md](README.md) - Original system guide

Enjoy! 🚀
