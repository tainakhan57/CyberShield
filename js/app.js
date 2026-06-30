// ============================================================
//  CyberShield — Global Configuration
//  Change API_BASE here and it updates everywhere
// ============================================================

const API_BASE = 'http://localhost/CyberShield/api';

// Auth helpers
const Auth = {
    getUser: () => JSON.parse(sessionStorage.getItem('user') || 'null'),
    setUser: (user) => sessionStorage.setItem('user', JSON.stringify(user)),
    logout: () => {
        sessionStorage.removeItem('user');
        window.location.href = 'login.html';
    },
    require: () => {
        const user = Auth.getUser();
        if (!user) window.location.href = 'login.html';
        return user;
    }
};

// Display logged-in user in navbar
function renderNavUser() {
    const user = Auth.getUser();
    const el = document.getElementById('nav-username');
    if (el && user) {
        el.textContent = `${user.Role} · ${user.FirstName} ${user.LastName}`;
    }
}

// Toast notification
function showToast(msg, type = 'info') {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(c);
    }
    const colors = { success:'#00ff88', error:'#ff003c', info:'#00f5ff', warning:'#ffc800' };
    const icons  = { success:'✓', error:'✕', info:'ℹ', warning:'⚠' };
    const t = document.createElement('div');
    t.style.cssText = `background:#0f1620;border:1px solid ${colors[type]}44;color:${colors[type]};padding:12px 20px;border-radius:6px;font-family:'Rajdhani',sans-serif;font-size:0.88rem;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;max-width:320px;`;
    t.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity 0.3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

// Modal helpers
function openModal(id)  { document.getElementById(id)?.classList.add('active');    }
function closeModal(id) { document.getElementById(id)?.classList.remove('active'); }

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('active');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
});

// Counter animation
function animateCounter(el, target, duration = 1200) {
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = Math.floor(current).toLocaleString();
        if (current >= target) clearInterval(timer);
    }, 16);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-count]').forEach(el => {
        setTimeout(() => animateCounter(el, parseInt(el.dataset.count)), 300);
    });
    renderNavUser();
});
