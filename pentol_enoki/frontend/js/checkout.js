/**
 * Checkout Page Script
 */

// Track selected items
let selectedItems = new Set();

function displayCheckoutItems() {
    const container = document.getElementById('checkout-items');
    
    if (!container) return;
    
    if (cart.items.length === 0) {
        window.location.href = 'cart.html';
        return;
    }
    
    // Initialize all items as selected
    selectedItems = new Set(cart.items.map(item => item.product_id));
    
    container.innerHTML = cart.items.map(item => `
        <div class="checkout-item" id="item-${item.product_id}">
            <label class="checkbox-container">
                <input type="checkbox" 
                       class="item-checkbox" 
                       data-product-id="${item.product_id}"
                       onchange="toggleItemSelection(${item.product_id}, this.checked)"
                       checked>
                <span class="checkmark"></span>
            </label>
            <img src="${item.image_url || '../assets/placeholder.jpg'}" 
                 alt="${item.product_name}"
                 onerror="this.src='../assets/placeholder.jpg'">
            <div class="checkout-item-info">
                <h4>${item.product_name}</h4>
                <p class="item-quantity">${item.quantity} x ${formatCurrency(item.price)}</p>
            </div>
            <div class="checkout-item-total">
                <strong>${formatCurrency(item.price * item.quantity)}</strong>
            </div>
        </div>
    `).join('');
    
    updateSelectAllCheckbox();
    updateCheckoutSummary();
}

function toggleItemSelection(productId, isSelected) {
    if (isSelected) {
        selectedItems.add(productId);
    } else {
        selectedItems.delete(productId);
    }
    
    updateSelectAllCheckbox();
    updateCheckoutSummary();
    updateSubmitButton();
}

function toggleSelectAll(isChecked) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
        const productId = parseInt(checkbox.dataset.productId);
        
        if (isChecked) {
            selectedItems.add(productId);
        } else {
            selectedItems.delete(productId);
        }
    });
    
    updateCheckoutSummary();
    updateSubmitButton();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all-items');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    }
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submit-order-btn');
    if (submitBtn) {
        submitBtn.disabled = selectedItems.size === 0;
    }
}

function updateCheckoutSummary() {
    // Calculate only for selected items
    const selectedCartItems = cart.items.filter(item => selectedItems.has(item.product_id));
    const subtotal = selectedCartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.1;
    const shipping = selectedItems.size > 0 ? 10000 : 0; // Flat shipping fee
    const total = subtotal + tax + shipping;
    
    // Update selected items count
    const countElement = document.getElementById('selected-items-count');
    if (countElement) {
        countElement.innerHTML = `<p>📦 <strong>${selectedItems.size}</strong> item dipilih</p>`;
    }
    
    document.getElementById('checkout-subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('checkout-tax').textContent = formatCurrency(tax);
    document.getElementById('checkout-shipping').textContent = formatCurrency(shipping);
    document.getElementById('checkout-total').textContent = formatCurrency(total);
}

async function processCheckout(event) {
    event.preventDefault();
    
    // Check if any items are selected
    if (selectedItems.size === 0) {
        showNotification('❌ Pilih minimal 1 produk untuk checkout', 'error');
        return;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        showNotification('❌ Mohon lengkapi semua field yang wajib diisi', 'error');
        return;
    }
    
    // Validate payment method
    if (!formData.get('payment_method')) {
        showNotification('❌ Pilih metode pembayaran', 'error');
        return;
    }
    
    // Filter only selected items
    const selectedCartItems = cart.items.filter(item => selectedItems.has(item.product_id));
    
    if (selectedCartItems.length === 0) {
        showNotification('❌ Tidak ada produk yang dipilih', 'error');
        return;
    }
    
    const subtotal = selectedCartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.1;
    const shipping = 10000;
    const total = subtotal + tax + shipping;
    
    // Prepare order data
    const orderData = {
        full_name: formData.get('full_name')?.trim() || '',
        email: formData.get('email')?.trim() || '',
        phone: formData.get('phone')?.trim() || '',
        address: formData.get('address')?.trim() || '',
        city: formData.get('city')?.trim() || '',
        postal_code: formData.get('postal_code')?.trim() || '',
        payment_method: formData.get('payment_method') || '',
        notes: formData.get('notes')?.trim() || '',
        items: selectedCartItems.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.price
        })),
        subtotal: subtotal,
        tax: tax,
        shipping: shipping,
        total: total
    };
    
    // Validate required fields
    if (!orderData.full_name || !orderData.email || !orderData.phone || 
        !orderData.address || !orderData.city || !orderData.postal_code) {
        showNotification('❌ Mohon lengkapi semua informasi pengiriman', 'error');
        return;
    }
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Memproses pesanan...';
    
    try {
        console.log('Sending order data:', orderData);
        const result = await api.post('orders.php', orderData);
        console.log('Order result:', result);
        
        if (result.success) {
            // Remove selected items from cart
            selectedCartItems.forEach(item => {
                cart.remove(item.product_id);
            });
            
            // Show success message
            showNotification('✅ Pesanan berhasil dibuat!', 'success');
            
            // Redirect to order detail or success page
            setTimeout(() => {
                if (result.order_id) {
                    window.location.href = `order-detail.html?id=${result.order_id}`;
                } else {
                    window.location.href = 'orders.html';
                }
            }, 1500);
        } else {
            showNotification('❌ ' + (result.message || 'Gagal membuat pesanan'), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Checkout error:', error);
        showNotification('❌ Terjadi kesalahan saat memproses pesanan', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    displayCheckoutItems();
    updateSubmitButton();
    
    // Setup form handler
    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', processCheckout);
    }
});
