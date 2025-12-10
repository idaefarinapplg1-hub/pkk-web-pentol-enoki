/**
 * Menu Page Script
 */

let allProducts = [];

async function loadProducts() {
    const container = document.getElementById('products-container');
    
    if (!container) {
        console.error('Container not found!');
        return;
    }
    
    try {
        const result = await api.get('products.php');
        
        if (result.success && result.data && Array.isArray(result.data)) {
            allProducts = result.data.filter(p => p.is_available == 1 && p.is_archived == 0);
            
            if (allProducts.length > 0) {
                displayProducts(allProducts);
                loadCategories();
            } else {
                container.innerHTML = '<p style="text-align: center; padding: 2rem;">Belum ada produk tersedia.</p>';
            }
        } else {
            container.innerHTML = '<p style="text-align: center; padding: 2rem;">Gagal memuat produk.</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<p style="text-align: center; padding: 2rem;">Terjadi kesalahan.</p>';
    }
}

function displayProducts(products) {
    const container = document.getElementById('products-container');
    
    if (!products || products.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 2rem;">Tidak ada produk tersedia</p>';
        return;
    }
    
    container.innerHTML = products.map(product => `
        <div class="product-card">
            <img src="${product.image_url || '../assets/placeholder.jpg'}" 
                 alt="${product.product_name}" 
                 class="product-image"
                 onerror="this.src='../assets/placeholder.jpg'">
            <div class="product-info">
                <h3 class="product-name">${product.product_name}</h3>
                <p class="product-description">${product.description || ''}</p>
                <div class="product-price">${formatCurrency(product.price)}</div>
                <p style="color: #666; font-size: 0.9rem;">Stok: ${product.stock}</p>
                <div class="product-actions">
                    <button class="btn btn-primary" onclick="addToCart(${product.product_id})">
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

async function loadCategories() {
    const categories = [...new Set(allProducts.map(p => p.category_name).filter(Boolean))];
    const select = document.getElementById('categoryFilter');
    
    if (select) {
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            select.appendChild(option);
        });
        
        select.addEventListener('change', (e) => {
            const filtered = e.target.value 
                ? allProducts.filter(p => p.category_name === e.target.value)
                : allProducts;
            displayProducts(filtered);
        });
    }
}

async function addToCart(productId) {
    try {
        const result = await api.get(`products.php?id=${productId}`);
        
        if (result.success && result.data) {
            cart.add(result.data);
            showNotification('Produk ditambahkan ke keranjang!', 'success');
        } else {
            showNotification('Gagal menambahkan produk', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
});
