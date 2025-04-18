$(document).ready(function() {
    // Configure Toastr with updated settings
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",         // Increased from 3000 to 5000
        "extendedTimeOut": "2000", // Increased from 1000 to 2000
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };


    // Handle sidebar toggle
    $('#sidebarCollapse').click(function() {
        $('#sidebar, #content').toggleClass('active');
    });

    // Get reference number when page loads
    $.ajax({
        url: '../backend/getNextQuotationNumber.php',
        type: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                $('#refNo').val(response.refNo);
            } else {
                alert('Error getting reference number: ' + response.message);
            }
        },
        error: function() {
            alert('Error communicating with server');
        }
    });

    // Handle adding new product row for both tables
    $('.add-product-btn').click(function() {
        const tableId = $(this).data('table');
        const rowCount = $(`#${tableId} tbody tr`).length + 1;
        const newRow = `
            <tr>
                <td>${rowCount}</td>
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
        `;
        $(`#${tableId} tbody`).append(newRow);
    });

    // Handle removing product row
    $(document).on('click', '.remove-row-btn', function() {
        const row = $(this).closest('tr');
        const table = row.closest('table');
        
        if (table.find('tbody tr').length > 1) {
            row.remove();
            updateSerialNumbers(table);
            calculateTotals(table);
        }
    });

    // Update serial numbers after row removal
    function updateSerialNumbers(table) {
        table.find('tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Calculate row total when quantity or rate changes
    $(document).on('input', '.product-qty, .product-rate', function() {
        const row = $(this).closest('tr');
        const qty = parseFloat(row.find('.product-qty').val()) || 0;
        const rate = parseFloat(row.find('.product-rate').val()) || 0;
        const total = qty * rate;
        row.find('.product-total').val(total.toFixed(2));
        
        const table = $(this).closest('table');
        calculateTotals(table);
    });

    // Calculate sub total, GST and grand total for a specific table
    function calculateTotals(table) {
        const tableId = table.attr('id');
        const gstRate = tableId === 'productsTable1' ? 0.28 : 0.18;
        const index = tableId === 'productsTable1' ? '1' : '2';
        
        let subTotal = 0;
        
        // Calculate sub total
        table.find('tbody tr').each(function() {
            const total = parseFloat($(this).find('.product-total').val()) || 0;
            subTotal += total;
        });

        // Update sub total
        $(`#subTotal${index}`).val(subTotal.toFixed(2));

        // Calculate GST
        const gst = subTotal * gstRate;
        $(`#gst${index}`).val(gst.toFixed(2));

        calculateGrandTotal(index);
        calculateFinalTotal();
    }

    // Calculate grand total with round off for specific annexure
    function calculateGrandTotal(index) {
        const subTotal = parseFloat($(`#subTotal${index}`).val()) || 0;
        const gst = parseFloat($(`#gst${index}`).val()) || 0;
        const roundOff = parseFloat($(`#roundOff${index}`).val()) || 0;
        
        const grandTotal = subTotal + gst + roundOff;
        $(`#grandTotal${index}`).val(grandTotal.toFixed(2));
    }

    // Calculate final total from both annexures
    function calculateFinalTotal() {
        const grandTotal1 = parseFloat($('#grandTotal1').val()) || 0;
        const grandTotal2 = parseFloat($('#grandTotal2').val()) || 0;
        
        const finalTotal = grandTotal1 + grandTotal2;
        $('#finalTotal').val(finalTotal.toFixed(2));
    }

    // Handle round off changes for both sections
    $('#roundOff1, #roundOff2').on('input', function() {
        const index = $(this).attr('id').slice(-1);
        calculateGrandTotal(index);
        calculateFinalTotal();
    });

    // Initialize calculations for both tables
    $('#productsTable1, #productsTable2').each(function() {
        calculateTotals($(this));
    });

    // Handle save quotation
    $('#saveQuotation').click(function() {
        // // Show loading message
        // toastr.info('Saving quotation...', '', {"timeOut": 0, "extendedTimeOut": 0});

        // Collect all form data
        const quotationData = {
            customerName: $('#customerName').val().trim(),
            address: $('#address').val().trim(),
            refNo: $('#refNo').val().trim(),
            revision: $('#revision').val().trim(),
            date: $('#date').val().trim(),
            subject: $('#subject').val().trim(),
            annexure1: {
                products: getProductsData('productsTable1'),
                subTotal: $('#subTotal1').val(),
                gst: $('#gst1').val(),
                roundOff: $('#roundOff1').val() || "0",
                total: $('#grandTotal1').val(),
                terms: $('#terms1').val().trim()
            },
            annexure2: {
                products: getProductsData('productsTable2'),
                subTotal: $('#subTotal2').val(),
                gst: $('#gst2').val(),
                roundOff: $('#roundOff2').val() || "0",
                total: $('#grandTotal2').val(),
                terms: $('#terms2').val().trim()
            },
            finalTotal: $('#finalTotal').val()
        };

        // Validate required fields
        if (!validateForm(quotationData)) {
            toastr.clear(); // Clear the loading message
            return;
        }

        // Send data to backend
        $.ajax({
            url: '../backend/saveQuotation.php',
            type: 'POST',
            data: JSON.stringify(quotationData),
            contentType: 'application/json',
            success: function(response) {
                toastr.clear(); // Clear the loading message
                if (response.status === 'success') {
                    toastr.success('Quotation saved successfully', '', {
                        "timeOut": "2000",
                        "closeButton": true,
                        "onHidden": function() {
                            window.location.href = '../index.php';
                        }
                    });
                } else {
                    toastr.error('Error: ' + response.message, '', {
                        "timeOut": "5000",
                        "closeButton": true,
                        "progressBar": true
                    });
                }
            },
            error: function(xhr, status, error) {
                toastr.clear(); // Clear the loading message
                let errorMessage = 'An error occurred while saving the quotation.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' ' + xhr.responseJSON.message;
                }
                toastr.error(errorMessage, '', {
                    "timeOut": "5000",
                    "closeButton": true,
                    "progressBar": true
                });
            }
        });
    });

    // Helper function to get products data from a table
    function getProductsData(tableId) {
        const products = [];
        $(`#${tableId} tbody tr`).each(function() {
            const row = $(this);
            products.push({
                description: row.find('.product-description').val(),
                unit: row.find('.product-unit').val(),
                qty: row.find('.product-qty').val(),
                rate: row.find('.product-rate').val(),
                total: row.find('.product-total').val()
            });
        });
        return products;
    }

    // Validate form data
    function validateForm(data) {
        if (!data.customerName) {
            toastr.warning('Please enter customer name', '', { "timeOut": "7000" });
            $('#customerName').focus();
            return false;
        }
        if (!data.address) {
            toastr.warning('Please enter address', '', { "timeOut": "7000" });
            $('#address').focus();
            return false;
        }
        if (!data.subject) {
            toastr.warning('Please enter subject', '', { "timeOut": "7000" });
            $('#subject').focus();
            return false;
        }

        // Validate products in both annexures
        if (!validateProducts(data.annexure1.products, 'Annexure 1') || 
            !validateProducts(data.annexure2.products, 'Annexure 2')) {
            return false;
        }

        return true;
    }

    // Validate products data
    function validateProducts(products, annexureName) {
        let hasValidationError = false;
        let firstErrorField = null;

        products.forEach((product, index) => {
            const rowNum = index + 1;
            if (product.description) {
                if (!product.qty || product.qty <= 0) {
                    toastr.warning(`Please enter valid quantity for row ${rowNum} in ${annexureName}`, '', { "timeOut": "7000" });
                    const qtyField = $(`#${annexureName === 'Annexure 1' ? 'productsTable1' : 'productsTable2'} tbody tr:eq(${index}) .product-qty`);
                    if (!firstErrorField) firstErrorField = qtyField;
                    hasValidationError = true;
                }
                if (!product.rate || product.rate <= 0) {
                    toastr.warning(`Please enter valid rate for row ${rowNum} in ${annexureName}`, '', { "timeOut": "7000" });
                    const rateField = $(`#${annexureName === 'Annexure 1' ? 'productsTable1' : 'productsTable2'} tbody tr:eq(${index}) .product-rate`);
                    if (!firstErrorField) firstErrorField = rateField;
                    hasValidationError = true;
                }
            }
        });

        if (hasValidationError && firstErrorField) {
            firstErrorField.focus();
            return false;
        }

        return true;
    }

    // Add visual feedback for required fields
    $('#customerName, #address, #subject').on('input', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('field-error');
        }
    });

    // Add class to highlight required fields
    function highlightRequiredField(field) {
        $(field).addClass('field-error').focus();
    }

    // Add style for required field highlighting
    $('<style>')
        .text(`
            .field-error {
                border-color: #dc2626 !important;
                background-color: #fef2f2 !important;
                box-shadow: 0 0 0 1px #dc2626 !important;
            }
            .field-error:focus {
                box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
            }
        `)
        .appendTo('head');
});