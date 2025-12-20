# Confirm Modal Component Usage Guide

A reusable confirmation modal component for delete actions and other confirmations throughout the application.

## Basic Usage

```blade
<!-- Include the modal component -->
<x-confirm-modal 
    id="delete-item-modal"
    title="Delete Item"
    message="Are you sure you want to delete this item? This action cannot be undone."
    confirm-text="Delete"
    cancel-text="Cancel"
    confirm-class="bg-red-600 hover:bg-red-700"
/>

<!-- Trigger the modal -->
<button onclick="confirmDelete(123)">Delete</button>

<script>
function confirmDelete(itemId) {
    window.confirmModal.show('delete-item-modal', function() {
        // Perform delete action
        fetch('/items/' + itemId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            window.location.reload();
        });
    });
}
</script>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | `'confirm-modal'` | Unique ID for the modal |
| `title` | string | `'Confirm Action'` | Modal title |
| `message` | string | `'Are you sure you want to proceed?'` | Confirmation message |
| `confirmText` | string | `'Confirm'` | Confirm button text |
| `cancelText` | string | `'Cancel'` | Cancel button text |
| `confirmClass` | string | `'bg-blue-600 hover:bg-blue-700'` | Confirm button CSS classes |
| `cancelClass` | string | `'bg-gray-300...'` | Cancel button CSS classes |

## JavaScript API

### Show Modal
```javascript
window.confirmModal.show(modalId, callback, options);
```

**Parameters:**
- `modalId` (string): The ID of the modal to show
- `callback` (function): Function to execute when confirmed
- `options` (object, optional): Override modal content
  - `title`: Override title
  - `message`: Override message
  - `confirmText`: Override confirm button text
  - `cancelText`: Override cancel button text

### Hide Modal
```javascript
window.confirmModal.hide(modalId);
```

## Examples

### Delete with Form Submission
```blade
<button onclick="confirmDelete({{ $item->id }})">Delete</button>

<x-confirm-modal id="delete-modal" title="Delete Item" message="Are you sure?" />

<script>
function confirmDelete(itemId) {
    window.confirmModal.show('delete-modal', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/items/' + itemId;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);
        
        document.body.appendChild(form);
        form.submit();
    });
}
</script>
```

### Dynamic Content
```javascript
window.confirmModal.show('delete-modal', function() {
    // Delete action
}, {
    title: 'Delete School',
    message: 'This will delete the school and all associated data. Are you sure?',
    confirmText: 'Yes, Delete',
    cancelText: 'No, Keep It'
});
```

## Features

- ✅ Custom styled modal (not browser alert)
- ✅ Dark mode support
- ✅ Smooth animations
- ✅ Escape key to close
- ✅ Click outside to close
- ✅ Customizable title, message, and buttons
- ✅ Reusable across the application
- ✅ Works with forms and AJAX requests

