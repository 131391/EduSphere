import './bootstrap';

function resolveApiMessage(payload, fallback = 'Something went wrong. Please try again.') {
    fallback = fallback || 'Something went wrong. Please try again.';

    if (!payload) return fallback;

    if (typeof payload === 'string') {
        return payload;
    }

    const firstError = payload.errors
        ? Object.values(payload.errors).flat().find(Boolean)
        : null;

    const message = payload.message && !['The given data was invalid.', 'Validation failed', 'Validation failed.'].includes(payload.message)
        ? payload.message
        : null;

    return message || firstError || payload.error || fallback;
}

function dispatchToast(message, type = 'info') {
    if (!message) return;

    window.dispatchEvent(new CustomEvent('show-toast', {
        detail: { message, type }
    }));
}

window.resolveApiMessage = resolveApiMessage;
window.firstValidationMessage = function firstValidationMessage(errors, fallback = 'Please correct the highlighted fields.') {
    const first = errors
        ? Object.values(errors).flat().find(Boolean)
        : null;

    return first || fallback;
};
window.showAppToast = dispatchToast;
window.Toast = window.Toast || {
    fire(config = {}) {
        const iconToType = {
            success: 'success',
            error: 'error',
            warning: 'warning',
            info: 'info',
            question: 'info',
        };

        const type = iconToType[config.icon] || config.type || 'info';
        const message = config.title || config.text || config.message || 'Done';

        dispatchToast(message, type);
        return Promise.resolve();
    }
};

// Modern app initialization
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Flowbite (if available)
    try {
        if (window.Flowbite) {
            window.Flowbite.initDropdowns();
            window.Flowbite.initModals();
            window.Flowbite.initTabs();
        }
    } catch (e) {
        console.debug('Flowbite not yet loaded');
    }

    // Initialize Livewire
    if (window.Livewire) {
        console.log('Livewire initialized');
    }

    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Custom validation if needed
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });

    // Global error handlers
    window.addEventListener('error', (event) => {
        console.error('Error:', event.error);
    });
});

// Livewire hooks (if using Livewire)
document.addEventListener('livewire:initialized', () => {
    console.log('Livewire fully initialized');
    // Reinitialize Flowbite components after Livewire updates
    try {
        if (window.Flowbite) {
            window.Flowbite.initAll();
        }
    } catch (e) {
        console.debug('Flowbite update failed');
    }
});

export { };
