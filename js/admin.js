// Custom Confirm Modal
function showConfirm(message, onConfirm, onCancel) {
    return new Promise((resolve) => {
        // Remove existing modal if any
        const existingModal = document.querySelector('.custom-modal-overlay');
        if (existingModal) existingModal.remove();

        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        overlay.innerHTML = `
            <div class="custom-modal">
                <div class="custom-modal-message">${message}</div>
                <div class="custom-modal-buttons">
                    <button class="custom-modal-btn custom-modal-btn-cancel" data-action="cancel">Tidak</button>
                    <button class="custom-modal-btn custom-modal-btn-confirm" data-action="confirm">Ya</button>
                </div>
            </div>
        `;

        // Add to body
        document.body.appendChild(overlay);

        // Trigger animation
        setTimeout(() => overlay.classList.add('show'), 10);

        // Handle button clicks
        const handleClick = (e) => {
            const action = e.target.dataset.action;
            if (!action) return;

            // Close animation
            overlay.classList.remove('show');
            setTimeout(() => overlay.remove(), 300);

            if (action === 'confirm') {
                if (onConfirm) onConfirm();
                resolve(true);
            } else {
                if (onCancel) onCancel();
                resolve(false);
            }
        };

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) { // Click outside modal
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
                if (onCancel) onCancel();
                resolve(false);
            } else {
                handleClick(e);
            }
        });
    });
}

// Custom Toast Notification
function showToast(message, type = 'success', duration = 4000) {
    // Get or create toast container
    let container = document.querySelector('.custom-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'custom-toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');

    // Determine border color based on type
    let borderColor = '#00C853'; // success (green)
    let iconColor = '#00C853';
    let icon = 'bi-check-circle-fill';

    if (type === 'error' || type === 'danger') {
        borderColor = '#dc3545';
        iconColor = '#dc3545';
        icon = 'bi-x-circle-fill';
    } else if (type === 'warning') {
        borderColor = '#ffc107';
        iconColor = '#ffc107';
        icon = 'bi-exclamation-triangle-fill';
    } else if (type === 'info') {
        borderColor = '#0dcaf0';
        iconColor = '#0dcaf0';
        icon = 'bi-info-circle-fill';
    }

    toast.style.cssText = `
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border-left: 4px solid ${borderColor};
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 300px;
        max-width: 400px;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        font-family: 'Poppins', sans-serif;
    `;

    toast.innerHTML = `
        <i class="bi ${icon}" style="color: ${iconColor}; font-size: 1.25rem;"></i>
        <span style="flex: 1; color: #333; font-size: 0.95rem;">${message}</span>
        <button class="toast-close-btn" style="background: none; border: none; color: #00C853; font-size: 1.25rem; cursor: pointer; padding: 0; line-height: 1;">
            <i class="bi bi-x"></i>
        </button>
    `;

    // Add to container
    container.appendChild(toast);

    // Trigger show animation
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 10);

    // Close button handler
    const closeBtn = toast.querySelector('.toast-close-btn');
    const closeToast = () => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    };
    closeBtn.addEventListener('click', closeToast);

    // Auto close
    setTimeout(closeToast, duration);
}
