<?php
require_once __DIR__ . '/../../partials/header.php';
// The header includes session start, auth checks, and CSRF token generation.
?>

<main class="container">
    <div class="admin-learning-page">
        <h1 class="page-title">Manage Learning Modules</h1>
        <p class="page-description">Create, edit, and manage learning content for users.</p>

        <div class="toolbar">
            <button id="create-module-btn" class="btn btn-primary">Create New Module</button>
        </div>

        <div class="table-container">
            <table class="data-table" id="modules-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="modules-table-body">
                    <!-- Module data will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal for Create/Edit Module -->
<div id="module-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modal-title">Create New Module</h2>
        <form id="module-form">
            <input type="hidden" id="module-id" name="id">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required>
            </div>
            <div class="form-group">
                <label for="summary">Summary</label>
                <textarea id="summary" name="summary" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="content">Content (HTML allowed)</label>
                <textarea id="content" name="content" rows="10"></textarea>
            </div>
            <div class="form-group">
                <label for="video_url">Video URL (Optional)</label>
                <input type="url" id="video_url" name="video_url">
            </div>
            <div class="form-group">
                <label for="order_no">Order</label>
                <input type="number" id="order_no" name="order_no" value="0">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Module</button>
            </div>
            <div id="form-error" class="text-error" style="display:none;"></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('module-modal');
    const createBtn = document.getElementById('create-module-btn');
    const closeBtn = modal.querySelector('.close-btn');
    const form = document.getElementById('module-form');
    const modalTitle = document.getElementById('modal-title');
    const formError = document.getElementById('form-error');
    const tableBody = document.getElementById('modules-table-body');
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';

    // --- Fetch and Render Modules ---
    async function fetchModules() {
        try {
            const response = await apiFetch('/api/learning/admin_list.php');
            if (response.success) {
                renderTable(response.data);
            } else {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center error">${response.message}</td></tr>`;
            }
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center error">Failed to load modules.</td></tr>`;
            console.error('Error fetching modules:', error);
        }
    }

    function renderTable(modules) {
        tableBody.innerHTML = '';
        if (modules.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">No modules found.</td></tr>`;
            return;
        }
        modules.forEach(module => {
            const row = document.createElement('tr');
            const status = module.is_active ? '<span class="status-active">Active</span>' : '<span class="status-inactive">Inactive</span>';
            row.innerHTML = `
                <td>${e(module.id)}</td>
                <td>${e(module.title)}</td>
                <td>${e(module.category)}</td>
                <td>${e(module.order_no)}</td>
                <td>${status}</td>
                <td class="actions">
                    <button class="btn btn-sm" onclick="openEditModal(${module.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteModule(${module.id})">Delete</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // --- Modal Handling ---
    function openModal(mode = 'create', moduleData = {}) {
        form.reset();
        formError.style.display = 'none';
        document.getElementById('module-id').value = '';

        if (mode === 'edit') {
            modalTitle.textContent = 'Edit Module';
            document.getElementById('module-id').value = moduleData.id;
            document.getElementById('title').value = moduleData.title;
            document.getElementById('category').value = moduleData.category;
            document.getElementById('summary').value = moduleData.summary;
            document.getElementById('content').value = moduleData.content;
            document.getElementById('video_url').value = moduleData.video_url;
            document.getElementById('order_no').value = moduleData.order_no;
            document.getElementById('is_active').checked = moduleData.is_active;
        } else {
            modalTitle.textContent = 'Create New Module';
        }
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    createBtn.onclick = () => openModal('create');
    closeBtn.onclick = closeModal;
    window.onclick = (event) => {
        if (event.target == modal) {
            closeModal();
        }
    };

    // --- API Interactions ---
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        formError.style.display = 'none';

        const formData = new FormData(form);
        const module = Object.fromEntries(formData.entries());
        module.is_active = document.getElementById('is_active').checked; // FormData bug with checkboxes
        module.order_no = parseInt(module.order_no, 10);

        const id = module.id;
        const isEditMode = !!id;
        const url = isEditMode ? `/api/learning/admin_update.php?id=${id}` : '/api/learning/admin_create.php';

        try {
            const response = await apiFetch(url, {
                method: 'POST',
                body: JSON.stringify(module)
            });

            if (response.success) {
                closeModal();
                fetchModules(); // Refresh table
            } else {
                formError.textContent = response.message || 'An unknown error occurred.';
                formError.style.display = 'block';
            }
        } catch (error) {
            formError.textContent = 'A network error occurred. Please try again.';
            formError.style.display = 'block';
            console.error('Form submission error:', error);
        }
    });

    window.openEditModal = async function(id) {
        try {
            const response = await apiFetch(`/api/learning/admin_get.php?id=${id}`);
            if (response.success) {
                openModal('edit', response.data);
            } else {
                alert('Error: ' + response.message);
            }
        } catch (error) {
            alert('Failed to fetch module details.');
            console.error('Error fetching module for edit:', error);
        }
    };

    window.deleteModule = async function(id) {
        if (!confirm('Are you sure you want to delete this module? This action cannot be undone.')) {
            return;
        }
        try {
            const response = await apiFetch('/api/learning/admin_delete.php', {
                method: 'POST',
                body: JSON.stringify({ id: id })
            });

            if (response.success) {
                fetchModules(); // Refresh table
            } else {
                alert('Error: ' + response.message);
            }
        } catch (error) {
            alert('Failed to delete module.');
            console.error('Error deleting module:', error);
        }
    };

    // --- Utility Functions ---
    async function apiFetch(url, options = {}) {
        options.headers = {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            ...options.headers
        };
        const response = await fetch(url, options);
        if (!response.ok) {
            // Try to parse error message from body, otherwise use status text
            let errorData;
            try {
                errorData = await response.json();
            } catch (e) {
                // Not a JSON response
            }
            const message = errorData?.message || `HTTP error! status: ${response.status} ${response.statusText}`;
            throw new Error(message);
        }
        return response.json();
    }

    function e(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Initial load
    fetchModules();
});
</script>

<?php
require_once __DIR__ . '/../../partials/footer.php';
?>
