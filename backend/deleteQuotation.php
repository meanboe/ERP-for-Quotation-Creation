<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

if (!isset($_POST['ref_no'])) {
    echo json_encode(['status' => 'error', 'message' => 'Reference number is required']);
    exit;
}

$refNo = $_POST['ref_no'];

try {
    mysqli_begin_transaction($conn);

    // First delete products
    $deleteProducts = "DELETE FROM quotation_products WHERE quotation_ref = ?";
    $stmt = mysqli_prepare($conn, $deleteProducts);
    if (!$stmt) {
        throw new Exception('Error preparing delete products statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $refNo);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting products: ' . mysqli_stmt_error($stmt));
    }

    // Then delete quotation
    $deleteQuotation = "DELETE FROM quotations WHERE ref_no = ?";
    $stmt = mysqli_prepare($conn, $deleteQuotation);
    if (!$stmt) {
        throw new Exception('Error preparing delete quotation statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $refNo);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting quotation: ' . mysqli_stmt_error($stmt));
    }

    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'Quotation deleted successfully']);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);