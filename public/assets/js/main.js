/**
 * Main JavaScript - Utilidades globales
 * Sistema de Tickets de Cancelación
 */

// CSRF Token
const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value || '';

/**
 * Wrapper para fetch con CSRF y manejo de errores
 */
async function api(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };

    try {
        const response = await fetch(url, mergedOptions);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Error en la solicitud');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * Mostrar toast notification
 */
function showToast(message, type = 'info') {
    const container = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} animate-slide-in`;
    toast.innerHTML = `
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${getToastIcon(type)}
        </svg>
        <span>${message}</span>
        <button class="ml-4 hover:opacity-75" onclick="this.parentElement.remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container fixed top-4 right-4 z-50 space-y-2';
    document.body.appendChild(container);
    return container;
}

function getToastIcon(type) {
    switch (type) {
        case 'success':
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        case 'error':
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        case 'warning':
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
        default:
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    }
}

/**
 * Mostrar modal de confirmación
 */
function showConfirm(title, message, onConfirm, confirmText = 'Confirmar') {
    const container = document.getElementById('modalContainer');
    const content = document.getElementById('modalContent');
    
    if (!container || !content) return;
    
    content.innerHTML = `
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">${title}</h3>
            <p class="text-sm text-gray-500 mb-6">${message}</p>
            <div class="flex justify-center space-x-4">
                <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                <button type="button" class="btn btn-danger modal-confirm">${confirmText}</button>
            </div>
        </div>
    `;
    
    container.classList.remove('hidden');
    
    // Event listeners
    content.querySelector('.modal-cancel').addEventListener('click', hideModal);
    content.querySelector('.modal-confirm').addEventListener('click', () => {
        hideModal();
        if (typeof onConfirm === 'function') onConfirm();
    });
    
    document.getElementById('modalOverlay').addEventListener('click', hideModal);
}

function hideModal() {
    document.getElementById('modalContainer')?.classList.add('hidden');
}

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

/**
 * Formatear fecha
 */
function formatDate(dateString, includeTime = false) {
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return new Date(dateString).toLocaleDateString('es-MX', options);
}

/**
 * Validar UUID
 */
function isValidUUID(uuid) {
    const regex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
    return regex.test(uuid);
}

/**
 * Validar RFC mexicano
 */
function isValidRFC(rfc) {
    const regex = /^[A-ZÑ&]{3,4}\d{6}[A-V1-9][0-9A-Z]?[0-9A-Z]$/i;
    return regex.test(rfc);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Copiar al portapapeles
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copiado al portapapeles', 'success');
    } catch (err) {
        console.error('Error al copiar:', err);
        showToast('Error al copiar', 'error');
    }
}

/**
 * Form submission con loading state
 */
function handleFormSubmit(formElement, onSubmit) {
    formElement.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...
        `;
        
        try {
            await onSubmit(new FormData(this));
        } catch (error) {
            showToast(error.message || 'Error al procesar', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Inicialización global
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modales con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideModal();
    });
    
    // Agregar confirmación a botones de eliminar
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.dataset.confirm || '¿Estás seguro?';
            showConfirm('Confirmar acción', message, () => {
                if (this.form) {
                    this.form.submit();
                } else if (this.href) {
                    window.location.href = this.href;
                }
            });
        });
    });
});
