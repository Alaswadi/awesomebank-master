/* =====================================================
   AwesomeBank - Shared Application JavaScript
   ===================================================== */

const CUSTOMER_MENU = (userId) => [
  { label: 'Dashboard',      icon: 'fa-home',          href: 'dashboard.html',                         id: 'dashboard' },
  { label: 'My Accounts',    icon: 'fa-wallet',         href: `accountOverview.html?user_id=${userId}`, id: 'accountOverview' },
  { label: 'Money Transfer', icon: 'fa-paper-plane',    href: `moneyTransfer.html?user_id=${userId}`,   id: 'moneyTransfer' },
  { label: 'Statements',     icon: 'fa-file-alt',       href: `customerStatement.html?user_id=${userId}`,id: 'customerStatement' },
  { label: 'Credit Card',    icon: 'fa-credit-card',    href: `creditCard.html?user_id=${userId}`,      id: 'creditCard' },
  { label: 'Bill Payment',   icon: 'fa-receipt',        href: `billPayment.html?user_id=${userId}`,     id: 'billPayment' },
  { label: '2FA Settings',   icon: 'fa-shield-alt',     href: 'twoFactorAuth.html',                     id: 'twoFactorAuth' },
  { label: 'Edit Profile',   icon: 'fa-user-edit',      href: `editProfile.html?user_id=${userId}`,     id: 'editProfile' },
  { label: 'Preferences',    icon: 'fa-sliders-h',      href: 'preferences.html',                       id: 'preferences' },
  { label: 'Reset Password', icon: 'fa-key',            href: 'resetPassword.html',                     id: 'resetPassword' },
];

const ADMIN_MENU = [
  { label: 'Dashboard',        icon: 'fa-home',             href: 'dashboard.html',           id: 'dashboard' },
  { label: 'Manage Accounts',  icon: 'fa-university',       href: 'manageAccounts.html',      id: 'manageAccounts' },
  { label: 'Manage Users',     icon: 'fa-users',            href: 'manageuser.html',          id: 'manageuser' },
  { label: 'Exchange Rates',   icon: 'fa-exchange-alt',     href: 'fetchExchangeRates.html',  id: 'fetchExchangeRates' },
  { label: 'Upload Pictures',  icon: 'fa-cloud-upload-alt', href: 'uploadPictures.html',      id: 'uploadPictures' },
  { label: 'System Settings',  icon: 'fa-cog',              href: 'systemSettings.html',      id: 'systemSettings' },
  { label: 'Network Diagnostics', icon: 'fa-network-wired', href: 'ping.html',                id: 'ping' },
  { label: 'Import Transactions', icon: 'fa-file-import',   href: 'importTransactions.html',  id: 'importTransactions' },
];

function getCurrentPage() {
  const path = window.location.pathname;
  return path.split('/').pop().replace('.html', '') || 'index';
}

function buildSidebar(username, role, userId) {
  const menu = role === 'admin' ? ADMIN_MENU : CUSTOMER_MENU(userId);
  const currentPage = getCurrentPage();

  const navItems = menu.map(item => `
    <a href="${item.href}" class="nav-item${currentPage === item.id ? ' active' : ''}">
      <i class="fas ${item.icon}"></i>
      <span>${item.label}</span>
    </a>
  `).join('');

  const initials = username ? username.slice(0, 2).toUpperCase() : 'AB';
  const roleDisplay = role === 'admin' ? 'Administrator' : 'Customer';

  return `
    <aside class="sidebar" id="app-sidebar">
      <div class="sidebar-header">
        <a href="dashboard.html" class="sidebar-logo">
          <div class="logo-icon">AB</div>
          <span class="logo-text">AwesomeBank</span>
        </a>
      </div>
      <nav class="sidebar-nav">
        ${navItems}
      </nav>
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar">${initials}</div>
          <div class="user-info-text">
            <div class="user-name">${username || 'User'}</div>
            <div class="user-role-badge">${roleDisplay}</div>
          </div>
          <button onclick="appLogout()" class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </div>
      </div>
    </aside>
  `;
}

async function initPage(callback) {
  const token = localStorage.getItem('token');
  if (!token) {
    window.location.href = 'login.html';
    return;
  }

  try {
    const response = await fetch('dashboard.php', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await response.json();

    if (!response.ok) {
      localStorage.removeItem('token');
      window.location.href = 'login.html';
      return;
    }

    const container = document.getElementById('sidebar-container');
    if (container) {
      container.innerHTML = buildSidebar(data.username, data.role, data.user_id);
    }

    if (callback) callback(data);

  } catch (err) {
    console.error('App init error:', err);
    window.location.href = 'login.html';
  }
}

function appLogout() {
  localStorage.removeItem('token');
  window.location.href = 'login.html';
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
}

function formatDate(dateString) {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
}
