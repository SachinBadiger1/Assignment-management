<?php
// Start session
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

// Check if grade and necessary data were submitted
if (isset($_POST['assignment_id'], $_POST['student_id'], $_POST['grade'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_POST['student_id'];
    $grade = $_POST['grade'];

    // Update the grade in the assignment_submissions table
    $sql = "UPDATE assignment_submissions SET grade = ? WHERE assignment_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $grade, $assignment_id, $student_id);

    if ($stmt->execute()) {
        echo "Grade updated successfully!";
    } else {
        echo "Error updating grade: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid input. Please try again.";
}

// Close the database connection
$conn->close();
?>
