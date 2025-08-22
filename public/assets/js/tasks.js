/**
 * Daily Tasks Page Logic
 *
 * Handles fetching, rendering, and updating daily tasks.
 */

const tasksContainer = document.getElementById('tasks-list-container');

let tasksData = [];

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
 * Renders a single task item.
 * @param {object} task - The task data object.
 * @returns {HTMLElement} - The DOM element for the task item.
 */
function renderTask(task) {
    const isCompleted = task.status === 'done' || task.status === 'skipped';
    const taskItem = createElement('div', `task-item card ${isCompleted ? 'task-item--completed' : ''}`);
    taskItem.dataset.userTaskId = task.user_task_id;

    const content = createElement('div', 'task-item__content');
    const titleH3 = createElement('h3', 'task-item__title', task.title);
    const descriptionP = createElement('p', 'task-item__description', task.description);
    const xpSpan = createElement('span', 'task-item__xp', `${task.xp_reward} XP`);
    content.append(titleH3, descriptionP, xpSpan);

    const actions = createElement('div', 'task-item__actions');
    if (!isCompleted) {
        const completeBtn = createElement('button', 'btn btn-sm btn-success complete-btn', 'Complete');
        const skipBtn = createElement('button', 'btn btn-sm btn-secondary skip-btn', 'Skip');
        actions.append(completeBtn, skipBtn);
    } else {
        const statusSpan = createElement('span', 'task-item__status', task.status);
        actions.appendChild(statusSpan);
    }

    taskItem.append(content, actions);
    return taskItem;
}

/**
 * Updates the stats on the page (tasks completed, points earned).
 */
function updateStats() {
    const totalTasks = tasksData.length;
    const completedTasks = tasksData.filter(t => t.status === 'done').length;
    const pointsEarned = tasksData.reduce((sum, t) => sum + (t.status === 'done' ? t.xp_reward : 0), 0);

    document.getElementById('tasks-completed').textContent = `${completedTasks}/${totalTasks}`;
    document.getElementById('points-earned').textContent = pointsEarned;
    // Streak data would need to come from a separate API endpoint or be included.
}

/**
 * Fetches tasks from the API and renders them.
 */
async function fetchAndRenderTasks() {
    if (!tasksContainer) return;
    tasksContainer.innerHTML = '<p>Loading your daily tasks...</p>';

    try {
        const response = await window.apiFetch('/api/tasks/today.php');
        tasksData = response.data || [];

        if (tasksData.length > 0) {
            const taskElements = tasksData.map(renderTask);
            tasksContainer.replaceChildren(...taskElements);
        } else {
            tasksContainer.innerHTML = '<p>No tasks scheduled for today. Check back tomorrow!</p>';
        }
        updateStats();
    } catch (error) {
        tasksContainer.innerHTML = '<p class="text-error">Could not load tasks. Please try again later.</p>';
    }
}

/**
 * Handles clicks on task action buttons.
 */
function handleTaskActions() {
    if (!tasksContainer) return;

    tasksContainer.addEventListener('click', async (e) => {
        const target = e.target;
        const taskItem = target.closest('.task-item');
        if (!taskItem) return;

        const userTaskId = taskItem.dataset.userTaskId;
        const isComplete = target.classList.contains('complete-btn');
        const isSkip = target.classList.contains('skip-btn');

        if (isComplete || isSkip) {
            const newStatus = isComplete ? 'done' : 'skipped';
            try {
                const response = await window.apiFetch('/api/tasks/complete.php', {
                    method: 'POST',
                    body: { user_task_id: userTaskId, status: newStatus }
                });

                if (response.success) {
                    // Update local data and re-render for simplicity
                    const taskIndex = tasksData.findIndex(t => t.user_task_id == userTaskId);
                    if (taskIndex > -1) {
                        tasksData[taskIndex].status = newStatus;
                        tasksData[taskIndex].points_earned = response.points_earned;
                    }
                    const newTaskItem = renderTask(tasksData[taskIndex]);
                    taskItem.replaceWith(newTaskItem);
                    updateStats();
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Task updated!', type: 'success' } }));
                }
            } catch (error) {
                // Error toast handled by global fetch helper
            }
        }
    });
}

/**
 * Initializes all Tasks page functionality.
 */
export function initTasksPage() {
    fetchAndRenderTasks();
    handleTaskActions();
}
