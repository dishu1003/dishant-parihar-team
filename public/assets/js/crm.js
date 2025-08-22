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
 * Creates a DOM element with given tag, class, and text content.
 * @param {string} tag - The HTML tag.
 * @param {string} className - The CSS class name.
 * @param {string} [textContent] - The text content.
 * @returns {HTMLElement}
 */
function createElement(tag, className, textContent) {
    const el = document.createElement(tag);
    el.className = className;
    if (textContent) {
        el.textContent = textContent;
    }
    return el;
}

/**
 * Renders a single lead card.
 * @param {object} lead - The lead data object.
 * @returns {HTMLElement} - The DOM element for the lead card.
 */
function renderLeadCard(lead) {
    const card = createElement('div', 'card lead-card');
    card.dataset.leadId = lead.id;
    card.dataset.interest = lead.interest_level;

    // --- Header ---
    const header = createElement('div', 'card__header');
    const nameH3 = createElement('h3', 'lead-card__name', lead.name);
    const interestSpan = createElement('span', `lead-card__interest-level lead-card__interest-level--${lead.interest_level}`, lead.interest_level);
    header.append(nameH3, interestSpan);

    // --- Body ---
    const body = createElement('div', 'card__body');
    const followUpDate = lead.follow_up_date ? new Date(lead.follow_up_date) : null;
    const isOverdue = followUpDate && followUpDate < new Date();
    const formattedFollowUp = followUpDate ? followUpDate.toLocaleDateString() : 'N/A';

    // Using innerHTML for simple, safe content (strong tags).
    const mobileP = document.createElement('p');
    mobileP.innerHTML = `<strong>Mobile:</strong> <a href="tel:${lead.mobile}">${lead.mobile}</a>`;

    const cityP = createElement('p');
    cityP.innerHTML = `<strong>City:</strong> ${lead.city || 'N/A'}`;

    const followupP = createElement('p');
    const followupSpan = createElement('span', isOverdue ? 'text-error' : '', formattedFollowUp);
    followupP.innerHTML = '<strong>Follow-up:</strong> ';
    followupP.appendChild(followupSpan);

    const notesP = createElement('p', 'lead-card__notes');
    notesP.innerHTML = '<strong>Notes:</strong> ';
    notesP.append(lead.notes || 'No notes yet.'); // Using append() which treats strings as text

    body.append(mobileP, cityP, followupP, notesP);

    // --- Footer ---
    const footer = createElement('div', 'card__footer');
    const whatsappLink = `https://wa.me/${lead.mobile.replace(/\D/g, '')}?text=Hi%20${encodeURIComponent(lead.name)},%20...`;
    const whatsappBtn = createElement('a', 'btn btn-sm btn-success', 'WhatsApp');
    whatsappBtn.href = whatsappLink;
    whatsappBtn.target = '_blank';
    const editBtn = createElement('button', 'btn btn-sm btn-secondary edit-btn', 'Edit');
    const deleteBtn = createElement('button', 'btn btn-sm btn-danger delete-btn', 'Delete');
    footer.append(whatsappBtn, editBtn, deleteBtn);

    card.append(header, body, footer);
    return card;
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
            const leadElements = response.data.map(renderLeadCard);
            leadsContainer.replaceChildren(...leadElements); // Use replaceChildren for performance
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
