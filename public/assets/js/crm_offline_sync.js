/**
 * CRM Offline Sync
 *
 * Manages an IndexedDB queue for leads created while offline and syncs
 * them with the server when a connection is available.
 */

const DB_NAME = 'asclepius-crm-db';
const DB_VERSION = 1;
const STORE_NAME = 'leads_queue';
let db;

/**
 * Opens and initializes the IndexedDB database.
 * @returns {Promise<IDBDatabase>}
 */
function openDB() {
    return new Promise((resolve, reject) => {
        if (db) {
            return resolve(db);
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = event => {
            console.error('IndexedDB error:', event.target.error);
            reject('Error opening database.');
        };

        request.onsuccess = event => {
            db = event.target.result;
            resolve(db);
        };

        request.onupgradeneeded = event => {
            const dbInstance = event.target.result;
            if (!dbInstance.objectStoreNames.contains(STORE_NAME)) {
                dbInstance.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                console.log('IndexedDB object store created:', STORE_NAME);
            }
        };
    });
}

/**
 * Adds a lead object to the offline queue in IndexedDB.
 * @param {object} leadData - The lead data to be queued.
 * @returns {Promise<void>}
 */
async function addToQueue(leadData) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.add(leadData);

        request.onsuccess = () => {
            console.log('Lead added to offline queue.');
            resolve();
        };
        request.onerror = event => {
            console.error('Error adding lead to queue:', event.target.error);
            reject('Could not save lead offline.');
        };
    });
}

/**
 * Retrieves all leads from the offline queue.
 * @returns {Promise<Array<object>>}
 */
async function getQueuedLeads() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.getAll();

        request.onsuccess = () => {
            resolve(request.result);
        };
        request.onerror = event => {
            console.error('Error getting queued leads:', event.target.error);
            reject('Could not read offline queue.');
        };
    });
}

/**
 * Clears all leads from the offline queue.
 * @returns {Promise<void>}
 */
async function clearQueue() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.clear();

        request.onsuccess = () => {
            console.log('Offline queue cleared.');
            resolve();
        };
        request.onerror = event => {
            console.error('Error clearing queue:', event.target.error);
            reject('Could not clear offline queue.');
        };
    });
}

/**
 * Attempts to sync all queued leads with the server.
 */
async function syncQueuedLeads() {
    console.log('Attempting to sync queued leads...');
    const queuedLeads = await getQueuedLeads();

    if (queuedLeads.length === 0) {
        console.log('No leads to sync.');
        return;
    }

    // Use the global apiFetch helper from main.js
    if (typeof window.apiFetch !== 'function') {
        console.error('apiFetch is not available.');
        return;
    }

    try {
        // The create endpoint is designed to handle an array of leads
        const response = await window.apiFetch('/api/crm/lead_create.php', {
            method: 'POST',
            body: queuedLeads
        });

        console.log('Sync successful:', response);
        await clearQueue();
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: `${queuedLeads.length} offline lead(s) synced successfully!`, type: 'success' }
        }));
    } catch (error) {
        console.error('Sync failed:', error);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Offline sync failed. Will try again later.', type: 'error' }
        }));
    }
}

/**
 * Main function to handle creating a lead, either online or offline.
 * This should be called by the CRM form's submit event handler.
 * @param {object} leadData - The lead data from the form.
 */
export async function handleLeadSubmit(leadData) {
    // If online, try to submit directly. If it fails, queue it.
    if (navigator.onLine) {
        try {
            const response = await window.apiFetch('/api/crm/lead_create.php', {
                method: 'POST',
                body: leadData
            });
            console.log('Lead created online:', response);
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message: 'Lead created successfully!', type: 'success' }
            }));
            // Optionally, redirect or clear form here
            document.getElementById('add-lead-form').reset();
            return;
        } catch (error) {
            console.warn('Online submission failed, saving to offline queue.', error);
            // Fall through to queueing logic
        }
    }

    // If offline or if online submission failed, add to queue.
    try {
        await addToQueue(leadData);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'You are offline. Lead saved locally and will sync later.', type: 'info' }
        }));
        document.getElementById('add-lead-form').reset();
    } catch (error) {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: `Error: ${error}`, type: 'error' }
        }));
    }
}


/**
 * Initializes event listeners for online/offline status and sync requests.
 */
export function initOfflineSync() {
    window.addEventListener('online', syncQueuedLeads);
    // Listen for the message from the service worker for background sync
    window.addEventListener('execute-crm-sync', syncQueuedLeads);

    // Initial sync attempt on load, in case there's anything in the queue
    syncQueuedLeads();
}
