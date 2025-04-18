<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    // Get ref_no if provided
    $refNo = isset($_GET['ref_no']) ? $_GET['ref_no'] : null;
    
    // Base query
    $query = "SELECT *, DATE_FORMAT(quote_date, '%Y-%m-%d') as formatted_date 
              FROM quotations";
    
    // Add where clause if ref_no provided
    if ($refNo) {
        $query .= " WHERE ref_no = ?";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($refNo) {
        mysqli_stmt_bind_param($stmt, 's', $refNo);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing query: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    $quotations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Convert NULL values to empty strings or appropriate defaults
        $quotation = [
            'ref_no' => $row['ref_no'],
            'customer_name' => $row['customer_name'] ?? '',
            'created_by' => $row['created_by'] ?? '',
            'quote_date' => $row['formatted_date'],
            'revision' => $row['revision'] ?? '00',
            'address' => $row['address'] ?? '',
            'subject' => $row['subject'] ?? '',
            'annexure1_subtotal' => $row['annexure1_subtotal'] ?? '0.00',
            'annexure1_gst' => $row['annexure1_gst'] ?? '0.00',
            'annexure1_roundoff' => $row['annexure1_roundoff'] ?? '0.00',
            'annexure1_total' => $row['annexure1_total'] ?? '0.00',
            'annexure1_terms' => $row['annexure1_terms'] ?? '100% Advance against PO',
            'annexure2_subtotal' => $row['annexure2_subtotal'] ?? '0.00',
            'annexure2_gst' => $row['annexure2_gst'] ?? '0.00',
            'annexure2_roundoff' => $row['annexure2_roundoff'] ?? '0.00',
            'annexure2_total' => $row['annexure2_total'] ?? '0.00',
            'annexure2_terms' => $row['annexure2_terms'] ?? '70% Advance against PO, 20% Against Delivery & 10% After Installation',
            'final_total' => $row['final_total'] ?? '0.00',
            'created_at' => date('d M Y h:i A', strtotime($row['created_at']))
        ];
        
        $quotations[] = $quotation;
    }
    
    echo json_encode(['status' => 'success', 'data' => $quotations]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);