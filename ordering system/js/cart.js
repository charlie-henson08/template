// Cart Management Functions

const CART_STORAGE_KEY = 'shopping_cart';

/**
 * Get cart from localStorage
 * @returns {Array} Cart items array
 */
function getCart() {
    const cart = localStorage.getItem(CART_STORAGE_KEY);
    return cart ? JSON.parse(cart) : [];
}

/**
 * Save cart to localStorage
 * @param {Array} cart - Cart items array
 */
function saveCart(cart) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    updateCartCount();
}

/**
 * Add item to cart
 * @param {number} id - Product ID
 * @param {string} name - Product name
 * @param {number} price - Product price
 */
function addToCart(id, name, price) {
    let cart = getCart();
    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        // If item exists, increase quantity
        existingItem.quantity += 1;
    } else {
        // Add new item
        cart.push({
            id: id,
            name: name,
            price: parseFloat(price),
            quantity: 1
        });
    }

    saveCart(cart);
    showNotification('Added to cart!');
}

/**
 * Remove item from cart
 * @param {number} id - Product ID
 */
function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    saveCart(cart);
    displayCart();
}

/**
 * Update item quantity
 * @param {number} id - Product ID
 * @param {number} quantity - New quantity
 */
function updateQuantity(id, quantity) {
    let cart = getCart();
    const item = cart.find(item => item.id === id);

    if (item) {
        if (quantity <= 0) {
            removeFromCart(id);
        } else {
            item.quantity = parseInt(quantity);
            saveCart(cart);
            displayCart();
        }
    }
}

/**
 * Calculate cart totals
 * @returns {Object} Object with subtotal, tax, and total
 */
function calculateTotals() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.10; // 10% tax
    const total = subtotal + tax;

    return {
        subtotal: subtotal.toFixed(2),
        tax: tax.toFixed(2),
        total: total.toFixed(2)
    };
}

/**
 * Display cart items on cart page
 */
function displayCart() {
    const cart = getCart();
    const cartContainer = document.getElementById('cart-container');
    const checkoutContainer = document.getElementById('checkout-container');

    if (!cartContainer) return; // Not on cart page

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="empty-cart">Your cart is empty. <a href="index.php">Continue shopping</a></p>';
        if (checkoutContainer) {
            checkoutContainer.style.display = 'none';
        }
        return;
    }

    // Display cart items
    let html = '<table class="cart-table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr></thead><tbody>';

    cart.forEach(item => {
        const itemTotal = (item.price * item.quantity).toFixed(2);
        html += `
            <tr>
                <td>${escapeHtml(item.name)}</td>
                <td>$${parseFloat(item.price).toFixed(2)}</td>
                <td>
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </td>
                <td>$${itemTotal}</td>
                <td><button class="btn btn-danger" onclick="removeFromCart(${item.id})">Remove</button></td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    cartContainer.innerHTML = html;

    // Update totals and show checkout
    const totals = calculateTotals();
    document.getElementById('subtotal').textContent = totals.subtotal;
    document.getElementById('tax').textContent = totals.tax;
    document.getElementById('total').textContent = totals.total;

    if (checkoutContainer) {
        checkoutContainer.style.display = 'block';
    }
}

/**
 * Update cart count in navigation
 */
function updateCartCount() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(el => {
        el.textContent = totalItems;
    });
}

/**
 * Clear cart
 */
function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        localStorage.removeItem(CART_STORAGE_KEY);
        updateCartCount();
        displayCart();
        showNotification('Cart cleared');
    }
}

/**
 * Checkout - Send cart data to server
 */
function checkout() {
    const cart = getCart();

    if (cart.length === 0) {
        alert('Your cart is empty');
        return;
    }

    // Show loading state
    const checkoutBtn = document.getElementById('checkout-btn');
    const originalText = checkoutBtn.textContent;
    checkoutBtn.textContent = 'Processing...';
    checkoutBtn.disabled = true;

    // Send cart data to server
    fetch('api/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            clearLocalCart();
            updateCartCount();
            
            // Show success message
            document.getElementById('checkout-container').style.display = 'none';
            document.getElementById('cart-container').innerHTML = '';
            document.getElementById('success-message').style.display = 'block';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        checkoutBtn.textContent = originalText;
        checkoutBtn.disabled = false;
    });
}

/**
 * Clear cart from localStorage
 */
function clearLocalCart() {
    localStorage.removeItem(CART_STORAGE_KEY);
}

/**
 * Escape HTML special characters
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Show notification message
 * @param {string} message - Message to display
 */
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Event listeners for product listing page
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();

    // Add event listeners to "Add to Cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            const name = this.dataset.name;
            const price = this.dataset.price;
            addToCart(id, name, price);
        });
    });

    // Add event listener to checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', checkout);
    }

    // Add event listener to clear cart button
    const clearCartBtn = document.getElementById('clear-cart-btn');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearCart);
    }
});
