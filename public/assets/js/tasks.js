/**
 * Daily Tasks Page Logic
 *
 * Handles fetching, rendering, and updating daily tasks.
 */

const tasksContainer = document.getElementById('tasks-list-container');

let tasksData = [];

/**
 * Renders a single task item.
 * @param {object} task - The task data object.
 * @returns {string} - The HTML string for the task item.
 */
function renderTask(task) {
    const isCompleted = task.status === 'done' || task.status === 'skipped';
    return `
        <div class="task-item card ${isCompleted ? 'task-item--completed' : ''}" data-user-task-id="${task.user_task_id}">
            <div class="task-item__content">
                <h3 class="task-item__title">${task.title}</h3>
                <p class="task-item__description">${task.description}</p>
                <span class="task-item__xp">${task.xp_reward} XP</span>
            </div>
            <div class="task-item__actions">
                ${!isCompleted ? `
                    <button class="btn btn-sm btn-success complete-btn">Complete</button>
                    <button class="btn btn-sm btn-secondary skip-btn">Skip</button>
                ` : `<span class="task-item__status">${task.status}</span>`}
            </div>
        </div>
    `;
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
            tasksContainer.innerHTML = tasksData.map(renderTask).join('');
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
                    taskItem.outerHTML = renderTask(tasksData[taskIndex]);
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
