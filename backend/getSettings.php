<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    $query = "SELECT 
        company_name,
        contact_person,
        mobile_no,
        email_id,
        gst_no,
        company_address,
        quotation_letter
    FROM company_settings 
    WHERE id = 1";

    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Error fetching settings: ' . mysqli_error($conn));
    }

    $settings = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'status' => 'success',
        'data' => $settings
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);