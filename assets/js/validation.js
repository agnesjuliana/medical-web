/**
 * Client-Side Form Validation
 * 
 * Provides real-time validation on blur and submit.
 * Works with the component kit input styling.
 */

/**
 * Show an error message below a field
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorEl = document.getElementById(fieldId + '-error');

    if (field) {
        field.classList.add('border-red-300', 'bg-red-50/50');
        field.classList.remove('border-gray-200');
    }
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

/**
 * Clear an error message
 */
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorEl = document.getElementById(fieldId + '-error');

    if (field) {
        field.classList.remove('border-red-300', 'bg-red-50/50');
        field.classList.add('border-gray-200');
    }
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }
}

/**
 * Validate required field
 */
function validateRequired(fieldId, label) {
    const field = document.getElementById(fieldId);
    if (!field) return true;

    const value = field.value.trim();
    if (value === '') {
        showError(fieldId, `${label} is required.`);
        return false;
    }
    clearError(fieldId);
    return true;
}

/**
 * Validate email format
 */
function validateEmail(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return true;

    const value = field.value.trim();
    if (value === '') return true; // Skip if empty (use validateRequired for that)

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
        showError(fieldId, 'Please enter a valid email address.');
        return false;
    }
    clearError(fieldId);
    return true;
}

/**
 * Validate minimum length
 */
function validateMinLength(fieldId, min, label) {
    const field = document.getElementById(fieldId);
    if (!field) return true;

    const value = field.value.trim();
    if (value === '') return true;

    if (value.length < min) {
        showError(fieldId, `${label} must be at least ${min} characters.`);
        return false;
    }
    clearError(fieldId);
    return true;
}

/**
 * Validate two fields match
 */
function validateMatch(fieldId1, fieldId2, label) {
    const field1 = document.getElementById(fieldId1);
    const field2 = document.getElementById(fieldId2);
    if (!field1 || !field2) return true;

    if (field1.value !== field2.value) {
        showError(fieldId2, `${label} does not match.`);
        return false;
    }
    clearError(fieldId2);
    return true;
}

/**
 * Toggle password visibility
 */
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
    } else {
        field.type = 'password';
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
    }
}

// Auto-attach blur validation to common fields
document.addEventListener('DOMContentLoaded', function() {
    const fieldConfigs = [
        { id: 'name',             validate: () => validateRequired('name', 'Full name') },
        { id: 'email',            validate: () => { validateRequired('email', 'Email'); validateEmail('email'); } },
        { id: 'password',         validate: () => { validateRequired('password', 'Password'); validateMinLength('password', 6, 'Password'); } },
        { id: 'password_confirm', validate: () => validateMatch('password', 'password_confirm', 'Password confirmation') },
    ];

    fieldConfigs.forEach(config => {
        const field = document.getElementById(config.id);
        if (field) {
            field.addEventListener('blur', config.validate);
            field.addEventListener('input', () => clearError(config.id));
        }
    });
});
