<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // replace with your database username
$password = ""; // replace with your database password
$dbname = "assignment_management"; // replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming the student ID is stored in the session
$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['assignment_file'])) {
    $assignment_id = $_POST['assignment_id'];
    $submission_date = date('Y-m-d'); // Current date
    
    // Get the file content as BLOB
    $file = $_FILES['assignment_file']['tmp_name'];
    $file_content = file_get_contents($file);
    $file_name = $_FILES['assignment_file']['name']; // Store the file name if you want to reference it later

    // Check if the submission already exists
    $sql_check = "SELECT * FROM assignment_submissions WHERE assignment_id='$assignment_id' AND student_id='$student_id'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Update submission if it already exists
        $sql_update = "UPDATE assignment_submissions 
                       SET submission_date='$submission_date', submitted_file=?, status='Submitted' 
                       WHERE assignment_id='$assignment_id' AND student_id='$student_id'";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param('b', $file_content); // 'b' indicates blob
        $stmt->send_long_data(0, $file_content);
        $stmt->execute();
    } else {
        // Insert new submission record
        $sql_insert = "INSERT INTO assignment_submissions (assignment_id, student_id, submission_date, submitted_file, status) 
                       VALUES (?, ?, ?, ?, 'Submitted')";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param('ssss', $assignment_id, $student_id, $submission_date, $file_content);
        $stmt->send_long_data(3, $file_content); // Send file content as blob
        $stmt->execute();
    }
}

// Redirect back to the dashboard
header("Location: student_dashboard.php");
exit();

$conn->close();
?>
