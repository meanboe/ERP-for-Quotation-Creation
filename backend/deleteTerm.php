<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    // Get term ID from POST data
    $termId = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (!$termId) {
        throw new Exception('Term ID is required');
    }

    // Delete the term
    $query = "DELETE FROM terms_conditions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $termId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting term: ' . mysqli_stmt_error($stmt));
    }

    // Check if any rows were affected
    if (mysqli_affected_rows($conn) === 0) {
        throw new Exception('Term not found');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Term deleted successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);