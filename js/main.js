$(document).ready(function () {
    // Initial mobile check
    function checkMobile() {
        if ($(window).width() <= 991) {
            $('#sidebar').removeClass('active');
            $('#content').addClass('active');
        }
    }

    // Run on page load
    checkMobile();

    // Add overlay div for mobile if it doesn't exist
    if (!$('.overlay').length) {
        $('body').append('<div class="overlay"></div>');
    }



    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            checkMobile();

            // Adjust table container on resize
            if ($('.table-responsive').length) {
                $('.table-responsive').css('max-width', $('#content').width());
            }

            if ($(window).width() <= 768) {
                $('#content').removeClass('active');
                $('#sidebar').removeClass('active');
            }

            // Hide overlay on larger screens
            if ($(window).width() > 991) {
                $('.overlay').removeClass('active');
            }
        }, 250);
    }).trigger('resize');

    // Add animation to stats cards
    $('.stats-card').each(function (index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        }).delay(100 * index).animate({
            'opacity': '1',
            'transform': 'translateY(0)'
        }, 500);
    });



    // Add smooth hover effect to action buttons
    $('.btn-sm').hover(
        function () { $(this).addClass('shadow-sm'); },
        function () { $(this).removeClass('shadow-sm'); }
    );

    // Add ripple effect to buttons
    $('.btn').on('click', function (e) {
        var ripple = $('<span class="ripple"></span>');
        var x = e.clientX - $(this).offset().left;
        var y = e.clientY - $(this).offset().top;

        ripple.css({
            left: x + 'px',
            top: y + 'px'
        });

        $(this).append(ripple);

        setTimeout(function () {
            ripple.remove();
        }, 1000);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "timeOut": "5000"
    };

    // Load quotations when page loads
    loadQuotations();

    // Load quotation stats when page loads
    loadQuotationStats();


    // Load quotations from backend
    function loadQuotations() {
        $.ajax({
            url: 'backend/getQuotations.php',
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    displayQuotations(response.data);
                } else {
                    toastr.error('Error loading quotations: ' + response.message);
                }
            },
            error: function () {
                toastr.error('Failed to communicate with server');
            }
        });
    }

    // Display quotations in table
    function displayQuotations(quotations) {
        const tbody = $('#quotationsTableBody');
        tbody.empty();

        quotations.forEach(function (quote) {
            const row = `
                <tr>
                    <td>${quote.ref_no}</td>
                    <td>${quote.customer_name}</td>
                    <td>${quote.created_by}</td>
                    <td>${quote.subject}</td>
                    <td>${quote.created_at}</td>
                    <td>
                        <button class="btn btn-sm btn-primary print-btn" data-ref="${quote.ref_no}" title="Print">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-btn" data-ref="${quote.ref_no}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-ref="${quote.ref_no}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }


    // Handle print button click
    $(document).on('click', '.print-btn', function () {
        const refNo = $(this).data('ref');
        $.ajax({
            url: 'backend/getQuotations.php',
            type: 'GET',
            data: { ref_no: refNo },
            success: function (response) {
                if (response.status === 'success' && response.data.length > 0) {
                    const quotation = response.data[0];
                    showPrintModal(quotation);
                }
            }
        });
    });

    // Show print modal with quotation data
    function showPrintModal(quotation) {
        const printContent = generatePrintContent(quotation);
        $('#printContent').html(printContent);
        $('#printModal').modal('show');
    }

    // Add function to load terms for print/PDF
    function loadTermsForPrint(annexureType) {
        return new Promise((resolve) => {
            $.ajax({
                url: 'backend/getTerms.php',
                type: 'GET',
                data: { annexure_type: annexureType },
                success: function (response) {
                    if (response.status === 'success') {
                        const terms = response.data;
                        let termText = '';
                        terms.sort((a, b) => a.sort_order - b.sort_order);
                        terms.forEach((term, index) => {
                            termText += `<div class="term-item"><span class="term-number">${index + 1}.</span> ${term.term_text}</div>`;
                        });
                        resolve(termText.trim());
                    } else {
                        resolve('');
                    }
                },
                error: function () {
                    resolve('');
                }
            });
        });
    }

    // Add CSS for terms display in the generatePrintContent
    const termStyles = `
        <style>
            .term-item {
                margin-bottom: 5px;
            }
            .term-number {
                display: inline-block;
                width: 25px;
                font-weight: normal;
            }
        </style>
    `;

    // Modify the generatePrintContent function to use both default and dynamic terms
    function generatePrintContent(quotation) {
        // Fetch products for both annexures
        return $.ajax({
            url: 'backend/getQuotationProducts.php',
            type: 'GET',
            data: { ref_no: quotation.ref_no },
            success: function (productsResponse) {
                if (productsResponse.status === 'success') {
                    const annexure1Products = productsResponse.data.annexure1;
                    const annexure2Products = productsResponse.data.annexure2;

                    let showAnnexure1 = annexure1Products && annexure1Products.length > 0;
                    let showAnnexure2 = annexure2Products && annexure2Products.length > 0;

                    let annexure1ProductsHtml = showAnnexure1 ? generateProductsTable(annexure1Products) : '';
                    let annexure2ProductsHtml = showAnnexure2 ? generateProductsTable(annexure2Products) : '';

                    // First load terms for both annexures
                    Promise.all([
                        loadTermsForPrint(1),
                        loadTermsForPrint(2)
                    ]).then(([annexure1DynamicTerms, annexure2DynamicTerms]) => {
                        // Then fetch settings and generate content
                        $.ajax({
                            url: 'backend/getSettings.php',
                            type: 'GET',
                            success: function (settingsResponse) {
                                if (settingsResponse.status === 'success') {
                                    const settings = settingsResponse.data;
                                    const content = `
                                        ${termStyles}
                                        <div class="print-wrapper">
                                            <!-- Page 1: Letter Details -->
                                            <div class="page letter-details">
                                                <div class="company-header">
                                                    <img src="assets/images/logo.svg" alt="Logo" style="max-width: 200px;">
                                                </div>
                                                
                                                <div class="quotation-details">
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong>Ref No:</strong> <span class="ref-no">${quotation.ref_no}</span><br>
                                                            <strong>Date:</strong> ${quotation.quote_date}<br>
                                                            <strong>Revision:</strong> R${quotation.revision}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="customer-details mb-3">
                                                        <strong>To,</strong><br>
                                                        ${quotation.customer_name}<br>
                                                        ${quotation.address.replace(/\n/g, '<br>')}
                                                    </div>
                                                    
                                                    <div class="subject mb-4">
                                                        <strong>Subject:</strong> ${quotation.subject}
                                                    </div>

                                                    ${settings.quotation_letter ? `<div class="quotation-letter mb-3">${settings.quotation_letter.replace(/\n/g, '<br>')}</div>` : ''}
                                                    
                                                    <div class="signature-section">
                                                        <p class="mb-0">Yours Faithfully</p>
                                                        <p class="mb-0"><strong>${settings.company_name || ''}</strong></p>
                                                        <p class="mb-0">${settings.contact_person || ''}</p>
                                                        <p class="mb-0">+91 ${settings.mobile_no || ''}</p>
                                                        <p class="mb-0">Email - ${settings.email_id || ''}</p>
                                                        <p class="mb-0">${settings.company_address ? settings.company_address.replace(/\n/g, '<br>') : ''}</p>
                                                        <p class="mb-0">GSTIN - ${settings.gst_no || ''}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            ${showAnnexure1 ? `
                                            <!-- Page 2: Annexure 1 -->
                                            <div class="page annexure-1">
                                                <div class="annexure mb-4">
                                                    <h4 class="mb-3">Annexure 1 - Supply of Unit</h4>
                                                    <div class="table-responsive">
                                                        ${annexure1ProductsHtml}
                                                        <table class="table table-bordered annexureTabletotal1">
                                                            <tr>
                                                                <td width="80%"><strong>Sub Total:</strong></td>
                                                                <td class="text-end">₹${parseFloat(quotation.annexure1_subtotal).toFixed(2)}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>GST (28%):</strong></td>
                                                                <td class="text-end">₹${parseFloat(quotation.annexure1_gst).toFixed(2)}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Round Off:</strong></td>
                                                                <td class="text-end">₹${parseFloat(quotation.annexure1_roundoff).toFixed(2)}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Total with GST:</strong></td>
                                                                <td class="text-end">₹${parseFloat(quotation.annexure1_total).toFixed(2)}</td>
                                                            </tr>
                                                        </table>
                                                        <div class="terms termAnnexure1 mt-3">
                                                            <strong>A) Terms & Conditions:</strong><br>
                                                            ${quotation.annexure1_terms}<br><br>
                                                            <strong>B) General Terms & Conditions:</strong><br>
                                                            ${annexure1DynamicTerms}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            ` : ''}

                                            ${showAnnexure2 ? `
                                            <!-- Page 3: Annexure 2 and Final Total -->
                                            <div class="page annexure-2">
                                                <div class="annexure mb-4">
                                                    <h4 class="mb-3">Annexure 2 - Supply of Accessories</h4>
                                                    ${annexure2ProductsHtml}
                                                    <table class="table table-bordered annexureTabletotal2">
                                                        <tr>
                                                            <td width="80%"><strong>Sub Total:</strong></td>
                                                            <td class="text-end">₹${parseFloat(quotation.annexure2_subtotal).toFixed(2)}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>GST (18%):</strong></td>
                                                            <td class="text-end">₹${parseFloat(quotation.annexure2_gst).toFixed(2)}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Round Off:</strong></td>
                                                            <td class="text-end">₹${parseFloat(quotation.annexure2_roundoff).toFixed(2)}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total with GST:</strong></td>
                                                            <td class="text-end">₹${parseFloat(quotation.annexure2_total).toFixed(2)}</td>
                                                        </tr>
                                                    </table>
                                                    <div class="terms termAnnexure2 mt-3">
                                                        <strong>A) Terms & Conditions:</strong><br>
                                                        ${quotation.annexure2_terms}<br><br>
                                                        <strong>B) General Terms & Conditions:</strong><br>
                                                        ${annexure2DynamicTerms}
                                                    </div>
                                                </div>

                                                <div class="final-section">
                                                    <div class="final-total">
                                                        <table class="table table-bordered finalTotal">
                                                            <tr>
                                                                <td width="80%"><strong>Final Total:</strong></td>
                                                                <td class="text-end">₹${parseFloat(quotation.final_total).toFixed(2)}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            ` : ''}
                                            ${(!showAnnexure1 && !showAnnexure2) ? `
                                            <div class="no-products-message text-center mt-4">
                                                <h4>No products added to any annexure.</h4>
                                            </div>
                                            ` : ''}
                                        </div>
                                    `;
                                    $('#printContent').html(content);
                                }
                            }
                        });
                    });
                }
            }
        });
    }

    // Helper function to generate products table HTML
    function generateProductsTable(products) {
        if (!products || products.length === 0) return '';

        return `
            <table class="table table-bordered annexureTable mb-3">
                <thead>
                    <tr>
                        <th width="5%">Sr No.</th>
                        <th width="40%">Description</th>
                        <th width="15%">Unit</th>
                        <th width="10%">Qty</th>
                        <th width="15%">Rate</th>
                        <th width="15%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${products.map((product, index) => `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${product.description}</td>
                            <td>${product.unit}</td>
                            <td class="text-end">${parseFloat(product.quantity).toFixed(2)}</td>
                            <td class="text-end">₹${parseFloat(product.rate).toFixed(2)}</td>
                            <td class="text-end">₹${parseFloat(product.total).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    // // Handle print button click in modal
    // $('#printQuotation').click(function () {
    //     const content = document.getElementById('printContent');
    //     if (content) {
    //         window.print();
    //     }
    // });

    // Handle download PDF button click
    $('#downloadPdf').click(function () {
        const element = document.getElementById('printContent');
        const refNo = $(element).find('.quotation-details .ref-no').text().trim() || 'quotation';

        // Get quotation data from the current modal content
        const quotationData = {
            annexure1_terms: $(element).find('.annexure-1 .terms').first().text().split('A) Terms & Conditions:')[1].split('B) General Terms & Conditions:')[0].trim(),
            annexure2_terms: $(element).find('.annexure-2 .terms').first().text().split('A) Terms & Conditions:')[1].split('B) General Terms & Conditions:')[0].trim()
        };

        // Check if annexures have products
        const hasAnnexure1Products = $(element).find('.annexure-1 .annexureTable tbody tr').length > 0;
        const hasAnnexure2Products = $(element).find('.annexure-2 .annexureTable tbody tr').length > 0;

        // Initialize jsPDF with better settings
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
            compress: true,
            precision: 4
        });

        // Load company logo with higher quality
        const logoImg = $(element).find('.company-header img')[0];
        if (logoImg) {
            const canvas = document.createElement('canvas');
            canvas.width = logoImg.width * 2;
            canvas.height = logoImg.height * 2;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(logoImg, 0, 0, canvas.width, canvas.height);
            const logoData = canvas.toDataURL('image/png', 1.0);

            doc.addImage(logoData, 'PNG', 15, 15, 50, 18.5);
        }

        // Add first page
        addFirstPage(doc, element);

        // Add Annexure 1 only if it has products
        if (hasAnnexure1Products) {
            doc.addPage();
            addAnnexure1Page(doc, element, quotationData);
        }

        // Add Annexure 2 only if it has products
        if (hasAnnexure2Products) {
            doc.addPage();
            addAnnexure2Page(doc, element, quotationData);
        }

        // Save with higher quality settings
        try {
            doc.save(`Quotation_${refNo}.pdf`);
        } catch (error) {
            console.error('Error generating PDF:', error);
            toastr.error('Error generating PDF. Please try again.');
        }
    });

    // Helper function to add first page content with improved layout
    function addFirstPage(doc, element) {
        // Set font settings
        doc.setFont('helvetica');
        doc.setFontSize(11);

        // Add quotation details with proper spacing
        const details = $(element).find('.quotation-details');
        let yPos = 50; // Start position after logo

        // Reference number and date
        doc.setFontSize(11);
        doc.text('Ref No.: ' + $(details).find('.ref-no').text(), 15, yPos);
        yPos += 6;
        doc.text('Date: ' + $(details).find('strong:contains("Date:")')[0].nextSibling.nodeValue.trim(), 15, yPos);
        yPos += 6;
        doc.text('Revision: ' + $(details).find('strong:contains("Revision:")').parent().text().split('Revision:')[1].trim(), 15, yPos);

        // Add customer details with proper spacing
        yPos += 15;
        const customerDetails = $(element).find('.customer-details');
        doc.setFontSize(11);
        doc.text('To,', 15, yPos);
        yPos += 7;

        // Split customer details into lines and add with proper spacing
        let customerText = $(customerDetails).text()
            .replace(/\s*To,\s*/, '')        // remove "To," with any extra spaces
            .replace(/\n\s*/g, '\n')         // clean extra spaces after line breaks
            .trim();
        let customerLines = doc.splitTextToSize(customerText, 180);
        doc.text(customerLines, 15, yPos);
        yPos += (customerLines.length * 7) + 5;

        // Add subject with proper formatting
        const subject = $(element).find('.subject');
        let subjectText = $(subject).text().split('Subject:')[1].trim();
        let subjectLines = doc.splitTextToSize(subjectText, 180);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(13);
        doc.text('Subject: ' + subjectLines, 15, yPos);
        yPos += (subjectLines.length * 5) + 10;

        // Add quotation letter if exists
        const letter = $(element).find('.quotation-letter');
        if (letter.length) {
            let letterHTML = $('.quotation-letter').html();
            let letterText = letterHTML
                .replace(/<br\s*\/?>/gi, '\n')  // convert <br> to new lines
                .replace(/<[^>]+>/g, '')        // remove any other HTML tags
                .trim();
            let letterLines = doc.splitTextToSize(letterText, 240);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(11);
            doc.text(15, yPos, letterLines);
            yPos += (letterLines.length * 5) + 10;
        }

        // Add signature section at the bottom
        const signature = $(element).find('.signature-section');
        doc.setFontSize(11);

        doc.text('Yours Faithfully,', 15, yPos);
        yPos += 6;
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text($(signature).find('strong').first().text(), 15, yPos);
        yPos += 6;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);

        // Add company details
        const companyDetails = [
            $(signature).find('p:eq(2)').text(), // Contact person
            '+91 ' + $(signature).find('p:eq(3)').text().split('+91 ')[1], // Mobile
            'Email - ' + $(signature).find('p:eq(4)').text().split('Email - ')[1], // Email
            $(signature).find('p:eq(5)').text(), // Address
            'GSTIN - ' + $(signature).find('p:eq(6)').text().split('GSTIN - ')[1] // GST
        ];

        companyDetails.forEach(detail => {
            if (detail.trim()) {
                doc.text(15, yPos, detail);
                yPos += 6;
            }
        });
    }

    // Helper function to convert HTML terms to PDF format with numbers
    function processTermsForPDF(termsHtml) {
        const div = document.createElement('div');
        div.innerHTML = termsHtml;
        let result = '';
        div.querySelectorAll('.term-item').forEach(item => {
            result += item.textContent.trim() + '\n';
        });
        return result.trim();
    }

    // Helper function to add Annexure 1 page with improved layout
    function addAnnexure1Page(doc, element, quotation) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.line(14, 10, 199, 10);
        let yPos = 12;

        // Load company logo with higher quality
        const logoImg = $(element).find('.company-header img')[0];
        if (logoImg) {
            const canvas = document.createElement('canvas');
            canvas.width = logoImg.width * 2;
            canvas.height = logoImg.height * 2;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(logoImg, 0, 0, canvas.width, canvas.height);
            const logoData = canvas.toDataURL('image/png', 1.0);

            doc.addImage(logoData, 'PNG', 16, yPos, 33, 12);
        }

        yPos += 14; // Adjust position after logo

        doc.line(14, yPos, 199, yPos);
        doc.setFont('helvetica', 'normal');

        yPos += 4; // Adjust position after line
        const pageWidth = 210;
        const title = 'ANNEXURE - Supply of UNITS';
        const textWidth = doc.getTextWidth(title);
        const x = (pageWidth - textWidth) / 2;
        doc.text(title, x, yPos);
        yPos += 2;
        doc.line(14, 10, 14, yPos);
        doc.line(199, 10, 199, yPos);

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });

        yPos = doc.autoTable.previous.finalY;
        // Add products table with improved formatting
        const table1Data = [];
        $(element).find('.annexure-1 .annexureTable tbody tr').each(function () {
            const row = [];
            $(this).find('td').each(function () {
                row.push($(this).text().trim().replace(/[₹\u20B9\^\¹]/g, 'Rs. '));
            });

            table1Data.push(row);
        });

        // Configure table with better styling
        doc.autoTable({
            startY: yPos,
            head: [['#', 'WORK DESCRIPTION', 'UNIT', 'QTY', 'RATE', 'TOTAL']],
            body: table1Data,
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10 },
                1: { cellWidth: 80 },
                2: { cellWidth: 15 },
                3: { cellWidth: 20 },
                4: { cellWidth: 25 },
                5: { cellWidth: 35 }
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0', '1', '2', '3', '4', '5'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

                if (data.section === 'body' && ['0', '2', '3'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

                if (data.section === 'body' && ['4', '5'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'left';
                }

            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        const totalsTable = [];
        const totalsSection = $(element).find('.annexure-1 .annexureTabletotal1 tr');

        totalsSection.each(function () {
            const label = $(this).find('td:first').text().trim();
            let value = $(this).find('td:last').text().trim();

            // Remove all invisible or weird symbols including ₹
            value = value.replace(/[₹\u20B9\^\¹]/g, 'Rs. '); // replace all misbehaving currency symbols

            totalsTable.push(['', '', label, value]);
        });

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: totalsTable,
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 10 },
                1: { cellWidth: 80 },
                2: { cellWidth: 60, halign: 'right' },
                3: { cellWidth: 35, halign: 'left' }
            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });


        // Add terms and conditions
        yPos = doc.autoTable.previous.finalY;
        doc.setFontSize(11);


        const totalTermTable = [];

        totalTermTable.push(['1', quotation.annexure1_terms]);

        // Add totals table
        doc.autoTable({
            startY: yPos,
            head: [['A', 'TOP & Taxes']],
            body: totalTermTable,
            theme: 'plain',
            styles: {
                fontSize: 9,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                1: { cellWidth: 175 },
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

            
            }
        });

        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });


        // Add terms and conditions
        yPos = doc.autoTable.previous.finalY;


        const totalgenTermTable = [];

        $(element).find('.termAnnexure1 .term-item').each(function () {
            const row = [];
            $(this).each(function () {
                row.push($(this).find('span').text().trim().replace(/\./g, ''), $(this).text().trim().replace(/^\d+\.\s*/, ''));
            });

            totalgenTermTable.push(row);
        });

        

        // Add totals table
        doc.autoTable({
            startY: yPos,
            head: [['B', 'General Terms & Conditions']],
            body: totalgenTermTable,
            theme: 'plain',
            styles: {
                fontSize: 9,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center'},
                1: { cellWidth: 175 },
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }
            }
        });

        yPos = doc.autoTable.previous.finalY + 0.5 - 9.5;
        doc.rect(13.5, 9.5, 186, yPos);
    }

    // Helper function to add Annexure 2 page with improved layout
    function addAnnexure2Page(doc, element, quotation) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.line(14, 10, 199, 10);
        let yPos = 12;

        // Load company logo with higher quality
        const logoImg = $(element).find('.company-header img')[0];
        if (logoImg) {
            const canvas = document.createElement('canvas');
            canvas.width = logoImg.width * 2;
            canvas.height = logoImg.height * 2;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(logoImg, 0, 0, canvas.width, canvas.height);
            const logoData = canvas.toDataURL('image/png', 1.0);

            doc.addImage(logoData, 'PNG', 16, yPos, 33, 12);
        }

        yPos += 14; // Adjust position after logo

        doc.line(14, yPos, 199, yPos);
        doc.setFont('helvetica', 'normal');

        yPos += 4; // Adjust position after line
        const pageWidth = 210;
        const title = 'ANNEXURE';
        const textWidth = doc.getTextWidth(title);
        const x = (pageWidth - textWidth) / 2;
        doc.text(title, x, yPos);
        yPos += 2;
        doc.line(14, 10, 14, yPos);
        doc.line(199, 10, 199, yPos);

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });

        yPos = doc.autoTable.previous.finalY;
        // Add products table with improved formatting
        const table1Data = [];
        $(element).find('.annexure-2 .annexureTable tbody tr').each(function () {
            const row = [];
            $(this).find('td').each(function () {
                row.push($(this).text().trim().replace(/[₹\u20B9\^\¹]/g, 'Rs. '));
            });

            table1Data.push(row);
        });

        // Configure table with better styling
        doc.autoTable({
            startY: yPos,
            head: [['#', 'WORK DESCRIPTION', 'UNIT', 'QTY', 'RATE', 'TOTAL']],
            body: table1Data,
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10 },
                1: { cellWidth: 80 },
                2: { cellWidth: 15 },
                3: { cellWidth: 20 },
                4: { cellWidth: 25 },
                5: { cellWidth: 35 }
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0', '1', '2', '3', '4', '5'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

                if (data.section === 'body' && ['0', '2', '3'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

                if (data.section === 'body' && ['4', '5'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'left';
                }

            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        const totalsTable = [];
        const totalsSection = $(element).find('.annexure-2 .annexureTabletotal2 tr');

        totalsSection.each(function () {
            const label = $(this).find('td:first').text().trim();
            let value = $(this).find('td:last').text().trim();

            // Remove all invisible or weird symbols including ₹
            value = value.replace(/[₹\u20B9\^\¹]/g, 'Rs. '); // replace all misbehaving currency symbols

            totalsTable.push(['', '', label, value]);
        });

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: totalsTable,
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 10 },
                1: { cellWidth: 80 },
                2: { cellWidth: 60, halign: 'right' },
                3: { cellWidth: 35, halign: 'left' }
            }
        });

        // Add totals section with proper formatting
        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });


        // Add terms and conditions
        yPos = doc.autoTable.previous.finalY;
        doc.setFontSize(11);

        const totalTermTable = [];

        totalTermTable.push(['1', quotation.annexure2_terms]);

        // Add totals table
        doc.autoTable({
            startY: yPos,
            head: [['A', 'TOP & Taxes']],
            body: totalTermTable,
            theme: 'plain',
            styles: {
                fontSize: 9,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                1: { cellWidth: 175 },
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }

            
            }
        });

        yPos = doc.autoTable.previous.finalY;

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['']],
            theme: 'plain',
            styles: {
                fontSize: 10,
                cellPadding: 0,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            columnStyles: {
                0: { cellWidth: 185 }
            }
        });


        // Add terms and conditions
        yPos = doc.autoTable.previous.finalY;


        const totalgenTermTable = [];

        $(element).find('.termAnnexure2 .term-item').each(function () {
            const row = [];
            $(this).each(function () {
                row.push($(this).find('span').text().trim().replace(/\./g, ''), $(this).text().trim().replace(/^\d+\.\s*/, ''));
            });

            totalgenTermTable.push(row);
        });

        

        // Add totals table
        doc.autoTable({
            startY: yPos,
            head: [['B', 'General Terms & Conditions']],
            body: totalgenTermTable,
            theme: 'plain',
            styles: {
                fontSize: 9,
                cellPadding: 1,
                lineWidth: 0.2,
                lineColor: [0, 0, 0]
            },
            headStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center'},
                1: { cellWidth: 175 },
            },
            didParseCell: function (data) {
                // Right align numbers
                if (data.section === 'head' && ['0'].includes(data.column.index.toString())) {
                    data.cell.styles.halign = 'center';
                }
            }
        });

        yPos = doc.autoTable.previous.finalY;

        const finalTotal = $(element).find('.final-total table tr').last();

        // Add totals table
        doc.autoTable({
            startY: yPos,
            body: [['Final Total:', $(finalTotal).find('td:last').text().replace(/[₹\u20B9\^\¹]/g, 'Rs. ')]],
            theme: 'plain',
            styles: {
                fontSize: 12.5,
                cellPadding: 2,
                lineWidth: 0.2,
                lineColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 150, halign: 'right' },
                1: { cellWidth: 35, halign: 'left'},
            }
        });
        

        yPos = doc.autoTable.previous.finalY + 0.5 - 9.5;
        doc.rect(13.5, 9.5, 186, yPos);
    }

    // Handle edit button click
    $(document).on('click', '.edit-btn', function () {
        const refNo = $(this).data('ref');

        $.ajax({
            url: 'backend/getQuotations.php',
            type: 'GET',
            data: { ref_no: refNo },
            success: function (response) {
                if (response.status === 'success' && response.data.length > 0) {
                    const quotation = response.data[0];

                    // Format date to dd/mm/yyyy
                    const date = new Date(quotation.quote_date);
                    const formattedDate = date.getDate().toString().padStart(2, '0') + '/' +
                        (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
                        date.getFullYear();

                    // Fill in basic details
                    $('#editRefNo').val(quotation.ref_no);
                    $('#editCustomerName').val(quotation.customer_name || '');
                    $('#editDate').val(formattedDate);
                    $('#editAddress').val(quotation.address || '');
                    $('#editSubject').val(quotation.subject || '');

                    // Increment revision number
                    const currentRevision = parseInt(quotation.revision || '0');
                    $('#editRevision').val((currentRevision + 1).toString().padStart(2, '0'));

                    // Fill in annexure details with proper number formatting
                    $('#editSubTotal1').val(parseFloat(quotation.annexure1_subtotal || 0).toFixed(2));
                    $('#editGst1').val(parseFloat(quotation.annexure1_gst || 0).toFixed(2));
                    $('#editRoundOff1').val(parseFloat(quotation.annexure1_roundoff || 0).toFixed(2));
                    $('#editGrandTotal1').val(parseFloat(quotation.annexure1_total || 0).toFixed(2));
                    $('#editTerms1').val(quotation.annexure1_terms || '100% Advance against PO');

                    $('#editSubTotal2').val(parseFloat(quotation.annexure2_subtotal || 0).toFixed(2));
                    $('#editGst2').val(parseFloat(quotation.annexure2_gst || 0).toFixed(2));
                    $('#editRoundOff2').val(parseFloat(quotation.annexure2_roundoff || 0).toFixed(2));
                    $('#editGrandTotal2').val(parseFloat(quotation.annexure2_total || 0).toFixed(2));
                    $('#editTerms2').val(quotation.annexure2_terms || '70% Advance against PO, 20% Against Delivery & 10% After Installation');

                    $('#editFinalTotal').val(parseFloat(quotation.final_total || 0).toFixed(2));

                    // Get products data
                    $.ajax({
                        url: 'backend/getQuotationProducts.php',
                        type: 'GET',
                        data: { ref_no: refNo },
                        success: function (productsResponse) {
                            if (productsResponse.status === 'success') {
                                // Clear existing product rows except first row
                                $('#editProductsTable1 tbody tr:gt(0)').remove();
                                $('#editProductsTable2 tbody tr:gt(0)').remove();

                                // Clear first row inputs
                                $('#editProductsTable1 tbody tr:first input, #editProductsTable1 tbody tr:first select').val('');
                                $('#editProductsTable2 tbody tr:first input, #editProductsTable2 tbody tr:first select').val('');

                                // Fill products data
                                fillProductsTable('#editProductsTable1', productsResponse.data.annexure1);
                                fillProductsTable('#editProductsTable2', productsResponse.data.annexure2);

                                // Calculate totals after loading products
                                calculateEditTotals($('#editProductsTable1'));
                                calculateEditTotals($('#editProductsTable2'));

                                // Show the modal
                                $('#editQuotationModal').modal('show');
                            }
                        }
                    });
                }
            }
        });
    });

    // Helper function to fill products table
    function fillProductsTable(tableId, products) {
        const $table = $(tableId);
        const $firstRow = $table.find('tbody tr:first');

        // Fill in products
        products.forEach((product, index) => {
            const $row = index === 0 ? $firstRow : $firstRow.clone();

            $row.find('.product-description').val(product.description);
            $row.find('.product-unit').val(product.unit);
            $row.find('.product-qty').val(parseFloat(product.quantity).toFixed(2));
            $row.find('.product-rate').val(parseFloat(product.rate).toFixed(2));
            $row.find('.product-total').val(parseFloat(product.total).toFixed(2));

            if (index > 0) {
                $table.find('tbody').append($row);
            }
        });
    }

    // Add event handlers for edit form calculations
    $('#editQuotationModal').on('input', '.product-qty, .product-rate', function () {
        const row = $(this).closest('tr');
        const qty = parseFloat(row.find('.product-qty').val()) || 0;
        const rate = parseFloat(row.find('.product-rate').val()) || 0;
        const total = qty * rate;
        row.find('.product-total').val(total.toFixed(2));

        const table = $(this).closest('table');
        calculateEditTotals(table);
    });

    // Calculate totals for edit form with proper GST calculation
    function calculateEditTotals(table) {
        const tableId = table.attr('id');
        const gstRate = tableId === 'editProductsTable1' ? 0.28 : 0.18;
        const index = tableId === 'editProductsTable1' ? '1' : '2';

        let subTotal = 0;

        // Calculate sub total
        table.find('tbody tr').each(function () {
            const qty = parseFloat($(this).find('.product-qty').val()) || 0;
            const rate = parseFloat($(this).find('.product-rate').val()) || 0;
            const total = qty * rate;
            $(this).find('.product-total').val(total.toFixed(2));
            subTotal += total;
        });

        // Update sub total with two decimal places
        $(`#editSubTotal${index}`).val(subTotal.toFixed(2));

        // Calculate GST with two decimal places
        const gst = subTotal * gstRate;
        $(`#editGst${index}`).val(gst.toFixed(2));

        // Get round off value (if any)
        const roundOff = parseFloat($(`#editRoundOff${index}`).val()) || 0;

        // Calculate grand total with round off
        const grandTotal = subTotal + gst + roundOff;
        $(`#editGrandTotal${index}`).val(grandTotal.toFixed(2));

        // Update final total
        calculateEditFinalTotal();
    }

    // Calculate final total with proper decimal handling
    function calculateEditFinalTotal() {
        const grandTotal1 = parseFloat($('#editGrandTotal1').val()) || 0;
        const grandTotal2 = parseFloat($('#editGrandTotal2').val()) || 0;

        const finalTotal = grandTotal1 + grandTotal2;
        $('#editFinalTotal').val(finalTotal.toFixed(2));
    }

    // Handle round off changes in edit form
    $('#editRoundOff1, #editRoundOff2').on('input', function () {
        const index = $(this).attr('id').slice(-1);
        calculateEditGrandTotal(index);
        calculateEditFinalTotal();
    });

    // Handle saving edited quotation
    $('#saveQuotationChanges').click(function () {
        // Collect all form data
        const quotationData = {
            refNo: $('#editRefNo').val(),
            customerName: $('#editCustomerName').val().trim(),
            address: $('#editAddress').val().trim(),
            subject: $('#editSubject').val().trim(),
            date: $('#editDate').val(),
            revision: $('#editRevision').val(),
            annexure1: {
                products: getEditProductsData('editProductsTable1'),
                subTotal: $('#editSubTotal1').val(),
                gst: $('#editGst1').val(),
                roundOff: $('#editRoundOff1').val() || "0",
                total: $('#editGrandTotal1').val(),
                terms: $('#editTerms1').val().trim() || '100% Advance against PO'
            },
            annexure2: {
                products: getEditProductsData('editProductsTable2'),
                subTotal: $('#editSubTotal2').val(),
                gst: $('#editGst2').val(),
                roundOff: $('#editRoundOff2').val() || "0",
                total: $('#editGrandTotal2').val(),
                terms: $('#editTerms2').val().trim() || '70% Advance against PO, 20% Against Delivery & 10% After Installation'
            },
            finalTotal: $('#editFinalTotal').val()
        };

        // Validate form
        if (!validateEditForm(quotationData)) {
            return;
        }

        // Show loading indicator
        toastr.info('Saving changes...', '', { "timeOut": 0, "extendedTimeOut": 0 });

        // Send data to backend
        $.ajax({
            url: 'backend/updateQuotation.php',
            type: 'POST',
            data: JSON.stringify(quotationData),
            contentType: 'application/json',
            success: function (response) {
                toastr.clear(); // Clear the loading message
                if (response.status === 'success') {
                    $('#editQuotationModal').modal('hide');
                    toastr.success('Quotation updated successfully');
                    loadQuotations(); // Reload the table
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.clear(); // Clear the loading message
                let errorMessage = 'An error occurred while updating the quotation.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' ' + xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
            }
        });
    });

    // Helper function to get products data from edit form
    function getEditProductsData(tableId) {
        const products = [];
        $(`#${tableId} tbody tr`).each(function () {
            const row = $(this);
            const description = row.find('.product-description').val().trim();
            if (description) {
                products.push({
                    description: description,
                    unit: row.find('.product-unit').val(),
                    qty: row.find('.product-qty').val(),
                    rate: row.find('.product-rate').val(),
                    total: row.find('.product-total').val()
                });
            }
        });
        return products;
    }

    // Validate edit form
    function validateEditForm(data) {
        if (!data.customerName) {
            toastr.warning('Please enter customer name');
            $('#editCustomerName').focus();
            return false;
        }
        if (!data.address) {
            toastr.warning('Please enter address');
            $('#editAddress').focus();
            return false;
        }
        if (!data.subject) {
            toastr.warning('Please enter subject');
            $('#editSubject').focus();
            return false;
        }

        // Validate products in both annexures
        if (!validateEditProducts(data.annexure1.products, 'Annexure 1') ||
            !validateEditProducts(data.annexure2.products, 'Annexure 2')) {
            return false;
        }

        return true;
    }

    // Validate edit products data
    function validateEditProducts(products, annexureName) {
        let hasValidationError = false;
        let firstErrorField = null;

        products.forEach((product, index) => {
            if (!product.qty || product.qty <= 0) {
                toastr.warning(`Please enter valid quantity for row ${index + 1} in ${annexureName}`);
                hasValidationError = true;
            }
            if (!product.rate || product.rate <= 0) {
                toastr.warning(`Please enter valid rate for row ${index + 1} in ${annexureName}`);
                hasValidationError = true;
            }
        });

        return !hasValidationError;
    }

    // Handle adding new product row in edit form
    $('#editQuotationModal .add-product-btn').click(function () {
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

    // Handle delete button click
    $(document).on('click', '.delete-btn', function () {
        const refNo = $(this).data('ref');
        $('#deleteModal').data('ref', refNo).modal('show');
    });

    // Handle delete confirmation
    $('#confirmDelete').click(function () {
        const refNo = $('#deleteModal').data('ref');
        $.ajax({
            url: 'backend/deleteQuotation.php',
            type: 'POST',
            data: { ref_no: refNo },
            success: function (response) {
                $('#deleteModal').modal('hide');
                if (response.status === 'success') {
                    toastr.success('Quotation deleted successfully');
                    loadQuotations(); // Reload the table
                } else {
                    toastr.error('Error deleting quotation: ' + response.message);
                }
            },
            error: function () {
                $('#deleteModal').modal('hide');
                toastr.error('Failed to communicate with server');
            }
        });
    });

    // Handle sidebar collapse
    $('#sidebarCollapse').click(function () {
        $('#sidebar, #content').toggleClass('active');
    });

    // Function to load quotation stats
    function loadQuotationStats() {
        $.ajax({
            url: 'backend/getQuotationStats.php',
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    const stats = response.data;
                    $('#totalQuotations').text(stats.total);

                    // Update trend icon and text
                    const trendIcon = stats.percentageChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                    const trendClass = stats.percentageChange >= 0 ? 'text-success' : 'text-danger';

                    $('#quotationTrendIcon').removeClass().addClass(`fas ${trendIcon} me-2`);
                    $('#quotationTrend').parent()
                        .removeClass('text-success text-danger')
                        .addClass(trendClass);

                    $('#quotationTrend').text(Math.abs(stats.percentageChange) + '% from last month');
                }
            }
        });
    }

    let searchTimer;

    $('#searchInput').on('input', function () {
        const searchBox = $(this);

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const searchText = searchBox.val().toLowerCase().trim();
            const rows = $('#quotationsTableBody tr');

            // Remove previous highlights from non-action cells
            rows.each(function () {
                const row = $(this);
                row.children('td').not(':last-child').each(function () {
                    const originalText = $(this).text(); // Just text, no HTML
                    $(this).html(originalText); // Reset cell
                });
            });

            if (searchText === '') {
                rows.show();
                $('.no-results').remove();
                return;
            }

            let hasResults = false;

            rows.each(function () {
                const row = $(this);
                const searchableCells = row.children('td').not(':last-child'); // reliable way

                // Collect text of searchable cells only
                const rowText = searchableCells.map(function () {
                    return $(this).text().toLowerCase();
                }).get().join(' ');

                const isMatch = rowText.includes(searchText);
                row.toggle(isMatch);

                if (isMatch) {
                    hasResults = true;

                    // Highlight matched text in searchable cells
                    searchableCells.each(function () {
                        const cell = $(this);
                        const cellText = cell.text();
                        const regex = new RegExp(`(${searchText})`, 'gi');
                        const highlighted = cellText.replace(regex, `<span class="highlight">$1</span>`);
                        cell.html(highlighted);
                    });
                }
            });

            // Show/hide "No results" message
            if (!hasResults) {
                if (!$('.no-results').length) {
                    $('.table').after('<div class="no-results text-center py-3">No matching records found</div>');
                }
            } else {
                $('.no-results').remove();
            }

        }, 300); // debounce delay
    });


});