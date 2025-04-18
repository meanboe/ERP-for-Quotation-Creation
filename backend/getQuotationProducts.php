<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

if (!isset($_GET['ref_no'])) {
    echo json_encode(['status' => 'error', 'message' => 'Reference number is required']);
    exit;
}

$refNo = $_GET['ref_no'];

try {
    $query = "SELECT * FROM quotation_products 
              WHERE quotation_ref = ? 
              ORDER BY annexure_no, id";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 's', $refNo);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing statement: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [
        'annexure1' => [],
        'annexure2' => []
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Format numeric values to 2 decimal places
        $product = [
            'description' => $row['description'] ?? '',
            'unit' => $row['unit'] ?? 'NOS',
            'quantity' => number_format((float)$row['quantity'], 2, '.', ''),
            'rate' => number_format((float)$row['rate'], 2, '.', ''),
            'total' => number_format((float)$row['total'], 2, '.', '')
        ];
        
        if ($row['annexure_no'] == 1) {
            $products['annexure1'][] = $product;
        } else {
            $products['annexure2'][] = $product;
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $products]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);