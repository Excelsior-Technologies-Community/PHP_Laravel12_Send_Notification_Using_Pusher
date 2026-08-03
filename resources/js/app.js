import './bootstrap';
import './echo';

function playNotificationSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
        oscillator.start(ctx.currentTime);
        oscillator.stop(ctx.currentTime + 0.3);
    } catch (e) {
        console.error('Audio play failed', e);
    }
}

async function loadNotifications() {
    try {
        const response = await fetch('/notifications/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        updateBadge(data.count);
        if (data.count > 0) {
            loadDropdownNotifications();
        }
    } catch (e) {
        console.error('Failed to load notification count', e);
    }
}

function updateBadge(count) {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;
    badge.textContent = count;
    if (count > 0) {
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

async function loadDropdownNotifications() {
    try {
        const response = await fetch('/notifications?ajax=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const html = await response.text();
        const list = document.getElementById('notification-list');
        if (!list) return;
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const tableBody = doc.querySelector('tbody');
    if (tableBody) {
        list.innerHTML = '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    const id = cells[0].textContent.trim();
                    const message = cells[1].textContent.trim();
                    const type = cells[2].textContent.trim();
                    const status = cells[3].textContent.trim();
                    const time = cells[4].textContent.trim();

                    const item = document.createElement('div');
                    item.className = 'dropdown-item-text small px-3 py-2 border-bottom';
                    item.innerHTML = `
                        <div class="fw-bold">${message}</div>
                        <div class="text-muted">${type} - ${time}</div>
                        <div>
                            ${status.includes('Unread') 
                                ? `<form action="/notifications/mark-read/${id}" method="POST" class="d-inline mt-1">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <button type="submit" class="btn btn-sm btn-success py-0 px-1">Mark Read</button>
                                   </form>` 
                                : ''}
                        </div>
                    `;
                    list.appendChild(item);
                }
            });
            if (rows.length === 0) {
                list.innerHTML = '<div class="px-3 py-2 text-muted small">No notifications.</div>';
            }
        }
    } catch (e) {
        console.error('Failed to load dropdown notifications', e);
    }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toastId = 'toast-' + Date.now();
    const toastEl = document.createElement('div');
    toastEl.id = toastId;
    toastEl.className = 'toast align-items-center text-bg-' + (type === 'error' ? 'danger' : 'success') + ' border-0';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fa fa-circle-check"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

async function markAsRead(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    await fetch(`/notifications/mark-read/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
    });
    loadNotifications();
}

window.Echo.channel('notifications')
    .listen('.received', (data) => {
        const currentUserId = document.querySelector('meta[name="auth-user-id"]')?.content;
        if (currentUserId && data.notification.user_id == currentUserId) {
            playNotificationSound();
            showToast(data.notification.data.message || 'New notification received');
            loadNotifications();
        }
    });

if (document.getElementById('notificationDropdown')) {
    document.getElementById('notificationDropdown').addEventListener('show.bs.dropdown', () => {
        loadDropdownNotifications();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
});

export {};
