<?php
require(__DIR__ . '/../fpdf/fpdf.php');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studeninfo";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['roll_no']) || empty(trim($_GET['roll_no']))) {
    die("Roll number is required.");
}

$roll_no = htmlspecialchars(trim($_GET['roll_no']), ENT_QUOTES, 'UTF-8');

// Update SQL query to include the created_at column
$query = "SELECT roll_no, level1_comments, level2_comments, level3_comments, level4_comments,  verification_status,  action	,approval ,timestamp 
          FROM students 
          INNER JOIN level ON students.id = remarks.student_id 
          INNER JOIN users ON remarks.user_id = users.id 
          WHERE roll_no = ? 
          ORDER BY remarks.level ASC";
     


     


if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("s", $roll_no);
    $stmt->execute();
    $stmt->bind_result($name, $roll_no, $status, $remark, $level, $username, $created_at);
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->fetch(); // Fetch the initial row

        // Debug: Check if variables are populated correctly
        error_log("Name: $name, Roll No: $roll_no, Status: $status");

        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Student Strike Off Report', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Name: $name", 0, 1);
        $pdf->Cell(0, 10, "Roll No: $roll_no", 0, 1);
        $pdf->Cell(0, 10, "Status: $status", 0, 1);

        $pdf->Ln(10);
        $pdf->Cell(0, 10, "Remarks:", 0, 1);

        // Add remarks to the PDF with timing
        do {
            // Format the created_at timestamp
            $formatted_date = date('Y-m-d H:i:s', strtotime($created_at));
            $pdf->Cell(0, 10, "Level $level - $username: $remark (Added on: $formatted_date)", 0, 1);
        } while ($stmt->fetch());

        // Add timestamp for PDF generation
        $pdf->Ln(10);
        $pdf->Cell(0, 10, "Generated on: " . date('Y-m-d H:i:s'), 0, 1);

        $stmt->close();

        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Student_Strike_Off_Report.pdf"');
        $pdf->Output('D', 'Student_Strike_Off_Report.pdf');
        exit();

    } else {
        echo "No remarks found for the given student.";
    }
} else {
    echo "Failed to prepare the query.";
}

$conn->close();
?>