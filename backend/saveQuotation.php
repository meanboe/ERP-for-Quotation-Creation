<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data received: ' . json_last_error_msg()]);
    exit;
}

// Log the received data for debugging
error_log('Received data: ' . print_r($data, true));

// Function to increment quotation number
function incrementQuotationNumber($conn, $financialYear) {
    // First update the counter
    $update = "UPDATE quotation_counters SET last_number = last_number + 1 
              WHERE financial_year = ?";
    $stmt = mysqli_prepare($conn, $update);
    if (!$stmt) {
        throw new Exception('Error preparing update statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $financialYear);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating counter: ' . mysqli_stmt_error($stmt));
    }

    // Then get the new value
    $select = "SELECT last_number FROM quotation_counters WHERE financial_year = ?";
    $stmt = mysqli_prepare($conn, $select);
    if (!$stmt) {
        throw new Exception('Error preparing select statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $financialYear);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error getting counter: ' . mysqli_stmt_error($stmt));
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['last_number'];
}

// Create quotations table if it doesn't exist
$createQuotationsTable = "CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_no VARCHAR(50) UNIQUE NOT NULL,
    revision VARCHAR(10) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    subject TEXT NOT NULL,
    quote_date DATE NOT NULL,
    created_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    annexure1_subtotal DECIMAL(10,2) NOT NULL,
    annexure1_gst DECIMAL(10,2) NOT NULL,
    annexure1_roundoff DECIMAL(10,2) NOT NULL,
    annexure1_total DECIMAL(10,2) NOT NULL,
    annexure1_terms TEXT NOT NULL,
    annexure2_subtotal DECIMAL(10,2) NOT NULL,
    annexure2_gst DECIMAL(10,2) NOT NULL,
    annexure2_roundoff DECIMAL(10,2) NOT NULL,
    annexure2_total DECIMAL(10,2) NOT NULL,
    annexure2_terms TEXT NOT NULL,
    final_total DECIMAL(10,2) NOT NULL
)";

if (!mysqli_query($conn, $createQuotationsTable)) {
    echo json_encode(['status' => 'error', 'message' => 'Error creating quotations table']);
    exit;
}

// Create quotation_products table if it doesn't exist
$createProductsTable = "CREATE TABLE IF NOT EXISTS quotation_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_ref VARCHAR(50) NOT NULL,
    annexure_no INT NOT NULL,
    description TEXT NOT NULL,
    unit VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    rate DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (quotation_ref) REFERENCES quotations(ref_no)
)";

if (!mysqli_query($conn, $createProductsTable)) {
    echo json_encode(['status' => 'error', 'message' => 'Error creating products table']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Get current financial year from ref_no
    $refParts = explode('/', $data['refNo']);
    $financialYear = $refParts[1];

    // Log financial year and ref parts
    error_log('Financial Year: ' . $financialYear);
    error_log('Ref Parts: ' . print_r($refParts, true));

    // Increment the counter and get actual quotation number
    $actualNumber = incrementQuotationNumber($conn, $financialYear);
    
    // Update ref_no with actual number
    $data['refNo'] = $refParts[0] . '/' . $financialYear . '/' . $refParts[2] . '/' . 
                     str_pad($actualNumber, 2, '0', STR_PAD_LEFT);

    // Log the final ref number
    error_log('Final Ref No: ' . $data['refNo']);

    // Insert quotation data
    $insertQuotation = "INSERT INTO quotations (
        ref_no, revision, customer_name, address, subject, quote_date, created_by,
        annexure1_subtotal, annexure1_gst, annexure1_roundoff, annexure1_total, annexure1_terms,
        annexure2_subtotal, annexure2_gst, annexure2_roundoff, annexure2_total, annexure2_terms,
        final_total
    ) VALUES (?, ?, ?, ?, ?, STR_TO_DATE(?, '%d/%m/%Y'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertQuotation);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    // Convert numeric values to float
    $annexure1SubTotal = floatval($data['annexure1']['subTotal']);
    $annexure1Gst = floatval($data['annexure1']['gst']);
    $annexure1RoundOff = floatval($data['annexure1']['roundOff']);
    $annexure1Total = floatval($data['annexure1']['total']);
    $annexure2SubTotal = floatval($data['annexure2']['subTotal']);
    $annexure2Gst = floatval($data['annexure2']['gst']);
    $annexure2RoundOff = floatval($data['annexure2']['roundOff']);
    $annexure2Total = floatval($data['annexure2']['total']);
    $finalTotal = floatval($data['finalTotal']);

    // Log the values being bound
    error_log('Binding values: ' . print_r([
        'ref_no' => $data['refNo'],
        'revision' => $data['revision'],
        'customer_name' => $data['customerName'],
        'address' => $data['address'],
        'subject' => $data['subject'],
        'date' => $data['date'],
        'created_by' => $_SESSION['user'],
        'annexure1' => [
            'subtotal' => $annexure1SubTotal,
            'gst' => $annexure1Gst,
            'roundoff' => $annexure1RoundOff,
            'total' => $annexure1Total,
            'terms' => $data['annexure1']['terms']
        ],
        'annexure2' => [
            'subtotal' => $annexure2SubTotal,
            'gst' => $annexure2Gst,
            'roundoff' => $annexure2RoundOff,
            'total' => $annexure2Total,
            'terms' => $data['annexure2']['terms']
        ],
        'final_total' => $finalTotal
    ], true));

    mysqli_stmt_bind_param($stmt, 'sssssssddddsddddsd',
        $data['refNo'],
        $data['revision'],
        $data['customerName'],
        $data['address'],
        $data['subject'],
        $data['date'],
        $_SESSION['user'],
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
        $finalTotal
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing statement: ' . mysqli_stmt_error($stmt));
    }

    // Insert products for Annexure 1
    foreach ($data['annexure1']['products'] as $product) {
        if (!empty($product['description'])) {
            insertProduct($conn, $data['refNo'], 1, $product);
        }
    }

    // Insert products for Annexure 2
    foreach ($data['annexure2']['products'] as $product) {
        if (!empty($product['description'])) {
            insertProduct($conn, $data['refNo'], 2, $product);
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    echo json_encode([
        'status' => 'success', 
        'message' => 'Quotation saved successfully',
        'refNo' => $data['refNo']
    ]);

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    error_log('Error in saveQuotation.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Helper function to insert products
function insertProduct($conn, $refNo, $annexureNo, $product) {
    $insertProduct = "INSERT INTO quotation_products (
        quotation_ref, annexure_no, description, unit, quantity, rate, total
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertProduct);
    if (!$stmt) {
        throw new Exception('Error preparing product statement: ' . mysqli_error($conn));
    }

    $qty = floatval($product['qty']);
    $rate = floatval($product['rate']);
    $total = floatval($product['total']);

    // Log product data
    error_log('Inserting product: ' . print_r([
        'ref_no' => $refNo,
        'annexure_no' => $annexureNo,
        'description' => $product['description'],
        'unit' => $product['unit'],
        'qty' => $qty,
        'rate' => $rate,
        'total' => $total
    ], true));

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
        throw new Exception('Error saving product details: ' . mysqli_stmt_error($stmt));
    }
}

mysqli_close($conn);