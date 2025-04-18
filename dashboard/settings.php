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
    <title>Settings - TwinCool</title>
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
            <li>
                <a href="terms.php"><i class="fas fa-list"></i>Terms & Conditions</a>
            </li>
            <li class="active">
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
            <div class="settings-card">
                <h4 class="mb-4">Company Settings</h4>
                
                <div class="row g-4">
                    <!-- Basic Company Information -->
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Company Name</div>
                            <input type="text" class="field-value" id="companyName">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Contact Person Name</div>
                            <input type="text" class="field-value" id="contactPerson">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Mobile Number</div>
                            <input type="tel" class="field-value" id="mobileNo">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Email ID</div>
                            <input type="email" class="field-value" id="emailId">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="field-group">
                            <div class="field-label">Company GST No.</div>
                            <input type="text" class="field-value" id="gstNo">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="field-group">
                            <div class="field-label">Company Address</div>
                            <textarea class="field-value" id="companyAddress" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Quotation Letter Format -->
                    <div class="col-12">
                        <div class="field-group">
                            <div class="field-label">Quotation Letter Format</div>
                            <textarea class="field-value" id="quotationLetter" rows="5"></textarea>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="col-12 text-end mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="saveSettings">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="../js/settings.js"></script>

</body>
</html>