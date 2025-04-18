$(document).ready(function() {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "timeOut": "5000"
    };

    // Load settings when page loads
    loadSettings();

    // Handle sidebar toggle
    $('#sidebarCollapse').click(function() {
        $('#sidebar, #content').toggleClass('active');
    });

    // Handle saving settings
    $('#saveSettings').click(function() {
        // Collect all settings data
        const settingsData = {
            companyName: $('#companyName').val().trim(),
            contactPerson: $('#contactPerson').val().trim(),
            mobileNo: $('#mobileNo').val().trim(),
            emailId: $('#emailId').val().trim(),
            gstNo: $('#gstNo').val().trim(),
            companyAddress: $('#companyAddress').val().trim(),
            quotationLetter: $('#quotationLetter').val().trim()
        };

        // Validate required fields
        if (!validateSettings(settingsData)) {
            return;
        }

        // Show loading indicator
        toastr.info('Saving settings...', '', {"timeOut": 0, "extendedTimeOut": 0});

        // Save settings to backend
        $.ajax({
            url: '../backend/saveSettings.php',
            type: 'POST',
            data: JSON.stringify(settingsData),
            contentType: 'application/json',
            success: function(response) {
                toastr.clear();
                if (response.status === 'success') {
                    toastr.success('Settings saved successfully');
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.clear();
                let errorMessage = 'An error occurred while saving settings.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' ' + xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
            }
        });
    });

    // Validate settings data
    function validateSettings(data) {
        if (!data.companyName) {
            toastr.warning('Please enter company name');
            $('#companyName').focus();
            return false;
        }
        if (!data.contactPerson) {
            toastr.warning('Please enter contact person name');
            $('#contactPerson').focus();
            return false;
        }
        if (!data.mobileNo) {
            toastr.warning('Please enter mobile number');
            $('#mobileNo').focus();
            return false;
        }
        if (!data.emailId) {
            toastr.warning('Please enter email ID');
            $('#emailId').focus();
            return false;
        }
        if (!data.gstNo) {
            toastr.warning('Please enter GST number');
            $('#gstNo').focus();
            return false;
        }
        if (!data.companyAddress) {
            toastr.warning('Please enter company address');
            $('#companyAddress').focus();
            return false;
        }
        return true;
    }

    // Load settings from backend
    function loadSettings() {
        $.ajax({
            url: '../backend/getSettings.php',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    // Fill form with settings data
                    $('#companyName').val(response.data.company_name || '');
                    $('#contactPerson').val(response.data.contact_person || '');
                    $('#mobileNo').val(response.data.mobile_no || '');
                    $('#emailId').val(response.data.email_id || '');
                    $('#gstNo').val(response.data.gst_no || '');
                    $('#companyAddress').val(response.data.company_address || '');
                    $('#quotationLetter').val(response.data.quotation_letter || '');
                } else {
                    toastr.error('Error loading settings: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Failed to load settings from server');
            }
        });
    }

    // Add input validation for mobile number
    $('#mobileNo').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substr(0, 10);
    });

    // Add input validation for GST number
    $('#gstNo').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Add input validation for email
    $('#emailId').on('blur', function() {
        const email = $(this).val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            toastr.warning('Please enter a valid email address');
            $(this).focus();
        }
    });
});