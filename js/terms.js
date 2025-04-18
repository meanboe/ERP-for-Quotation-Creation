$(document).ready(function() {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "timeOut": "5000"
    };

    // Load terms when page loads
    loadTerms();

    // Handle sidebar toggle
    $('#sidebarCollapse').click(function() {
        $('#sidebar, #content').toggleClass('active');
    });

    // Load terms for both annexures
    function loadTerms() {
        $.ajax({
            url: '../backend/getTerms.php',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    // Sort terms by annexure type
                    const annexure1Terms = response.data.filter(term => term.annexure_type === 1);
                    const annexure2Terms = response.data.filter(term => term.annexure_type === 2);

                    // Display terms in respective tables
                    displayTerms('annexure1Table', annexure1Terms);
                    displayTerms('annexure2Table', annexure2Terms);
                } else {
                    toastr.error('Error loading terms: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Failed to communicate with server');
            }
        });
    }

    // Display terms in specified table
    function displayTerms(tableId, terms) {
        const tbody = $(`#${tableId} tbody`);
        tbody.empty();

        terms.sort((a, b) => a.sort_order - b.sort_order);

        terms.forEach((term, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${term.term_text}</td>
                    <td>${term.sort_order}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-term" 
                            data-bs-toggle="modal" 
                            data-bs-target="#termModal"
                            data-term='${JSON.stringify(term)}'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-term" data-id="${term.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Handle showing add/edit term modal
    $('#termModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        const modal = $(this);
        
        // Clear form
        $('#termForm')[0].reset();
        $('#termId').val('');

        if (button.hasClass('edit-term')) {
            const term = button.data('term');
            modal.find('.modal-title').text('Edit Term');
            $('#termId').val(term.id);
            $('#annexureType').val(term.annexure_type);
            $('#termText').val(term.term_text);
            $('#sortOrder').val(term.sort_order);
        } else {
            modal.find('.modal-title').text('Add New Term');
        }
    });

    // Handle saving term
    $('#saveTerm').click(function() {
        const termData = {
            id: $('#termId').val() || null,
            annexure_type: parseInt($('#annexureType').val()),
            term_text: $('#termText').val().trim(),
            sort_order: parseInt($('#sortOrder').val())
        };

        if (!validateTermData(termData)) return;

        $.ajax({
            url: '../backend/saveTerm.php',
            type: 'POST',
            data: JSON.stringify(termData),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#termModal').modal('hide');
                    toastr.success('Term saved successfully');
                    loadTerms();
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Failed to communicate with server');
            }
        });
    });

    // Handle showing delete confirmation modal
    $(document).on('click', '.delete-term', function() {
        const termId = $(this).data('id');
        $('#deleteModal').data('termId', termId).modal('show');
    });

    // Handle deleting term
    $('#confirmDelete').click(function() {
        const termId = $('#deleteModal').data('termId');

        $.ajax({
            url: '../backend/deleteTerm.php',
            type: 'POST',
            data: { id: termId },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.status === 'success') {
                    toastr.success('Term deleted successfully');
                    loadTerms();
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() {
                $('#deleteModal').modal('hide');
                toastr.error('Failed to communicate with server');
            }
        });
    });

    // Validate term data
    function validateTermData(data) {
        if (!data.term_text) {
            toastr.warning('Please enter the term text');
            $('#termText').focus();
            return false;
        }
        if (isNaN(data.sort_order) || data.sort_order < 0) {
            toastr.warning('Please enter a valid sort order (0 or greater)');
            $('#sortOrder').focus();
            return false;
        }
        return true;
    }
});