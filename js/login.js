$(document).ready(function() {
    $('.login_btn').click(function() {
        var username = $('#username').val();
        var password = $('#password').val();
        var errorMessage = $('#error-message');
        
        if (!username || !password) {
            errorMessage.text('Please fill in all fields').show();
            return;
        }

        $.ajax({
            url: '../backend/login.php',
            type: 'POST',
            data: {
                username: username,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = '../index.php';
                } else {
                    errorMessage.text(response.message).show();
                }
            },
            error: function() {
                errorMessage.text('An error occurred. Please try again.').show();
            }
        });
    });
});