<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    // Get annexure type if provided
    $annexureType = isset($_GET['annexure_type']) ? (int)$_GET['annexure_type'] : null;
    
    // Base query
    $query = "SELECT * FROM terms_conditions";
    
    // Add where clause if annexure_type provided
    if ($annexureType) {
        $query .= " WHERE annexure_type = ?";
    }
    
    // Add order by
    $query .= " ORDER BY sort_order ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($annexureType) {
        mysqli_stmt_bind_param($stmt, 'i', $annexureType);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing query: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    $terms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $terms[] = [
            'id' => $row['id'],
            'annexure_type' => $row['annexure_type'],
            'term_text' => $row['term_text'],
            'sort_order' => $row['sort_order'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $terms
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);