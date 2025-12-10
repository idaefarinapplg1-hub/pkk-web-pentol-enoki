/**
 * Siap Santap - Main JavaScript Application
 */

const API_BASE = '../../backend/api';

// Utility Functions
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

const api = {
    async request(endpoint, options = {}) {
        try {
            const url = `${API_BASE}/${endpoint}`;
            
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error: ' + error.message };
        }
    },
    
    get(endpoint) {
        return this.request(endpoint);
    },
    
    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// Cart Management
class Cart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
    }
    
    add(product, quantity = 1) {
        const existing = this.items.find(item => item.product_id === product.product_id);
        
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({ ...product, quantity });
        }
        
        this.save();
        this.updateUI();
    }
    
    remove(productId) {
        this.items = this.items.filter(item => item.product_id !== productId);
        this.save();
        this.updateUI();
    }
    
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.product_id === productId);
        if (item) {
            item.quantity = quantity;
            if (quantity <= 0) {
                this.remove(productId);
            } else {
                this.save();
                this.updateUI();
            }
        }
    }
    
    clear() {
        this.items = [];
        this.save();
        this.updateUI();
    }
    
    getTotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }
    
    save() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }
    
    updateUI() {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            const count = this.items.length;
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    }
}

const cart = new Cart();

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 2rem;
        background: ${type === 'success' ? '#4CAF50' : '#F44336'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 3000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    cart.updateUI();
    checkAuth();
});

async function checkAuth() {
    try {
        const result = await api.get('auth.php?action=check');
        if (result.logged_in) {
            updateAuthUI(result.user);
        }
    } catch (error) {
        console.error('Auth check failed:', error);
    }
}

function updateAuthUI(user) {
    const authLinks = document.querySelector('.auth-links');
    if (authLinks && user) {
        authLinks.innerHTML = `
            <span>Welcome, ${user.full_name}</span>
            <a href="orders.html">My Orders</a>
            ${user.role === 'admin' ? '<a href="admin.html">Admin</a>' : ''}
            <a href="#" onclick="logout()">Logout</a>
        `;
    }
}

async function logout() {
    await api.post('auth.php?action=logout');
    localStorage.clear();
    window.location.href = 'index.html';
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        navLinks.classList.toggle('active');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    const nav = document.querySelector('nav');
    const navLinks = document.querySelector('.nav-links');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (navLinks && navLinks.classList.contains('active')) {
        if (!nav.contains(e.target) || (!toggle.contains(e.target) && !navLinks.contains(e.target))) {
            navLinks.classList.remove('active');
        }
    }
});
