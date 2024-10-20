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

$teacher_id = $_SESSION['user_id']; 

// Fetch course_id for the logged-in teacher
$sql_course = "SELECT course_id FROM teachers WHERE id = ?";
$stmt_course = $conn->prepare($sql_course);
$stmt_course->bind_param('s', $teacher_id);
$stmt_course->execute();
$stmt_course->bind_result($course_id);
$stmt_course->fetch();
$stmt_course->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $subject_name = $_POST['subject_name'];
    $description = $_POST['description'];
    $submission_date = $_POST['submission_date'];
    $class_id = $_POST['class_id']; 

    // Get today's date for given_date
    $given_date = date('Y-m-d');

    // Validate that submission_date is not earlier than today's date
    if ($submission_date < $given_date) {
        echo "Submission date cannot be earlier than today's date.";
    } else {
        // Insert the assignment into the database
        $sql_insert = "INSERT INTO assignments ( course_id, class_id, subject, description, given_date, submission_date) 
                       VALUES (  ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param('ssssss', $course_id, $class_id, $subject_name, $description, $given_date, $submission_date);

        if ($stmt->execute()) {
            // Redirect back to the teacher dashboard on success
            header("Location: teacher_dashboard.php?message=Assignment Created Successfully");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link rel="stylesheet" href="css/create_assignment.css">
</head>
<body>
    <div class="container">
        <h2>Create Assignment</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="class_id">Class ID:</label>
                <input type="radio" id="class_id_1" name="class_id" value="CL101" required> CL101<br>
                <input type="radio" id="class_id_2" name="class_id" value="CL102"> CL102<br>
                <input type="radio" id="class_id_3" name="class_id" value="CL103"> CL103<br>
                <!-- Add more radio buttons as needed -->
            </div>
            <div class="form-group">
                <label for="subject_name">Subject Name:</label>
                <input type="text" id="subject_name" name="subject_name" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="submission_date">Submission Date:</label>
                <input type="date" id="submission_date" name="submission_date" required>
            </div>
            <button type="submit">Create Assignment</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
