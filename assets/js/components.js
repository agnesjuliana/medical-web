/**
 * Component Kit JavaScript
 * 
 * Handles interactive components: modals, dropdowns, sidebar toggle, mobile navbar.
 */

// ─────────────────────────────────────────────
// DROPDOWN
// ─────────────────────────────────────────────

function toggleDropdown(menuId) {
    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');

    // Close all other dropdowns first
    closeAllDropdowns(menuId);

    if (isHidden) {
        menu.classList.remove('hidden');
        // Animate in
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0)';
        });
    } else {
        closeDropdown(menuId);
    }
}

function closeDropdown(menuId) {
    const menu = document.getElementById(menuId);
    if (!menu) return;

    menu.style.opacity = '0';
    menu.style.transform = 'translateY(-4px)';
    setTimeout(() => menu.classList.add('hidden'), 150);
}

function closeAllDropdowns(exceptId) {
    document.querySelectorAll('[id$="-menu"], [id$="Menu"]').forEach(el => {
        if (el.id !== exceptId && !el.classList.contains('hidden')) {
            closeDropdown(el.id);
        }
    });
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('[id$="-menu"], [id$="Menu"]');
    dropdowns.forEach(menu => {
        const parent = menu.closest('.relative, [id$="Dropdown"]');
        if (parent && !parent.contains(e.target)) {
            closeDropdown(menu.id);
        }
    });
});


// ─────────────────────────────────────────────
// MODAL
// ─────────────────────────────────────────────

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const panel = document.getElementById(modalId + '-panel');
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Animate in
    requestAnimationFrame(() => {
        if (panel) {
            panel.style.opacity = '1';
            panel.style.transform = 'scale(1)';
        }
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const panel = document.getElementById(modalId + '-panel');
    if (!modal) return;

    if (panel) {
        panel.style.opacity = '0';
        panel.style.transform = 'scale(0.95)';
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.fixed.inset-0.z-\\[100\\]:not(.hidden)').forEach(modal => {
            closeModal(modal.id);
        });
    }
});


// ─────────────────────────────────────────────
// MOBILE NAVBAR
// ─────────────────────────────────────────────

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const iconOpen = document.getElementById('menuIconOpen');
    const iconClose = document.getElementById('menuIconClose');

    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    menu.classList.toggle('hidden');

    if (iconOpen && iconClose) {
        iconOpen.classList.toggle('hidden', isHidden);
        iconClose.classList.toggle('hidden', !isHidden);
    }
}


// ─────────────────────────────────────────────
// SIDEBAR TOGGLE (for mobile)
// ─────────────────────────────────────────────

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    const isVisible = !sidebar.classList.contains('-translate-x-full');

    if (isVisible) {
        sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
    }
}


// ─────────────────────────────────────────────
// UTILS
// ─────────────────────────────────────────────

/**
 * Copy text to clipboard
 */
function copyToClipboard(text, feedbackEl) {
    navigator.clipboard.writeText(text).then(() => {
        if (feedbackEl) {
            const original = feedbackEl.textContent;
            feedbackEl.textContent = 'Copied!';
            setTimeout(() => { feedbackEl.textContent = original; }, 2000);
        }
    });
}

/**
 * Format date string to locale
 */
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}
