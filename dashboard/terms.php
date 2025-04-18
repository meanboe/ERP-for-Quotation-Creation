<?php
session_start();

// Check if the user session is set
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - TwinCool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/logo.svg" alt="Logo" class="logo">
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="../index.php"><i class="fas fa-home"></i>Dashboard</a>
            </li>
            <li>
                <a href="createQuotation.php"><i class="fas fa-file-invoice"></i>Quotations</a>
            </li>
            <li class="active">
                <a href="terms.php"><i class="fas fa-list"></i>Terms & Conditions</a>
            </li>
            <li>
                <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <div class="user-dropdown dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo $_SESSION['user']; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid">
            <div class="terms-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Terms & Conditions Management</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termModal">
                        <i class="fas fa-plus me-2"></i>Add New Term
                    </button>
                </div>

                <!-- Annexure 1 Terms -->
                <div class="terms-section mb-5">
                    <h5 class="mb-3">Annexure 1 - Supply of Units (28% GST)</h5>
                    <div class="table-responsive">
                        <table class="table" id="annexure1Table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="75%">Term</th>
                                    <th width="10%">Order</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Terms will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Annexure 2 Terms -->
                <div class="terms-section">
                    <h5 class="mb-3">Annexure 2 - Supply of Accessories (18% GST)</h5>
                    <div class="table-responsive">
                        <table class="table" id="annexure2Table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="75%">Term</th>
                                    <th width="10%">Order</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Terms will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Term Modal -->
<div class="modal fade" id="termModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Term</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="termForm">
                    <input type="hidden" id="termId">
                    <div class="mb-3">
                        <label class="form-label">Annexure Type</label>
                        <select class="form-select" id="annexureType" required>
                            <option value="1">Annexure 1 - Supply of Units (28% GST)</option>
                            <option value="2">Annexure 2 - Supply of Accessories (18% GST)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Term Text</label>
                        <textarea class="form-control" id="termText" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sortOrder" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTerm">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this term?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="../js/terms.js"></script>

</body>
</html>