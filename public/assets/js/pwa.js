/**
 * PWA Service Worker Registration
 *
 * This module handles the registration of the application's service worker.
 */

/**
 * Registers the service worker.
 * It checks for browser support and registers the sw.js file on window load.
 */
export function registerServiceWorker() {
    // Check if service workers are supported by the browser
    if ('serviceWorker' in navigator) {
        // Register the service worker after the page has loaded
        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register('/sw.js') // The path is relative to the origin, not the JS file
                .then(registration => {
                    console.log('Service Worker registered successfully with scope:', registration.scope);
                })
                .catch(error => {
                    console.error('Service Worker registration failed:', error);
                });
        });

        // Listen for messages from the service worker (e.g., for background sync)
        navigator.serviceWorker.addEventListener('message', event => {
            if (event.data && event.data.type === 'EXECUTE_CRM_SYNC') {
                console.log('Received message from SW to execute CRM sync.');
                // We can dispatch a custom event that the crm_offline_sync module listens for.
                window.dispatchEvent(new CustomEvent('execute-crm-sync'));
            }
        });

    } else {
        console.log('Service workers are not supported by this browser.');
    }
}
