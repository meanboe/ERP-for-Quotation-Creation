<?php
session_start();

// Check if the user session is set
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}

$currentDate = date("d/m/Y");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quotation - TwinCool</title>
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
            <li class="active">
                <a href="#"><i class="fas fa-file-invoice"></i>Quotations</a>
            </li>
            <li>
                <a href="terms.php"><i class="fas fa-list"></i>Terms & Conditions</a>
            </li>
            <li>
                <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
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
            <div class="quotation-card">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Customer Name</div>
                            <input type="text" class="field-value" id="customerName">
                        </div>
                        <div class="field-group">
                            <div class="field-label">Address</div>
                            <textarea class="field-value" id="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Ref No.</div>
                            <input type="text" class="field-value" id="refNo" readonly>
                        </div>
                        <div class="field-group">
                            <div class="field-label">Rev.</div>
                            <input type="text" class="field-value" id="revision" value="00" readonly>
                        </div>
                        <div class="field-group">
                            <div class="field-label">Date</div>
                            <input type="text" class="field-value" id="date" value="<?php echo $currentDate; ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <div class="field-label">Subject</div>
                    <input type="text" class="field-value" id="subject">
                </div>

                <!-- First Annexure - 28% GST -->
                <div class="annexure-section">
                    <h5>Annexure 1 - Supply of Unit (28% GST)</h5>
                    <table class="products-table" id="productsTable1">
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
                    <button type="button" class="add-product-btn" data-table="productsTable1">
                        <i class="fas fa-plus"></i> Add Product
                    </button>

                    <div class="calculation-section">
                        <div class="calculation-row">
                            <span class="calculation-label">Sub Total:</span>
                            <input type="number" class="calculation-input" id="subTotal1" readonly>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">GST (28%):</span>
                            <input type="number" class="calculation-input" id="gst1" readonly>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Round Off:</span>
                            <input type="number" class="calculation-input" id="roundOff1" step="0.01">
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Total with GST:</span>
                            <input type="number" class="calculation-input" id="grandTotal1" readonly>
                        </div>
                    </div>

                    <!-- Terms and Conditions for Annexure 1 -->
                    <div class="terms-section mt-4">
                        <div class="field-group">
                            <div class="field-label">Terms and Conditions</div>
                            <textarea class="field-value terms-input" id="terms1" rows="3">100% Advance against PO</textarea>
                        </div>
                    </div>
                </div>

                <div class="annexure-divider"></div>

                <!-- Second Annexure - 18% GST -->
                <div class="annexure-section">
                    <h5>Annexure 2 - Supply of Accessories (18% GST)</h5>
                    <table class="products-table" id="productsTable2">
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
                    <button type="button" class="add-product-btn" data-table="productsTable2">
                        <i class="fas fa-plus"></i> Add Product
                    </button>

                    <div class="calculation-section">
                        <div class="calculation-row">
                            <span class="calculation-label">Sub Total:</span>
                            <input type="number" class="calculation-input" id="subTotal2" readonly>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">GST (18%):</span>
                            <input type="number" class="calculation-input" id="gst2" readonly>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Round Off:</span>
                            <input type="number" class="calculation-input" id="roundOff2" step="0.01">
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Total with GST:</span>
                            <input type="number" class="calculation-input" id="grandTotal2" readonly>
                        </div>
                    </div>

                    <!-- Terms and Conditions for Annexure 2 -->
                    <div class="terms-section mt-4">
                        <div class="field-group">
                            <div class="field-label">Terms and Conditions</div>
                            <textarea class="field-value terms-input" id="terms2" rows="3">70% Advance against PO, 20% Against Delivery & 10% After Installation</textarea>
                        </div>
                    </div>
                </div>

                <!-- Final Total Section -->
                <div class="calculation-section">
                    <div class="calculation-row">
                        <span class="calculation-label">Final Total:</span>
                        <input type="number" class="calculation-input" id="finalTotal" readonly>
                    </div>
                </div>

                <!-- Save Button Section -->
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-primary btn-lg save-quotation-btn" id="saveQuotation">
                        <i class="fas fa-save me-2"></i>Save Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="../js/createQuotation.js"></script>

</body>
</html>