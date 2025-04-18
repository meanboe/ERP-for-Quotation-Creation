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

    // Start transaction
    mysqli_begin_transaction($conn);

    // Convert date from dd/mm/yyyy to Y-m-d format for MySQL
    $mysqlDate = date('Y-m-d'); // Default to current date if not provided
    if (!empty($data['date'])) {
        $dateParts = explode('/', $data['date']);
        if (count($dateParts) === 3) {
            $mysqlDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        }
    }

    // Validate products
    if (empty($data['annexure1']['products']) && empty($data['annexure2']['products'])) {
        throw new Exception('At least one product is required');
    }

    // Update quotation
    $updateQuotation = "UPDATE quotations SET 
        customer_name = ?,
        quote_date = ?,
        address = ?,
        subject = ?,
        revision = ?,
        annexure1_subtotal = ?,
        annexure1_gst = ?,
        annexure1_roundoff = ?,
        annexure1_total = ?,
        annexure1_terms = ?,
        annexure2_subtotal = ?,
        annexure2_gst = ?,
        annexure2_roundoff = ?,
        annexure2_total = ?,
        annexure2_terms = ?,
        final_total = ?
        WHERE ref_no = ?";

    $stmt = mysqli_prepare($conn, $updateQuotation);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    // Convert numeric values to float and ensure they are not null
    $annexure1SubTotal = floatval($data['annexure1']['subTotal'] ?? 0);
    $annexure1Gst = floatval($data['annexure1']['gst'] ?? 0);
    $annexure1RoundOff = floatval($data['annexure1']['roundOff'] ?? 0);
    $annexure1Total = floatval($data['annexure1']['total'] ?? 0);
    $annexure2SubTotal = floatval($data['annexure2']['subTotal'] ?? 0);
    $annexure2Gst = floatval($data['annexure2']['gst'] ?? 0);
    $annexure2RoundOff = floatval($data['annexure2']['roundOff'] ?? 0);
    $annexure2Total = floatval($data['annexure2']['total'] ?? 0);
    $finalTotal = floatval($data['finalTotal'] ?? 0);

    mysqli_stmt_bind_param($stmt, 'sssssddddsddddsdd',
        $data['customerName'],
        $mysqlDate,
        $data['address'],
        $data['subject'],
        $data['revision'],
        $annexure1SubTotal,
        $annexure1Gst,
        $annexure1RoundOff,
        $annexure1Total,
        $data['annexure1']['terms'],
        $annexure2SubTotal,
        $annexure2Gst,
        $annexure2RoundOff,
        $annexure2Total,
        $data['annexure2']['terms'],
        $finalTotal,
        $data['refNo']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating quotation: ' . mysqli_stmt_error($stmt));
    }

    // Delete existing products
    $deleteProducts = "DELETE FROM quotation_products WHERE quotation_ref = ?";
    $stmt = mysqli_prepare($conn, $deleteProducts);
    mysqli_stmt_bind_param($stmt, 's', $data['refNo']);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting existing products: ' . mysqli_stmt_error($stmt));
    }

    // Insert updated products for Annexure 1
    if (!empty($data['annexure1']['products'])) {
        foreach ($data['annexure1']['products'] as $product) {
            if (!empty($product['description'])) {
                insertProduct($conn, $data['refNo'], 1, $product);
            }
        }
    }

    // Insert updated products for Annexure 2
    if (!empty($data['annexure2']['products'])) {
        foreach ($data['annexure2']['products'] as $product) {
            if (!empty($product['description'])) {
                insertProduct($conn, $data['refNo'], 2, $product);
            }
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Helper function to insert products
function insertProduct($conn, $refNo, $annexureNo, $product) {
    if (empty($product['description'])) {
        return; // Skip empty products
    }

    $insertProduct = "INSERT INTO quotation_products (
        quotation_ref, annexure_no, description, unit, quantity, rate, total
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertProduct);
    if (!$stmt) {
        throw new Exception('Error preparing product statement: ' . mysqli_error($conn));
    }

    $qty = floatval($product['qty'] ?? 0);
    $rate = floatval($product['rate'] ?? 0);
    $total = floatval($product['total'] ?? 0);

    mysqli_stmt_bind_param($stmt, 'sissddd',
        $refNo,
        $annexureNo,
        $product['description'],
        $product['unit'],
        $qty,
        $rate,
        $total
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error saving product: ' . mysqli_stmt_error($stmt));
    }
}

mysqli_close($conn);