import './bootstrap';

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

