<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

// Set timezone to India
date_default_timezone_set('Asia/Kolkata');

// Create users table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    logintime VARCHAR(50) DEFAULT NULL
)";

if (!mysqli_query($conn, $createTable)) {
    die("Error creating table: " . mysqli_error($conn));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't escape password for binary comparison

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields']);
        exit;
    }

    // First get user by username only
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Now compare password with case sensitivity
        if (strcmp($password, $row['password']) === 0) {
            // Format current time in Indian format
            $loginTime = date('d M Y h:i A');
            
            // Update login time
            $updateQuery = "UPDATE users SET logintime = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $loginTime, $row['id']);
            mysqli_stmt_execute($updateStmt);

            // Start session and set user data
            session_start();
            $_SESSION['user'] = $row['username'];

            echo json_encode(['status' => 'success', 'message' => 'Login successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }

    mysqli_stmt_close($stmt);
    if (isset($updateStmt)) {
        mysqli_stmt_close($updateStmt);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>