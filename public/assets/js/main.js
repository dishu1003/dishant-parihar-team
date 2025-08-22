/**
 * Main JavaScript Entry Point
 *
 * Orchestrates the initialization of all frontend modules and functionality.
 */

import { registerServiceWorker } from './pwa.js';
import { initUIComponents } from './ui_components.js';
import { initOfflineSync } from './crm_offline_sync.js';
import { generateAndDisplayTips } from './ai_mentor.js';
import { initAuthForms } from './auth.js';
import { initCrmPage } from './crm.js';
import { initTasksPage } from './tasks.js';
import { initLearningPage } from './learning.js';
import { initLearningDetailPage } from './learning_detail.js';

// --- Global State & Helpers ---

/**
 * A wrapper for the native fetch API to automatically handle CSRF tokens and JSON content type.
 * @param {string} url - The URL to fetch.
 * @param {object} options - Fetch options (method, body, etc.).
 * @returns {Promise<Response>}
 */
async function apiFetch(url, options = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    if (csrfToken) {
        defaultHeaders['X-CSRF-Token'] = csrfToken;
    }

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    };

    if (config.body && typeof config.body !== 'string') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(url, config);
        if (!response.ok) {
            // Try to parse error message from server, otherwise use status text
            const errorData = await response.json().catch(() => null);
            const errorMessage = errorData?.message || response.statusText;
            throw new Error(errorMessage);
        }
        return response.json();
    } catch (error) {
        console.error(`API Fetch Error to ${url}:`, error);
        // Show a toast notification for the error
        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: error.message, type: 'error' } }));
        throw error;
    }
}

// Make apiFetch globally available for convenience in other scripts if needed, or pass it as a dependency.
window.apiFetch = apiFetch;


// --- Theme Management (Dark/Light Mode) ---

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}

function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const defaultTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
    applyTheme(defaultTheme);

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }
}


// --- Application Initialization ---

/**
 * Main function to initialize the application after the DOM is loaded.
 */
function main() {
    console.log('Asclepius Wellness HQ Initializing...');

    // 1. Initialize Progressive Web App features
    registerServiceWorker();

    // 2. Initialize UI components (modals, toasts, etc.)
    initUIComponents();

    // 3. Initialize theme (dark/light mode)
    initTheme();

    // 4. Initialize CRM offline capabilities
    initOfflineSync();

    // 5. If on the dashboard, initialize AI Mentor
    if (document.body.classList.contains('dashboard-page')) {
         // This data would ideally be fetched or embedded in the page
        const mentorData = {
            overdue_followups: 2,
            hot_leads: 4,
            learning_progress: { 'Sales & Networking': 25 },
            streak: 3,
            activity: [1,1,1,0,1,1,1] // 1=active, 0=inactive for last 7 days
        };
        generateAndDisplayTips(mentorData);
    }

    // 6. Handle Top Navigation logic (logout, active links)
    handleTopNav();

    // 7. Handle Mobile Navigation Toggle
    handleMobileNav();

    // 8. Initialize auth forms if on an auth page
    if (document.body.classList.contains('auth-page')) {
        initAuthForms();
    }

    // 8. Initialize CRM page if on the crm page
    if (document.body.classList.contains('crm-page')) {
        initCrmPage();
    }

    // 9. Initialize Tasks page if on the tasks page
    if (document.body.classList.contains('tasks-page')) {
        initTasksPage();
    }

    // 10. Initialize Learning Hub page if on the learning page
    if (document.body.classList.contains('learning-page')) {
        initLearningPage();
    }

    // 11. Initialize Learning Detail page
    if (document.body.classList.contains('learning-detail-page')) {
        initLearningDetailPage();
    }

    console.log('Initialization complete.');
}

/**
 * Handles logic for the top navigation bar, including logout and active link highlighting.
 */
function handleTopNav() {
    // Handle Logout
    const logoutForm = document.getElementById('logout-form');
    if(logoutForm) {
        logoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const csrfToken = logoutForm.querySelector('input[name="csrf_token"]').value;

            try {
                const response = await fetch('/api/auth/logout.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken
                    }
                });

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Logging out...', type: 'info' } }));
                    window.location.href = '/';
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Logout request failed.');
                }
            } catch (error) {
                console.error('Logout failed:', error);
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: error.message, type: 'error' } }));
            }
        });
    }

    // Set active nav link
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.topnav__links a');

    let bestMatch = null;
    navLinks.forEach(link => {
        link.classList.remove('active'); // Reset all first
        const linkHref = link.getAttribute('href');
        if (currentPage.includes(linkHref)) {
            if (!bestMatch || linkHref.length > bestMatch.getAttribute('href').length) {
                bestMatch = link;
            }
        }
    });

    if (bestMatch) {
        bestMatch.classList.add('active');
    }
}

/**
 * Handles the mobile navigation toggle functionality.
 */
function handleMobileNav() {
    const toggleBtn = document.getElementById('mobile-nav-toggle');
    const mainNav = document.getElementById('main-nav');

    if (toggleBtn && mainNav) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = mainNav.classList.toggle('is-open');
            toggleBtn.setAttribute('aria-expanded', isOpen);
        });
    }
}

// --- Event Listeners ---

// Run the main initialization function when the DOM is ready.
document.addEventListener('DOMContentLoaded', main);
