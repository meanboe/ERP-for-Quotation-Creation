<?php
session_start();

// Check if the user session is set
if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit();
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TwinCool Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo.svg" alt="Logo" class="logo">
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="#"><i class="fas fa-home"></i>Dashboard</a>
            </li>
            <li>
                <a href="dashboard/createQuotation.php"><i class="fas fa-file-invoice"></i>Quotations</a>
            </li>
            <li>
                <a href="dashboard/terms.php"><i class="fas fa-list"></i>Terms & Conditions</a>
            </li>
            <li>
                <a href="dashboard/settings.php"><i class="fas fa-cog"></i>Settings</a>
            </li>
        </ul>
    </nav>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

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
                            <li><a class="dropdown-item" href="dashboard/settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Quotations</h5>
                            <p class="display-6" id="totalQuotations">0</p>
                            <p class="text-success d-flex align-items-center">
                                <i class="fas" id="quotationTrendIcon"></i>
                                <span id="quotationTrend">Loading...</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Active Customers</h5>
                            <p class="display-6">45</p>
                            <p class="text-success d-flex align-items-center">
                                <i class="fas fa-arrow-up me-2"></i>
                                <span>5% from last month</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="display-6">280</p>
                            <p class="text-success d-flex align-items-center">
                                <i class="fas fa-arrow-up me-2"></i>
                                <span>8% from last month</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotations Table Section -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">All Quotations</h5>
                    <div class="search-box">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search quotations...">
                    </div>
                </div>
                <div class="table-responsive-xl">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Quotation No.</th>
                                <th>Customer Name</th>
                                <th>Created By</th>
                                <th>Subject</th>
                                <th>Created Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quotationsTableBody">
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quotation Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="printContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadPdf">Download PDF</button>
                <button type="button" class="btn btn-success" id="printQuotation">Print</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editQuotationForm">
                    <!-- Form content will be loaded dynamically -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditedQuotation">Save Changes</button>
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
                Are you sure you want to delete this quotation?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Quotation Modal -->
<div class="modal fade" id="editQuotationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editQuotationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-group">
                                <div class="field-label">Customer Name</div>
                                <input type="text" class="field-value" id="editCustomerName">
                            </div>
                            <div class="field-group">
                                <div class="field-label">Address</div>
                                <textarea class="field-value" id="editAddress" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <div class="field-label">Ref No.</div>
                                <input type="text" class="field-value" id="editRefNo" readonly>
                            </div>
                            <div class="field-group">
                                <div class="field-label">Rev.</div>
                                <input type="text" class="field-value" id="editRevision" readonly>
                            </div>
                            <div class="field-group">
                                <div class="field-label">Date</div>
                                <input type="text" class="field-value" id="editDate">
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field-label">Subject</div>
                        <input type="text" class="field-value" id="editSubject">
                    </div>

                    <!-- First Annexure - 28% GST -->
                    <div class="annexure-section">
                        <h5>Annexure 1 - Supply of Unit (28% GST)</h5>
                        <table class="products-table" id="editProductsTable1">
                            <thead>
                                <tr>
                                    <th width="5%">Sr No.</th>
                                    <th width="40%">Description</th>
                                    <th width="15%">Unit</th>
                                    <th width="10%">Qty</th>
                                    <th width="15%">Rate</th>
                                    <th width="15%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><input type="text" class="product-description"></td>
                                    <td>
                                        <select class="product-unit form-select">
                                            <option value="NOS">NOS</option>
                                            <option value="RFT">RFT</option>
                                            <option value="RMT">RMT</option>
                                            <option value="SQFT">SQFT</option>
                                            <option value="SQMTR">SQMTR</option>
                                            <option value="LOT">LOT</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="product-qty" min="0"></td>
                                    <td><input type="number" class="product-rate" min="0"></td>
                                    <td><input type="number" class="product-total" readonly></td>
                                    <td><i class="fas fa-trash remove-row-btn"></i></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="add-product-btn" data-table="editProductsTable1">
                            <i class="fas fa-plus"></i> Add Product
                        </button>

                        <div class="calculation-section">
                            <div class="calculation-row">
                                <span class="calculation-label">Sub Total:</span>
                                <input type="number" class="calculation-input" id="editSubTotal1" readonly>
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">GST (28%):</span>
                                <input type="number" class="calculation-input" id="editGst1" readonly>
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">Round Off:</span>
                                <input type="number" class="calculation-input" id="editRoundOff1" step="0.01">
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">Total with GST:</span>
                                <input type="number" class="calculation-input" id="editGrandTotal1" readonly>
                            </div>
                        </div>

                        <div class="terms-section mt-4">
                            <div class="field-group">
                                <div class="field-label">Terms and Conditions</div>
                                <textarea class="field-value terms-input" id="editTerms1" rows="3">100% Advance against PO</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="annexure-divider"></div>

                    <!-- Second Annexure - 18% GST -->
                    <div class="annexure-section">
                        <h5>Annexure 2 - Supply of Accessories (18% GST)</h5>
                        <table class="products-table" id="editProductsTable2">
                            <thead>
                                <tr>
                                    <th width="5%">Sr No.</th>
                                    <th width="40%">Description</th>
                                    <th width="15%">Unit</th>
                                    <th width="10%">Qty</th>
                                    <th width="15%">Rate</th>
                                    <th width="15%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><input type="text" class="product-description"></td>
                                    <td>
                                        <select class="product-unit form-select">
                                            <option value="NOS">NOS</option>
                                            <option value="RFT">RFT</option>
                                            <option value="RMT">RMT</option>
                                            <option value="SQFT">SQFT</option>
                                            <option value="SQMTR">SQMTR</option>
                                            <option value="LOT">LOT</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="product-qty" min="0"></td>
                                    <td><input type="number" class="product-rate" min="0"></td>
                                    <td><input type="number" class="product-total" readonly></td>
                                    <td><i class="fas fa-trash remove-row-btn"></i></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="add-product-btn" data-table="editProductsTable2">
                            <i class="fas fa-plus"></i> Add Product
                        </button>

                        <div class="calculation-section">
                            <div class="calculation-row">
                                <span class="calculation-label">Sub Total:</span>
                                <input type="number" class="calculation-input" id="editSubTotal2" readonly>
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">GST (18%):</span>
                                <input type="number" class="calculation-input" id="editGst2" readonly>
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">Round Off:</span>
                                <input type="number" class="calculation-input" id="editRoundOff2" step="0.01">
                            </div>
                            <div class="calculation-row">
                                <span class="calculation-label">Total with GST:</span>
                                <input type="number" class="calculation-input" id="editGrandTotal2" readonly>
                            </div>
                        </div>

                        <div class="terms-section mt-4">
                            <div class="field-group">
                                <div class="field-label">Terms and Conditions</div>
                                <textarea class="field-value terms-input" id="editTerms2" rows="3">70% Advance against PO, 20% Against Delivery & 10% After Installation</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Final Total Section -->
                    <div class="calculation-section">
                        <div class="calculation-row">
                            <span class="calculation-label">Final Total:</span>
                            <input type="number" class="calculation-input" id="editFinalTotal" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveQuotationChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add necessary scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="js/main.js"></script>

</body>
</html>