/**
 * UI Helpers - Modern UI Components & Utilities
 */

export function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

export function formatDate(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(date);
}

export function formatTime(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

export function showStatus(container, type, message, timeout = 5000) {
    if (!message) {
        container.innerHTML = '';
        return;
    }

    const typeClass = {
        success: 'bg-success-50 border-success-200 text-success-700',
        error: 'bg-danger-50 border-danger-200 text-danger-700',
        warning: 'bg-warning-50 border-warning-200 text-warning-700',
        info: 'bg-primary-50 border-primary-200 text-primary-700',
    }[type] || 'bg-neutral-50 border-neutral-200 text-neutral-700';

    const icons = {
        success: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`,
        error: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>`,
        warning: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`,
        info: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>`,
    };

    const icon = icons[type] || icons.info;

    container.innerHTML = `
        <div class="flex items-start gap-3 rounded-lg border p-4 ${typeClass} animate-fade-in">
            <div class="flex-shrink-0">${icon}</div>
            <div class="flex-1 text-sm">${escapeHtml(message)}</div>
            <button class="close-btn flex-shrink-0 hover:opacity-50" type="button" aria-label="Close">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    `;

    const closeBtn = container.querySelector('.close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            container.innerHTML = '';
        });
    }

    if (timeout > 0) {
        setTimeout(() => {
            if (container.innerHTML) {
                container.innerHTML = '';
            }
        }, timeout);
    }
}

export function showModal(title, content, actions = []) {
    return new Promise((resolve) => {
        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center animate-fade-in';

        const modal = document.createElement('div');
        modal.className = 'bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 border border-neutral-200 animate-slide-in-up';

        let actionsHtml = '';
        if (actions.length > 0) {
            actionsHtml = `<div class="flex gap-2 justify-end">${actions
                .map(
                    (action, idx) => `
                <button class="btn btn-${action.style || 'ghost'}" data-action="${idx}">
                    ${escapeHtml(action.label)}
                </button>
            `
                )
                .join('')}</div>`;
        }

        modal.innerHTML = `
            <div class="p-6 md:p-8">
                <h3 class="text-xl font-semibold text-neutral-900 mb-2">${escapeHtml(title)}</h3>
                <div class="text-neutral-600 text-sm mb-6">${content}</div>
                ${actionsHtml}
            </div>
        `;

        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);

        const buttons = modal.querySelectorAll('[data-action]');
        buttons.forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                backdrop.remove();
                resolve(actions[idx].value || idx);
            });
        });

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                backdrop.remove();
                resolve(null);
            }
        });
    });
}

export function createMenuItemHtml(item, isActive = false) {
    const activeClass = isActive ? 'active' : '';
    const icon = getMenuIcon(item.id);
    return `
        <a 
            href="#" 
            data-menu="${item.id}" 
            class="menu-item ${activeClass}" 
            role="menuitem"
        >
            ${icon}
            <span>${escapeHtml(item.label)}</span>
        </a>
    `;
}

function getMenuIcon(menuId) {
    const icons = {
        dashboard: 'M3 12a9 9 0 110-18 9 9 0 010 18zM9 9a3 3 0 110-6 3 3 0 010 6z',
        'admin-master': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        candidates: 'M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646zM3 20h18a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2z',
        'my-candidates': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'candidate-form': 'M12 4v16m8-8H4',
        'activity-log': 'M9 12h6m-6 4h6m2-5a9 9 0 11-18 0 9 9 0 0118 0z',
        documents: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        'stasi-recap': 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        saw: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        ranking: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    };

    const pathData = icons[menuId] || icons.dashboard;
    return `
        <svg class="menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${pathData}"/>
        </svg>
    `;
}

export function toggleSidebar(sidebar, overlay, toggle, force = null) {
    const isOpen = sidebar.classList.contains('hidden') === false;
    const shouldOpen = force !== null ? force : !isOpen;

    if (shouldOpen) {
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        toggle?.setAttribute('aria-expanded', 'true');
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        toggle?.setAttribute('aria-expanded', 'false');
    }
}

export function debounce(fn, delay = 300) {
    let timeoutId;
    return function debounced(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

export function throttle(fn, limit = 300) {
    let inThrottle;
    return function throttled(...args) {
        if (!inThrottle) {
            fn(...args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
}
