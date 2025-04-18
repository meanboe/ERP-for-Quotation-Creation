<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

try {
    // Get total quotation count
    $query = "SELECT COUNT(*) as total FROM quotations";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Error executing query: ' . mysqli_error($conn));
    }
    
    $row = mysqli_fetch_assoc($result);
    $total = $row['total'];
    
    // Get last month's count for comparison
    $lastMonthQuery = "SELECT COUNT(*) as last_month FROM quotations 
                      WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                      AND created_at < CURRENT_DATE";
    $lastMonthResult = mysqli_query($conn, $lastMonthQuery);
    
    if (!$lastMonthResult) {
        throw new Exception('Error executing query: ' . mysqli_error($conn));
    }
    
    $lastMonthRow = mysqli_fetch_assoc($lastMonthResult);
    $lastMonth = $lastMonthRow['last_month'];
    
    // Get previous month's count
    $previousMonthQuery = "SELECT COUNT(*) as prev_month FROM quotations 
                          WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)
                          AND created_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
    $previousMonthResult = mysqli_query($conn, $previousMonthQuery);
    
    if (!$previousMonthResult) {
        throw new Exception('Error executing query: ' . mysqli_error($conn));
    }
    
    $previousMonthRow = mysqli_fetch_assoc($previousMonthResult);
    $previousMonth = $previousMonthRow['prev_month'];
    
    // Calculate percentage change
    if ($previousMonth == 0) {
        // If there were no quotations in previous month
        $percentageChange = $lastMonth > 0 ? 100 : 0; // 100% increase if we have quotations this month, 0% if none
    } else {
        // Normal percentage change calculation
        $percentageChange = round((($lastMonth - $previousMonth) / $previousMonth) * 100, 1);
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $total,
            'lastMonth' => $lastMonth,
            'previousMonth' => $previousMonth,
            'percentageChange' => $percentageChange
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);