/**
 * Global Form Validation Error Handler
 * 
 * This script automatically clears Laravel validation errors when users
 * start typing or selecting in form fields.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Find all forms on the page
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Handle all input and textarea fields
        form.addEventListener('input', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                clearFieldError(e.target);
            }
        });

        // Handle select fields (including Select2)
        form.addEventListener('change', function (e) {
            if (e.target.tagName === 'SELECT') {
                clearFieldError(e.target);
            }
        });

        // Handle Select2 specific events
        if (typeof $ !== 'undefined') {
            $(form).on('select2:select select2:clear', 'select', function (e) {
                clearFieldError(e.target);
            });
        }
    });
});

/**
 * Clear validation error for a specific field
 * @param {HTMLElement} field - The form field element
 */
function clearFieldError(field) {
    // Remove red border from the field
    if (field.classList.contains('border-red-500')) {
        field.classList.remove('border-red-500');
    }

    // Find and remove the error message
    // Error messages are typically the next sibling with class 'text-red-500' or 'text-red-600'
    let nextElement = field.nextElementSibling;

    // For Select2, skip the Select2 container
    if (nextElement && nextElement.classList.contains('select2-container')) {
        nextElement = nextElement.nextElementSibling;
    }

    // Remove error message if found
    if (nextElement && (nextElement.classList.contains('text-red-500') || nextElement.classList.contains('text-red-600'))) {
        nextElement.remove();
    }

    // Also check parent's next sibling (for some layouts)
    const parentNextSibling = field.parentElement?.nextElementSibling;
    if (parentNextSibling && (parentNextSibling.classList.contains('text-red-500') || parentNextSibling.classList.contains('text-red-600'))) {
        parentNextSibling.remove();
    }
}

/**
 * Manually clear error for a field by name
 * @param {string} fieldName - The name attribute of the field
 */
window.clearValidationError = function (fieldName) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
        clearFieldError(field);
    }
};
