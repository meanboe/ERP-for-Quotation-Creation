<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    // Get JSON data from request
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!$data) {
        throw new Exception('Invalid data received');
    }

    // Create settings table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS company_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255) NOT NULL,
        mobile_no VARCHAR(15) NOT NULL,
        email_id VARCHAR(255) NOT NULL,
        gst_no VARCHAR(20) NOT NULL,
        company_address TEXT NOT NULL,
        quotation_letter TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($conn, $createTable)) {
        throw new Exception('Error creating settings table: ' . mysqli_error($conn));
    }

    // Check if settings already exist
    $checkSettings = "SELECT id FROM company_settings LIMIT 1";
    $result = mysqli_query($conn, $checkSettings);
    $exists = mysqli_num_rows($result) > 0;

    if ($exists) {
        // Update existing settings
        $updateSettings = "UPDATE company_settings SET 
            company_name = ?,
            contact_person = ?,
            mobile_no = ?,
            email_id = ?,
            gst_no = ?,
            company_address = ?,
            quotation_letter = ?
            WHERE id = 1";
    } else {
        // Insert new settings
        $updateSettings = "INSERT INTO company_settings (
            company_name, contact_person, mobile_no, email_id, gst_no, 
            company_address, quotation_letter
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    }

    $stmt = mysqli_prepare($conn, $updateSettings);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'sssssss',
        $data['companyName'],
        $data['contactPerson'],
        $data['mobileNo'],
        $data['emailId'],
        $data['gstNo'],
        $data['companyAddress'],
        $data['quotationLetter']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error saving settings: ' . mysqli_stmt_error($stmt));
    }

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);