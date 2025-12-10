/**
 * Orders Page Script
 */

async function loadOrders() {
    const container = document.getElementById('orders-list');
    
    try {
        const result = await api.get('orders.php');
        
        if (result.success) {
            if (result.data.length === 0) {
                container.innerHTML = '<p>Belum ada pesanan</p>';
                return;
            }
            
            container.innerHTML = result.data.map(order => `
                <div class="cart-item" style="cursor: pointer;" onclick="viewOrder(${order.order_id})">
                    <div class="cart-item-info" style="flex: 1;">
                        <h3>Order #${order.order_number}</h3>
                        <p>Tanggal: ${new Date(order.created_at).toLocaleDateString('id-ID')}</p>
                        <p>Total: ${formatCurrency(order.total_amount)}</p>
                        <p>Pembayaran: ${order.payment_method.toUpperCase()}</p>
                    </div>
                    <div>
                        <span class="badge badge-${order.order_status}">${order.order_status}</span>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        container.innerHTML = '<p>Gagal memuat pesanan</p>';
    }
}

function viewOrder(orderId) {
    window.location.href = `order-detail.html?id=${orderId}`;
}

loadOrders();
