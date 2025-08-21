/**
 * UI Components
 *
 * This module contains the logic for all reusable, interactive UI components
 * like modals, toasts, accordions, and carousels.
 */

// --- Toast / Notification Handler ---

function handleToastNotifications() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);

    window.addEventListener('show-toast', event => {
        const { message, type = 'info', duration = 3000 } = event.detail;

        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('is-visible'), 10);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('is-visible');
            toast.addEventListener('transitionend', () => toast.remove());
        }, duration);
    });
}

// --- Modal Handler ---

function handleModals() {
    const openTriggers = document.querySelectorAll('[data-modal-target]');
    const closeTriggers = document.querySelectorAll('[data-modal-close]');

    openTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modal = document.querySelector(trigger.dataset.modalTarget);
            if (modal) {
                modal.classList.add('is-visible');
            }
        });
    });

    closeTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modal = trigger.closest('.modal');
            if (modal) {
                modal.classList.remove('is-visible');
            }
        });
    });

    // Close modal by clicking on the background overlay
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                modal.classList.remove('is-visible');
            }
        });
    });

    // Close modal with Escape key
    window.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.is-visible').forEach(modal => {
                modal.classList.remove('is-visible');
            });
        }
    });
}


// --- Accordion Handler ---

function handleAccordions() {
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        const content = item.querySelector('.accordion-content');

        if (header && content) {
            header.addEventListener('click', () => {
                // Optional: Close other open accordions
                // document.querySelectorAll('.accordion-item.is-open').forEach(openItem => {
                //     if (openItem !== item) {
                //         openItem.classList.remove('is-open');
                //         openItem.querySelector('.accordion-content').style.maxHeight = null;
                //     }
                // });

                item.classList.toggle('is-open');
                if (item.classList.contains('is-open')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    content.style.maxHeight = null;
                }
            });
        }
    });
}


/**
 * Initializes all UI components on the page.
 * This function should be called after the DOM is fully loaded.
 */
export function initUIComponents() {
    handleToastNotifications();
    handleModals();
    handleAccordions();
    // Add other component initializers here (e.g., handleCarousels)
}
