// Lost & Found Management System - Main JavaScript

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Image preview before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

// Filter items without page reload
function filterItems() {
    const search = document.getElementById('searchInput')?.value.toLowerCase();
    const items = document.querySelectorAll('.item-card');
    items.forEach(item => {
        const title = item.querySelector('.item-title')?.textContent.toLowerCase();
        const desc = item.querySelector('.item-desc')?.textContent.toLowerCase();
        if (!search || title.includes(search) || desc.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Show/hide back-to-top button
window.addEventListener('scroll', () => {
    const btn = document.getElementById('backToTop');
    if (btn) btn.style.display = window.scrollY > 300 ? 'block' : 'none';
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc2626';
            valid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    return valid;
}

// Status color update in tables
document.querySelectorAll('.status-badge').forEach(badge => {
    const status = badge.textContent.trim().toLowerCase();
    badge.className = 'badge badge-' + status;
});
