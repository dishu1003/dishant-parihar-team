<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/router.php';

// Protect this page
protect_page();

$page_title = "My CRM - " . SITE_NAME;
$body_class = "crm-page";
include __DIR__ . '/../partials/header.php';
?>

<div class="crm-container container">
    <div class="page-header">
        <h1 class="page-title">My Personal CRM</h1>
        <button id="add-lead-btn" class="btn btn-primary" data-modal-target="#add-lead-modal">Add New Lead</button>
    </div>
    <p class="page-subtitle">Manage your prospects and track your progress.</p>

    <div class="crm-filters">
        <!-- Filter options can be added here later -->
    </div>

    <div id="leads-container" class="grid-container">
        <!-- Leads will be dynamically rendered here by crm.js -->
        <p>Loading your leads...</p>
    </div>
</div>

<!-- Add Lead Modal -->
<div id="add-lead-modal" class="modal">
    <div class="modal-content">
        <span class="modal-close" data-modal-close>&times;</span>
        <h2>Add a New Lead</h2>
        <form id="add-lead-form">
            <div id="add-lead-form-error" class="form-message error-message"></div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="mobile" class="form-label">Mobile</label>
                    <input type="tel" id="mobile" name="mobile" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="city" class="form-label">City</label>
                    <input type="text" id="city" name="city" class="form-control">
                </div>
                <div class="form-group">
                    <label for="work" class="form-label">Occupation/Work</label>
                    <input type="text" id="work" name="work" class="form-control">
                </div>
                <div class="form-group">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" id="age" name="age" class="form-control">
                </div>
                <div class="form-group">
                    <label for="interest_level" class="form-label">Interest Level</label>
                    <select id="interest_level" name="interest_level" class="form-control">
                        <option value="warm">Warm</option>
                        <option value="hot">Hot</option>
                        <option value="cold">Cold</option>
                    </select>
                </div>
                <div class="form-group form-group-full">
                    <label for="meeting_date" class="form-label">Meeting Date</label>
                    <input type="date" id="meeting_date" name="meeting_date" class="form-control">
                </div>
                <div class="form-group form-group-full">
                    <label for="follow_up_date" class="form-label">Follow-up Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control">
                </div>
                <div class="form-group form-group-full">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Save Lead</button>
            </div>
        </form>
    </div>
</div>

<!-- Note: A similar modal for editing leads (`#edit-lead-modal`) would be created here -->

<?php include __DIR__ . '/../partials/footer.php'; ?>
