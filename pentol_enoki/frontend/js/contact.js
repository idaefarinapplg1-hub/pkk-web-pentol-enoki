/**
 * Contact Page Script
 */

const contactForm = document.getElementById('contactForm');
contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(contactForm);
    const data = Object.fromEntries(formData);
    
    const result = await api.post('messages.php', data);
    
    if (result.success) {
        showNotification('Pesan berhasil dikirim!');
        contactForm.reset();
        loadMyMessages();
    } else {
        showNotification(result.message, 'error');
    }
});

async function loadMyMessages() {
    const result = await api.get('messages.php');
    const container = document.getElementById('my-messages');
    
    if (result.success) {
        if (result.data.length === 0) {
            container.innerHTML = '<p style="text-align: center;">Belum ada pesan</p>';
            return;
        }
        
        container.innerHTML = result.data.map(msg => `
            <div style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; box-shadow: var(--shadow);">
                <h3>${msg.subject}</h3>
                <p>${msg.message}</p>
                <p style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                    ${new Date(msg.created_at).toLocaleString('id-ID')}
                </p>
                <span class="badge badge-${msg.status === 'replied' ? 'delivered' : 'pending'}">${msg.status}</span>
                ${msg.admin_reply ? `
                    <div style="background: var(--bg-light); padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                        <strong>Balasan Admin:</strong>
                        <p>${msg.admin_reply}</p>
                        <p style="color: #666; font-size: 0.9rem;">
                            ${new Date(msg.replied_at).toLocaleString('id-ID')}
                        </p>
                    </div>
                ` : ''}
            </div>
        `).join('');
    }
}

loadMyMessages();
