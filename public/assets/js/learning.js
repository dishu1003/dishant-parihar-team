/**
 * Learning Hub Page Logic
 *
 * Handles fetching and rendering learning modules.
 */

const modulesContainer = document.getElementById('learning-modules-container');

/**
 * Creates a DOM element with given tag, class, and text content.
 * @param {string} tag - The HTML tag.
 * @param {string} className - The CSS class name.
 * @param {string} [textContent] - The text content.
 * @returns {HTMLElement}
 */
function createElement(tag, className, textContent) {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (textContent) el.textContent = textContent;
    return el;
}

/**
 * Renders a single learning module card.
 * @param {object} module - The module data object.
 * @returns {HTMLElement} - The DOM element for the module card.
 */
function renderModuleCard(module) {
    const isCompleted = module.status === 'completed';
    const cardLink = createElement('a', `card module-card ${isCompleted ? 'module-card--completed' : ''}`);
    cardLink.href = `learning_detail.php?slug=${module.slug}`;

    const body = createElement('div', 'card__body');
    const titleH3 = createElement('h3', 'module-card__title', module.title);
    const summaryP = createElement('p', 'module-card__summary', module.summary || '');
    body.append(titleH3, summaryP);

    const footer = createElement('div', 'card__footer');
    const progressBar = createElement('div', 'module-card__progress-bar');
    const progressValue = createElement('div', 'module-card__progress-value');
    progressValue.style.width = `${module.progress}%`;
    progressBar.appendChild(progressValue);
    const statusSpan = createElement('span', 'module-card__status', isCompleted ? 'Completed' : `${module.progress}%`);
    footer.append(progressBar, statusSpan);

    cardLink.append(body, footer);
    return cardLink;
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
        const fragments = [];

        if (Object.keys(categories).length > 0) {
            for (const categoryName in categories) {
                const section = createElement('section', 'learning-category');
                const titleH2 = createElement('h2', 'category-title', categoryName);
                const grid = createElement('div', 'grid-container');

                const moduleElements = categories[categoryName].map(renderModuleCard);
                grid.append(...moduleElements);

                section.append(titleH2, grid);
                fragments.push(section);
            }
            modulesContainer.replaceChildren(...fragments);
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
