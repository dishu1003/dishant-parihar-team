/**
 * Learning Hub Page Logic
 *
 * Handles fetching and rendering learning modules.
 */

const modulesContainer = document.getElementById('learning-modules-container');

/**
 * Renders a single learning module card.
 * @param {object} module - The module data object.
 * @returns {string} - The HTML string for the module card.
 */
function renderModuleCard(module) {
    const isCompleted = module.status === 'completed';
    return `
        <a href="learning_detail.php?slug=${module.slug}" class="card module-card ${isCompleted ? 'module-card--completed' : ''}">
            <div class="card__body">
                <h3 class="module-card__title">${module.title}</h3>
                <p class="module-card__summary">${module.summary || ''}</p>
            </div>
            <div class="card__footer">
                <div class="module-card__progress-bar">
                    <div class="module-card__progress-value" style="width: ${module.progress}%;"></div>
                </div>
                <span class="module-card__status">${isCompleted ? 'Completed' : `${module.progress}%`}</span>
            </div>
        </a>
    `;
}

/**
 * Fetches modules from the API and renders them grouped by category.
 */
async function fetchAndRenderModules() {
    if (!modulesContainer) return;
    modulesContainer.innerHTML = '<p>Loading learning modules...</p>';

    try {
        const response = await window.apiFetch('/api/learning/modules_list.php');
        const categories = response.data || {};

        if (Object.keys(categories).length > 0) {
            let html = '';
            for (const category in categories) {
                html += `
                    <section class="learning-category">
                        <h2 class="category-title">${category}</h2>
                        <div class="grid-container">
                            ${categories[category].map(renderModuleCard).join('')}
                        </div>
                    </section>
                `;
            }
            modulesContainer.innerHTML = html;
        } else {
            modulesContainer.innerHTML = '<p>No learning modules are available at this time.</p>';
        }
    } catch (error) {
        modulesContainer.innerHTML = '<p class="text-error">Could not load learning modules. Please try again later.</p>';
    }
}


/**
 * Initializes all Learning Hub page functionality.
 */
export function initLearningPage() {
    fetchAndRenderModules();
}
