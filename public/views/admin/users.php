<?php
require_once __DIR__ . '/../../partials/header.php';

// The header includes session start, auth checks, and CSRF token generation.
// It will redirect if user is not logged in or not an admin.
?>

<main class="container">
    <div class="admin-users-page">
        <h1 class="page-title">User Management</h1>
        <p class="page-description">A list of all users in the system. You can view, edit, or deactivate users from here.</p>

        <div class="table-container">
            <table class="data-table" id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- User data will be populated here by JavaScript -->
                    <tr>
                        <td colspan="8" class="text-center">Loading users...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchUsers();

    function fetchUsers() {
        const tableBody = document.getElementById('users-table-body');
        const token = '<?php echo $_SESSION["csrf_token"]; ?>';

        fetch('/api/admin/users/list.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('Forbidden: You do not have permission to view this page.');
                }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                renderUsersTable(data.data);
            } else {
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center error">${data.message || 'Failed to load users.'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center error">${error.message || 'An error occurred while fetching users.'}</td></tr>`;
        });
    }

    function renderUsersTable(users) {
        const tableBody = document.getElementById('users-table-body');
        tableBody.innerHTML = ''; // Clear existing content

        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No users found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = document.createElement('tr');
            const status = user.is_active ? '<span class="status-active">Active</span>' : '<span class="status-inactive">Inactive</span>';

            row.innerHTML = `
                <td>${escapeHTML(user.id)}</td>
                <td>${escapeHTML(user.name)}</td>
                <td><a href="mailto:${escapeHTML(user.email)}">${escapeHTML(user.email)}</a></td>
                <td>${escapeHTML(user.phone) || 'N/A'}</td>
                <td>${escapeHTML(user.role)}</td>
                <td>${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}</td>
                <td>${status}</td>
                <td class="actions">
                    <button class="btn btn-sm" onclick="viewUser(${user.id})">View</button>
                    <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">Edit</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function escapeHTML(str) {
        if (str === null || str === undefined) {
            return '';
        }
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});

function viewUser(id) {
    // Placeholder for view functionality
    console.log(`Viewing user ${id}`);
    alert(`Viewing user ${id}`);
}

function editUser(id) {
    // Placeholder for edit functionality
    console.log(`Editing user ${id}`);
    alert(`Editing user ${id}`);
}
</script>

<?php
require_once __DIR__ . '/../../partials/footer.php';
?>
