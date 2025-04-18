<?php
// Login functionality for the application

session_start();

if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/login.js"></script>
</head>

<body>
    <div class="animated-bg">
        <div class="login-wrapper">
            <div class="login-card">
                <div class="logo-container">
                    <img src="../assets/images/logo.svg" alt="Logo">
                </div>
                <div class="material-input">
                    <input type="text" id="username" placeholder=" " required>
                    <label for="username">Username</label>
                </div>
                <div class="material-input">
                    <input type="password" id="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>
                <div id="error-message" style="color: red; margin-bottom: 10px; display: none;"></div>
                <button type="button" class="login_btn">Login</button>
            </div>
        </div>
    </div>
</body>
</html>