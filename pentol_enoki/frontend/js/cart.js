/**
 * Cart Page Script
 */

function displayCart() {
    const container = document.getElementById('cart-items');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (!container) return;
    
    if (cart.items.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 1rem;">Keranjang belanja Anda kosong</p>
                <a href="menu.html" class="btn btn-primary">Mulai Belanja</a>
            </div>
        `;
        checkoutBtn.disabled = true;
        updateSummary();
        return;
    }
    
    checkoutBtn.disabled = false;
    
    container.innerHTML = cart.items.map(item => `
        <div class="cart-item">
            <img src="${item.image_url || '../assets/placeholder.jpg'}" 
                 alt="${item.product_name}"
                 onerror="this.src='../assets/placeholder.jpg'">
            <div class="cart-item-info">
                <h3>${item.product_name}</h3>
                <p class="cart-item-price">${formatCurrency(item.price)}</p>
            </div>
            <div class="cart-item-quantity">
                <button class="qty-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                <input type="number" value="${item.quantity}" min="1" 
                       onchange="updateQuantity(${item.product_id}, this.value)"
                       class="qty-input">
                <button class="qty-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
            </div>
            <div class="cart-item-total">
                <p>${formatCurrency(item.price * item.quantity)}</p>
            </div>
            <button class="cart-item-remove" onclick="removeItem(${item.product_id})" title="Hapus">
                ✕
            </button>
        </div>
    `).join('');
    
    updateSummary();
}

function updateQuantity(productId, quantity) {
    quantity = parseInt(quantity);
    if (quantity < 1) {
        if (confirm('Hapus produk dari keranjang?')) {
            cart.remove(productId);
        }
    } else {
        cart.updateQuantity(productId, quantity);
    }
    displayCart();
}

function removeItem(productId) {
    if (confirm('Hapus produk dari keranjang?')) {
        cart.remove(productId);
        displayCart();
        showNotification('Produk dihapus dari keranjang', 'success');
    }
}

function updateSummary() {
    const subtotal = cart.getTotal();
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('tax').textContent = formatCurrency(tax);
    document.getElementById('total').textContent = formatCurrency(total);
}

function clearCart() {
    if (confirm('Kosongkan semua item dari keranjang?')) {
        cart.clear();
        displayCart();
        showNotification('Keranjang dikosongkan', 'success');
    }
}

function checkout() {
    if (cart.items.length === 0) {
        showNotification('Keranjang kosong', 'error');
        return;
    }
    window.location.href = 'checkout.html';
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    displayCart();
});
