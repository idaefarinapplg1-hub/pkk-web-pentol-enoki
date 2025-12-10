if (!container) return;

// Get order ID from URL
const urlParams = new URLSearchParams(window.location.search);
const orderId = urlParams.get('id');

if (!orderId) {
    container.innerHTML = '<p style="text-align: center; padding: 2rem; color: #f44336;">Order ID tidak ditemukan</p>';
    return;
}

try {
    const result = await api.get(`orders.php?id=${orderId}`);
    
    if (result.success && result.data) {
        const order = result.data.order || result.data;
        const items = result.data.items || [];
        
        // Calculate amounts
        const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.1;
        const shipping = 10000;
        const total = subtotal + tax + shipping;
        
        // Payment info section
        let paymentInfoHTML = '';
        if (order.payment_method === 'ewallet' || order.payment_method === 'transfer') {
            paymentInfoHTML = `
                <div class="order-info-card payment-info">
                    <h2>💳 Informasi Pembayaran</h2>
                    <div class="payment-details">
                        ${order.payment_method === 'ewallet' ? `
                            <div class="info-row">
                                <span>Metode:</span>
                                <strong>E-Wallet (GoPay/Dana)</strong>
                            </div>
                            <div class="info-row">
                                <span>Nomor Tujuan:</span>
                                <strong style="font-size: 1.125rem; color: var(--primary-color);">0812-1423-7478</strong>
                            </div>
                            <div class="info-row">
                                <span>a/n:</span>
                                <strong>rahmisucioktaviani</strong>
                            </div>
                        ` : `
                    
                        `}
                        <div class="info-row">
                            <span>Total Pembayaran:</span>
                            <strong style="font-size: 1.375rem; color: var(--primary-color);">${formatCurrency(total)}</strong>
                        </div>
                    </div>
                    <div class="payment-note">
                        <p><strong>⚠️ Penting:</strong></p>
                        <ul style="margin: 0.5rem 0 0 1.5rem; line-height: 1.8;">
                            <li>Transfer sesuai nominal <strong>EXACT</strong></li>
                            <li>Simpan bukti transfer</li>
                            <li>Konfirmasi pembayaran via WhatsApp: <strong>0812-1423-7478</strong></li>
                            <li>Pesanan diproses setelah pembayaran dikonfirmasi</li>
                        </ul>
                    </div>
                </div>
            `;
        } else if (order.payment_method === 'cod') {
            paymentInfoHTML = `
                <div class="order-info-card payment-info">
                    <h2>💵 Informasi Pembayaran</h2>
                    <div class="payment-details">
                        <div class="info-row">
                            <span>Metode:</span>
                            <strong>COD (Cash on Delivery)</strong>
                        </div>
                        <div class="info-row">
                            <span>Total Pembayaran:</span>
                            <strong style="font-size: 1.375rem; color: var(--primary-color);">${formatCurrency(total)}</strong>
                        </div>
                    </div>
                    <div class="payment-note">
                        <p><strong>ℹ️ Informasi:</strong></p>
                        <ul style="margin: 0.5rem 0 0 1.5rem; line-height: 1.8;">
                            <li>Bayar saat pesanan tiba</li>
                            <li>Siapkan uang pas untuk mempermudah transaksi</li>
                            <li>Periksa pesanan sebelum membayar</li>
                        </ul>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = `
            <div class="order-info-card">
                <h2>📋 Informasi Pesanan</h2>
                <div class="info-row">
                    <span>No. Pesanan:</span>
                    <strong style="color: var(--primary-color);">${order.order_number}</strong>
                </div>
                <div class="info-row">
                    <span>Tanggal Pemesanan:</span>
                    <span>${new Date(order.created_at).toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</span>
                </div>
                <div class="info-row">
                    <span>Status Pesanan:</span>
                    <span class="badge badge-${order.order_status}">${getStatusText(order.order_status)}</span>
                </div>
                <div class="info-row">
                    <span>Status Pembayaran:</span>
                    <span class="badge badge-${order.payment_status || 'pending'}">${getPaymentStatusText(order.payment_status || 'pending')}</span>
                </div>
            </div>
            
            ${paymentInfoHTML}
            
            <div class="order-info-card">
                <h2>📍 Informasi Pengiriman</h2>
                <div class="info-row">
                    <span>Nama Penerima:</span>
                    <strong>${order.customer_name || order.full_name}</strong>
                </div>
                <div class="info-row">
                    <span>Email:</span>
                    <span>${order.customer_email || order.email}</span>
                </div>
                <div class="info-row">
                    <span>No. Telepon:</span>
                    <strong style="color: var(--primary-color);">${order.phone}</strong>
                </div>
                <div class="info-row">
                    <span>Alamat Lengkap:</span>
                    <span style="line-height: 1.6;">${order.shipping_address}</span>
                </div>
                ${order.notes ? `
                <div class="info-row">
                    <span>Catatan:</span>
                    <span style="font-style: italic; color: var(--text-gray);">"${order.notes}"</span>
                </div>
                ` : ''}
            </div>
            
            <div class="order-info-card">
                <h2>🛍️ Detail Pesanan</h2>
                <div class="order-items-list">
                    ${items.length > 0 ? items.map((item, index) => `
                        <div class="order-item-row">
                            <div class="item-info">
                                <span class="item-number">${index + 1}.</span>
                                <div>
                                    <strong>${item.product_name}</strong>
                                    <br>
                                    <small style="color: var(--text-gray);">${item.quantity} × ${formatCurrency(item.price)}</small>
                                </div>
                            </div>
                            <div class="item-total">
                                <strong>${formatCurrency(item.quantity * item.price)}</strong>
                            </div>
                        </div>
                    `).join('') : '<p style="text-align: center; color: var(--text-gray);">Detail item tidak tersedia</p>'}
                </div>
                
                <div class="order-summary-total">
                    <div class="info-row">
                        <span>Subtotal Produk:</span>
                        <span>${formatCurrency(subtotal)}</span>
                    </div>
                    <div class="info-row">
                        <span>Pajak (10%):</span>
                        <span>${formatCurrency(tax)}</span>
                    </div>
                    <div class="info-row">
                        <span>Biaya Pengiriman:</span>
                        <span>${formatCurrency(shipping)}</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="info-row total">
                        <span><strong>Total Pembayaran:</strong></span>
                        <strong style="font-size: 1.5rem;">${formatCurrency(total)}</strong>
                    </div>
                </div>
            </div>
            
            <div class="order-info-card contact-support">
                <h2>📞 Butuh Bantuan?</h2>
                <p style="margin-bottom: 1rem; color: var(--text-gray);">Hubungi kami jika ada pertanyaan tentang pesanan Anda:</p>
                <div class="info-row">
                    <span>WhatsApp:</span>
                    <strong style="color: var(--primary-color);">0812-1423-7478</strong>
                </div>
                <div class="info-row">
                    <span>Email:</span>
                    <span>rahmisucioktaviani534@gmail.com</span>
                </div>
                <div class="info-row">
                    <span>Jam Operasional:</span>
                    <span>Senin - Minggu, 08:00 - 22:00 WIB</span>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = '<p style="text-align: center; padding: 2rem; color: #f44336;">Pesanan tidak ditemukan</p>';
    }
} catch (error) {
    console.error('Error loading order:', error);
    container.innerHTML = '<p style="text-align: center; padding: 2rem; color: #f44336;">Terjadi kesalahan saat memuat pesanan</p>';
}
