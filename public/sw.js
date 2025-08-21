/**
 * Service Worker for Asclepius Wellness HQ PWA
 *
 * Author: Jules, Senior Full-Stack Engineer
 * Version: 1.0
 */

const CACHE_NAME = 'asclepius-hq-v1';
const OFFLINE_URL = 'views/partials/offline.php'; // A simple offline fallback page

// List of files that constitute the "app shell" and should be pre-cached.
const APP_SHELL_URLS = [
    '/',
    '/index.php',
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/js/pwa.js',
    '/assets/js/ui_components.js',
    '/assets/js/crm_offline_sync.js',
    '/assets/img/logo.png', // Assuming a logo file
    'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap',
    'https://fonts.gstatic.com/s/montserrat/v25/JTUHjIg1_i6t8kCHKm4532VJOt5-Qf8fxjQs.woff2' // Example font file, actual URL may vary
];

// 1. Install Event: Pre-cache the app shell.
self.addEventListener('install', event => {
    console.log('[Service Worker] Install');
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);
        console.log('[Service Worker] Caching all: app shell and content');
        // Add offline fallback page to the cache
        const offlinePageRequest = new Request(OFFLINE_URL, { cache: 'reload' });
        await cache.add(offlinePageRequest);
        await cache.addAll(APP_SHELL_URLS);
    })());
});

// 2. Activate Event: Clean up old caches.
self.addEventListener('activate', event => {
    console.log('[Service Worker] Activate');
    event.waitUntil((async () => {
        const cacheNames = await caches.keys();
        await Promise.all(
            cacheNames.filter(name => name !== CACHE_NAME)
                      .map(name => caches.delete(name))
        );
    })());
});

// 3. Fetch Event: Serve from cache or network.
self.addEventListener('fetch', event => {
    // We only want to intercept GET requests.
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith((async () => {
        const cache = await caches.open(CACHE_NAME);

        try {
            // Try the network first (Stale-While-Revalidate strategy for dynamic content)
            const networkResponse = await fetch(event.request);

            // If the request is successful, cache it and return it.
            // We only cache successful responses (status 200).
            if (networkResponse.ok) {
                console.log(`[Service Worker] Caching new resource: ${event.request.url}`);
                await cache.put(event.request, networkResponse.clone());
            }

            return networkResponse;
        } catch (error) {
            // Network request failed, probably offline. Try to serve from cache.
            console.log(`[Service Worker] Network request for ${event.request.url} failed. Trying cache.`);
            const cachedResponse = await cache.match(event.request);
            if (cachedResponse) {
                return cachedResponse;
            }

            // If the request is for a page navigation and it's not in cache, show the offline fallback page.
            if (event.request.mode === 'navigate') {
                const offlinePage = await cache.match(OFFLINE_URL);
                if (offlinePage) {
                    return offlinePage;
                }
            }

            // If nothing works, return a basic error response.
            return new Response(
                JSON.stringify({ error: 'Network error and resource not found in cache.' }),
                { status: 503, headers: { 'Content-Type': 'application/json' } }
            );
        }
    })());
});


// 4. Background Sync Event: For CRM offline submissions.
self.addEventListener('sync', event => {
    console.log('[Service Worker] Background sync event received:', event.tag);
    if (event.tag === 'crm-lead-sync') {
        console.log('[Service Worker] Starting CRM lead sync.');
        // The crm_offline_sync.js file will have the logic to handle the sync.
        // This event just triggers it when connectivity is back.
        // We need to notify the client pages to run the sync function.
        event.waitUntil(
            self.clients.matchAll({ includeUncontrolled: true }).then(clients => {
                for (const client of clients) {
                    client.postMessage({ type: 'EXECUTE_CRM_SYNC' });
                }
            })
        );
    }
});
