# Ordering System - Complete Setup Guide

A simple but functional ordering system built with PHP, JavaScript, and MySQL.

## Features

✅ **Product Listing** - Browse products from database
✅ **Shopping Cart** - Add/remove items, manage quantities (localStorage)
✅ **Cart Management** - Increase/decrease quantity, remove items
✅ **Order Processing** - Send cart data to backend via AJAX
✅ **Database Storage** - Save orders and order items to MySQL
✅ **Responsive Design** - Works on desktop and mobile

## Project Structure

```
ordering system/
├── index.php              # Product listing page
├── cart.php               # Shopping cart page
├── db_config.php          # Database connection configuration
├── db_init.php            # Database initialization (create tables + sample data)
├── api/
│   └── checkout.php       # Checkout endpoint (API)
├── js/
│   └── cart.js            # Cart management JavaScript
├── css/
│   └── style.css          # All styles
└── README.md              # This file
```

## Database Schema

### products
```sql
- id (INT, Primary Key, Auto Increment)
- name (VARCHAR 100)
- price (DECIMAL 10,2)
- image (VARCHAR 255)
```

### orders
```sql
- id (INT, Primary Key, Auto Increment)
- created_at (TIMESTAMP, default: CURRENT_TIMESTAMP)
```

### order_items
```sql
- id (INT, Primary Key, Auto Increment)
- order_id (INT, Foreign Key)
- product_id (INT, Foreign Key)
- quantity (INT)
```

## Installation & Setup

### Step 1: Prerequisites
- XAMPP (or any PHP/MySQL server)
- PHP 7.0+ 
- MySQL 5.7+

### Step 2: Create Database
1. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
2. Run the initialization file to create database and tables:
   - Visit: `http://localhost/template/template/ordering%20system/db_init.php`
   - This will:
     - Create `ordering_system` database
     - Create tables: `products`, `orders`, `order_items`
     - Insert 6 sample products (Laptop, Mouse, Keyboard, Monitor, Headphones, Webcam)

### Step 3: Access the Application
- **Product Listing**: `http://localhost/template/template/ordering%20system/`
- **Shopping Cart**: `http://localhost/template/template/ordering%20system/cart.php`

## How to Use

### For Users/Customers

1. **Browse Products**: Visit the main page to see all available products
2. **Add to Cart**: Click "Add to Cart" button on any product
   - If item already in cart, quantity increases automatically
   - Notification appears confirming item was added
3. **View Cart**: Click "Cart" in navigation
4. **Manage Cart**:
   - Use +/- buttons to change quantity
   - Click "Remove" to delete an item
   - View cart total with 10% tax calculation
5. **Checkout**: Click "Proceed to Checkout"
   - Order is sent to server and saved to database
   - Cart is cleared automatically
   - Success message is displayed

### Cart Features

- **localStorage Storage**: Cart persists even after page refresh
- **Real-time Updates**: Cart count updates immediately in navigation
- **Tax Calculation**: Automatically calculates 10% tax on subtotal
- **Validation**: Prevents checkout with empty cart

## Code Overview

### Frontend (JavaScript - cart.js)

**Cart Functions:**
- `getCart()` - Retrieve cart from localStorage
- `saveCart(cart)` - Save cart to localStorage
- `addToCart(id, name, price)` - Add item to cart
- `removeFromCart(id)` - Remove item from cart
- `updateQuantity(id, quantity)` - Update item quantity
- `calculateTotals()` - Calculate subtotal, tax, total
- `displayCart()` - Render cart on cart page
- `checkout()` - Send cart data to server via fetch
- `updateCartCount()` - Update cart count in navigation

### Backend (PHP)

**db_config.php**
- Database connection configuration
- Reusable connection object

**db_init.php**
- Creates database if not exists
- Creates all required tables
- Inserts sample products
- Run-once initialization

**api/checkout.php**
- Receives cart data as JSON
- Validates cart items
- Starts database transaction
- Creates new order record
- Inserts order items
- Commits transaction on success
- Returns JSON response

**index.php**
- Fetches all products from database
- Displays them in responsive grid
- Includes navigation and styling

**cart.php**
- Contains HTML structure for cart page
- JavaScript renders cart contents dynamically
- Shows checkout form and controls

## Key Concepts

### Cart Storage (localStorage)
Cart is stored in browser's localStorage as JSON:
```javascript
[
  {id: 1, name: "Laptop", price: 999.99, quantity: 1},
  {id: 2, name: "Mouse", price: 29.99, quantity: 2}
]
```

### AJAX Checkout
Cart is sent to server using fetch API:
```javascript
fetch('api/checkout.php', {
  method: 'POST',
  body: JSON.stringify({cart: cartItems})
})
```

### Database Transactions
Checkout uses transactions to ensure data integrity:
- If order creation succeeds but item insertion fails, entire transaction rolls back
- Prevents partial orders being saved

## Customization

### Change Database Credentials
Edit `db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ordering_system');
```

### Change Tax Rate
Edit `api/checkout.php` or `js/cart.js`:
```javascript
const tax = subtotal * 0.10; // Change 0.10 to your tax rate
```

### Modify Product Display
Edit `css/style.css` to adjust grid layout:
```css
grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Change minmax size */
```

### Add More Products
Insert into database via phpMyAdmin or add to `db_init.php`:
```sql
INSERT INTO products (name, price, image) VALUES 
('Product Name', 99.99, 'image.jpg');
```

## Bonus Features Implemented

✅ **Quantity Controls** - Use +/- buttons to adjust quantity
✅ **Input Validation** - Prevents invalid quantities, empty carts
✅ **Clear Cart** - Clear all items with confirmation
✅ **Success Confirmation** - Shows order confirmation page
✅ **Error Handling** - AJAX error handling with user feedback
✅ **Notification** - Toast notifications for user actions
✅ **Transaction Safety** - Database transactions prevent data loss

## Testing Checklist

- [ ] Visit db_init.php and database initializes without errors
- [ ] Products display on home page
- [ ] Can add product to cart, quantity increases if already in cart
- [ ] Cart count in navigation updates correctly
- [ ] Cart page displays all items with correct totals
- [ ] Can adjust quantities with +/- buttons
- [ ] Can remove items from cart
- [ ] Clear cart asks for confirmation
- [ ] Checkout sends data to server
- [ ] Order is saved in database (check phpMyAdmin)
- [ ] Cart clears after successful checkout
- [ ] Success message displays
- [ ] Can place multiple orders

## Troubleshooting

**Database Connection Error**
- Check if MySQL is running
- Verify credentials in `db_config.php`
- Ensure `ordering_system` database exists

**Checkout Fails**
- Check browser console for JavaScript errors
- Check if `api/checkout.php` is accessible
- Verify database connection in checkout endpoint

**Cart Not Persisting**
- Check if localStorage is enabled in browser
- Check browser's Application tab (Dev Tools)
- Clear browser cache and try again

**Products Not Displaying**
- Run `db_init.php` to initialize database
- Check if products table is populated
- Verify database connection in `index.php`

## Future Enhancements

- User authentication and account management
- Product categories and filtering
- Search functionality
- Product reviews and ratings
- Payment gateway integration
- Order history and tracking
- Admin panel for product management
- Email order confirmations
- Inventory management
- Customer notifications

## License

This is an educational project. Feel free to use and modify as needed.

---

**Created**: March 2026
**Version**: 1.0
