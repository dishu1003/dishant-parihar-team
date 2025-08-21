/**
 * CRM Page Logic
 *
 * Handles fetching, rendering, and managing leads on the CRM page.
 */

import { handleLeadSubmit } from './crm_offline_sync.js';

const leadsContainer = document.getElementById('leads-container');
const addLeadModal = document.getElementById('add-lead-modal');
const addLeadForm = document.getElementById('add-lead-form');

/**
 * Renders a single lead card.
 * @param {object} lead - The lead data object.
 * @returns {string} - The HTML string for the lead card.
 */
function renderLeadCard(lead) {
    const followUpDate = lead.follow_up_date ? new Date(lead.follow_up_date) : null;
    const isOverdue = followUpDate && followUpDate < new Date();
    const formattedFollowUp = followUpDate ? followUpDate.toLocaleDateString() : 'N/A';
    const whatsappLink = `https://wa.me/${lead.mobile.replace(/\D/g, '')}?text=Hi%20${encodeURIComponent(lead.name)},%20...`;

    return `
        <div class="card lead-card" data-lead-id="${lead.id}" data-interest="${lead.interest_level}">
            <div class="card__header">
                <h3 class="lead-card__name">${lead.name}</h3>
                <span class="lead-card__interest-level lead-card__interest-level--${lead.interest_level}">${lead.interest_level}</span>
            </div>
            <div class="card__body">
                <p><strong>Mobile:</strong> <a href="tel:${lead.mobile}">${lead.mobile}</a></p>
                <p><strong>City:</strong> ${lead.city || 'N/A'}</p>
                <p><strong>Follow-up:</strong> <span class="${isOverdue ? 'text-error' : ''}">${formattedFollowUp}</span></p>
                <p class="lead-card__notes"><strong>Notes:</strong> ${lead.notes || 'No notes yet.'}</p>
            </div>
            <div class="card__footer">
                <a href="${whatsappLink}" target="_blank" class="btn btn-sm btn-success">WhatsApp</a>
                <button class="btn btn-sm btn-secondary edit-btn">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn">Delete</button>
            </div>
        </div>
    `;
}

/**
 * Fetches leads from the API and renders them to the page.
 */
async function fetchAndRenderLeads() {
    if (!leadsContainer) return;
    leadsContainer.innerHTML = '<p>Loading your leads...</p>';

    try {
        const response = await window.apiFetch('/api/crm/leads_list.php');
        if (response.data && response.data.length > 0) {
            leadsContainer.innerHTML = response.data.map(renderLeadCard).join('');
        } else {
            leadsContainer.innerHTML = '<p>You have no leads yet. Click "Add New Lead" to get started!</p>';
        }
    } catch (error) {
        leadsContainer.innerHTML = '<p class="text-error">Could not load leads. Please try again later.</p>';
        console.error('Failed to fetch leads:', error);
    }
}

/**
 * Handles the submission of the "Add Lead" form.
 */
function handleAddLeadForm() {
    if (!addLeadForm) return;

    addLeadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addLeadForm);
        const leadData = Object.fromEntries(formData.entries());

        // Use the offline-capable submit handler
        await handleLeadSubmit(leadData);

        // Close the modal and refresh the list
        if (addLeadModal) {
            addLeadModal.classList.remove('is-visible');
        }
        fetchAndRenderLeads();
    });
}

/**
 * Handles clicks on action buttons within the leads container (edit, delete).
 */
function handleLeadActions() {
    if (!leadsContainer) return;

    leadsContainer.addEventListener('click', async (e) => {
        const target = e.target;

        // --- Handle Delete ---
        if (target.classList.contains('delete-btn')) {
            const card = target.closest('.lead-card');
            const leadId = card.dataset.leadId;

            if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                try {
                    await window.apiFetch('/api/crm/lead_delete.php', {
                        method: 'POST',
                        body: { id: leadId }
                    });
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lead deleted.', type: 'success' } }));
                    fetchAndRenderLeads(); // Refresh the list
                } catch (error) {
                    // Error toast is handled by global apiFetch
                }
            }
        }

        // --- Handle Edit ---
        if (target.classList.contains('edit-btn')) {
            // TODO: Implement edit functionality
            // 1. Get lead data (maybe from an in-memory store or a quick API fetch)
            // 2. Open an #edit-lead-modal
            // 3. Populate the form with the lead data
            // 4. Handle the form submission to call the update API
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Edit functionality coming soon!', type: 'info' } }));
        }
    });
}

/**
 * Initializes all CRM page functionality.
 */
export function initCrmPage() {
    fetchAndRenderLeads();
    handleAddLeadForm();
    handleLeadActions();
}
