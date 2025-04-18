<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

function getCurrentFinancialYear() {
    $month = date('n');
    $year = date('y');
    if ($month < 4) { // If current month is before April
        return ($year - 1) . '-' . $year;
    }
    return $year . '-' . ($year + 1);
}

function getNextQuotationNumber($conn, $financialYear) {
    // First check if table exists
    $checkTable = "CREATE TABLE IF NOT EXISTS quotation_counters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        financial_year VARCHAR(10) NOT NULL UNIQUE,
        last_number INT NOT NULL DEFAULT 0
    )";
    
    if (!mysqli_query($conn, $checkTable)) {
        throw new Exception('Error creating counter table');
    }

    try {
        // Start transaction to ensure data consistency
        mysqli_begin_transaction($conn);

        // Check if financial year exists
        $check = "SELECT last_number FROM quotation_counters WHERE financial_year = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, 's', $financialYear);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 0) {
            // If financial year doesn't exist, insert it with initial number 0
            $insert = "INSERT INTO quotation_counters (financial_year, last_number) VALUES (?, 0)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, 's', $financialYear);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error initializing counter');
            }
            mysqli_commit($conn);
            return "01"; // First quotation of the financial year
        }

        $row = mysqli_fetch_assoc($result);
        // Return next number (current + 1) without incrementing
        $nextNumber = $row['last_number'] + 1;
        mysqli_commit($conn);
        return str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

try {
    $financialYear = getCurrentFinancialYear();
    $nextNumber = getNextQuotationNumber($conn, $financialYear);
    $prefix = "TCE";
    $userInitials = "JB"; // This should come from user settings later

    $refNo = "$prefix/$financialYear/$userInitials/$nextNumber";
    
    echo json_encode([
        'status' => 'success',
        'refNo' => $refNo,
        'financialYear' => $financialYear,
        'quotationNumber' => $nextNumber
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);