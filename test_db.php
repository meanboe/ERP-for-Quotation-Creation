<?php
require_once 'conn/conn.php';

// Create quotations table
$createQuotationsTable = "CREATE TABLE IF NOT EXISTS quotations (
    ref_no VARCHAR(50) PRIMARY KEY,
    customer_name VARCHAR(255),
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revision VARCHAR(10),
    address TEXT,
    subject TEXT,
    quote_date DATE,
    annexure1_subtotal DECIMAL(10,2),
    annexure1_gst DECIMAL(10,2),
    annexure1_roundoff DECIMAL(10,2),
    annexure1_total DECIMAL(10,2),
    annexure1_terms TEXT,
    annexure2_subtotal DECIMAL(10,2),
    annexure2_gst DECIMAL(10,2),
    annexure2_roundoff DECIMAL(10,2),
    annexure2_total DECIMAL(10,2),
    annexure2_terms TEXT,
    final_total DECIMAL(10,2)
)";

// Create quotation_products table
$createProductsTable = "CREATE TABLE IF NOT EXISTS quotation_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_ref VARCHAR(50),
    description TEXT,
    unit VARCHAR(20),
    quantity DECIMAL(10,2),
    rate DECIMAL(10,2),
    total DECIMAL(10,2),
    annexure_no INT,
    FOREIGN KEY (quotation_ref) REFERENCES quotations(ref_no) ON DELETE CASCADE
)";

// Create terms_conditions table
$createTermsTable = "CREATE TABLE IF NOT EXISTS terms_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annexure_type TINYINT NOT NULL, -- 1 for Annexure 1, 2 for Annexure 2
    term_text TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createTermsTable)) {
    echo "Error creating terms_conditions table: " . mysqli_error($conn) . "<br>";
} else {
    echo "Terms & conditions table created successfully<br>";
}

// Insert sample data
$insertSampleData = "INSERT INTO quotations (
    ref_no, customer_name, created_by, revision, address, subject, quote_date,
    annexure1_subtotal, annexure1_gst, annexure1_roundoff, annexure1_total, annexure1_terms,
    annexure2_subtotal, annexure2_gst, annexure2_roundoff, annexure2_total, annexure2_terms,
    final_total
) VALUES (
    'TCE/23-24/JB/01', 'Test Customer', 'John Doe', '00',
    '123 Test Street', 'Test Quotation', '2024-04-15',
    1000.00, 280.00, 0.00, 1280.00, '100% Advance against PO',
    500.00, 90.00, 0.00, 590.00, '70% Advance against PO',
    1870.00
)";

try {
    if (mysqli_query($conn, $createQuotationsTable)) {
        echo "Quotations table created successfully<br>";
    } else {
        throw new Exception("Error creating quotations table: " . mysqli_error($conn));
    }

    if (mysqli_query($conn, $createProductsTable)) {
        echo "Products table created successfully<br>";
    } else {
        throw new Exception("Error creating products table: " . mysqli_error($conn));
    }

    // Check if sample data already exists
    $checkQuery = "SELECT COUNT(*) as count FROM quotations WHERE ref_no = 'TCE/23-24/JB/01'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        if (mysqli_query($conn, $insertSampleData)) {
            echo "Sample data inserted successfully<br>";
        } else {
            throw new Exception("Error inserting sample data: " . mysqli_error($conn));
        }
    } else {
        echo "Sample data already exists<br>";
    }

    // Insert sample terms data if not exists
    $checkTerms = "SELECT COUNT(*) as count FROM terms_conditions";
    $result = mysqli_query($conn, $checkTerms);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] == 0) {
        // Sample terms for Annexure 1
        $sampleTerms1 = [
            "100% Advance against PO",
            "Delivery within 4-5 weeks from the date of PO & advance received",
            "Freight charges extra as actual",
            "Any modification in civil/electrical work will be charged extra"
        ];

        // Sample terms for Annexure 2
        $sampleTerms2 = [
            "70% Advance against PO",
            "20% Against Delivery",
            "10% After Installation",
            "Installation charges included in price",
            "Warranty as per company policy"
        ];

        $insertTerm = "INSERT INTO terms_conditions (annexure_type, term_text, sort_order) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertTerm);

        foreach ($sampleTerms1 as $index => $term) {
            mysqli_stmt_bind_param($stmt, 'isi', $annexure1, $term, $index);
            $annexure1 = 1;
            mysqli_stmt_execute($stmt);
        }

        foreach ($sampleTerms2 as $index => $term) {
            mysqli_stmt_bind_param($stmt, 'isi', $annexure2, $term, $index);
            $annexure2 = 2;
            mysqli_stmt_execute($stmt);
        }

        echo "Sample terms inserted successfully<br>";
    }

    echo "Database setup completed successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

mysqli_close($conn);
?>