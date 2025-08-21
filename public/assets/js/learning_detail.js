/**
 * Learning Module Detail Page Logic
 */

const container = document.querySelector('.learning-detail-container');
const contentContainer = document.getElementById('module-content-container');
const actionsContainer = document.getElementById('module-actions');
const markCompleteBtn = document.getElementById('mark-complete-btn');
let currentModuleId = null;

/**
 * Renders the fetched module data into the DOM.
 * @param {object} module - The module data from the API.
 */
function renderModule(module) {
    document.title = `${module.title} - ${document.title}`;
    currentModuleId = module.id;

    let html = `
        <h1 class="module-title">${module.title}</h1>
        <p class="module-category">Category: ${module.category}</p>
        <div class="module-content">
            ${module.content_html}
        </div>
    `;

    if (module.video_url) {
        html += `<div class="module-video-wrapper"><iframe src="${module.video_url.replace('watch?v=', 'embed/')}" frameborder="0" allowfullscreen></iframe></div>`;
    }
    if (module.pdf_url) {
        html += `<div class="module-attachment"><a href="${module.pdf_url}" target="_blank" class="btn btn-secondary">Download PDF</a></div>`;
    }

    contentContainer.innerHTML = html;
    actionsContainer.style.display = 'block';

    if (module.status === 'completed') {
        markCompleteBtn.disabled = true;
        markCompleteBtn.textContent = 'Module Completed';
    }
}

async function handleMarkComplete() {
    if (!currentModuleId) return;

    markCompleteBtn.disabled = true;
    markCompleteBtn.textContent = 'Completing...';

    try {
        const response = await window.apiFetch('/api/learning/mark_complete.php', {
            method: 'POST',
            body: { module_id: currentModuleId }
        });

        if (response.success) {
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Congratulations! Module completed.', type: 'success' } }));
            markCompleteBtn.textContent = 'Module Completed';
        }
    } catch (error) {
        markCompleteBtn.disabled = false;
        markCompleteBtn.textContent = 'Mark as Complete';
        // Error toast is handled by global fetch helper
    }
}


/**
 * Initializes the learning detail page.
 */
export async function initLearningDetailPage() {
    if (!container) return;

    const slug = container.dataset.slug;
    if (!slug) {
        contentContainer.innerHTML = '<p class="text-error">Module slug not found. Cannot load module.</p>';
        return;
    }

    try {
        const response = await window.apiFetch(`/api/learning/module_detail.php?slug=${slug}`);
        renderModule(response.data);
        markCompleteBtn.addEventListener('click', handleMarkComplete);
    } catch (error) {
        contentContainer.innerHTML = `<p class="text-error">Error: ${error.message}</p>`;
    }
}
