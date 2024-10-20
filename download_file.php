<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "assignment_management"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['submission_id'])) {
    $submission_id = $_GET['submission_id'];

    // Fetch the submitted file (BLOB)
    $sql = "SELECT submitted_file FROM assignment_submissions WHERE submission_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $submission_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($file);
    $stmt->fetch();

    // Check if the file exists in the database
    if ($stmt->num_rows > 0) {
        // Set the name and force it as a PDF file
        $file_name = 'submission_' . $submission_id . '.pdf'; // Using .pdf as the extension

        // Force download the file as a PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . strlen($file));

        // Output the file content
        echo $file;
        exit;
    } else {
        echo "No file found.";
    }
}

$conn->close();
?>
