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

    // Check if updating existing term or creating new one
    $isUpdate = isset($data['id']) && $data['id'];

    if ($isUpdate) {
        // Update existing term
        $query = "UPDATE terms_conditions SET 
            annexure_type = ?,
            term_text = ?,
            sort_order = ?
            WHERE id = ?";
            
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'isii',
            $data['annexure_type'],
            $data['term_text'],
            $data['sort_order'],
            $data['id']
        );
    } else {
        // Insert new term
        $query = "INSERT INTO terms_conditions (annexure_type, term_text, sort_order) 
                 VALUES (?, ?, ?)";
                 
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'isi',
            $data['annexure_type'],
            $data['term_text'],
            $data['sort_order']
        );
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error saving term: ' . mysqli_stmt_error($stmt));
    }

    // Get the ID of the saved term
    $termId = $isUpdate ? $data['id'] : mysqli_insert_id($conn);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $termId,
            'message' => $isUpdate ? 'Term updated successfully' : 'Term added successfully'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);