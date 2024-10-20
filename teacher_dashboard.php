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

$teacher_id = $_SESSION['user_id']; // Replace with your session variable for teacher ID

// Fetch teacher details if needed
$sql = "SELECT name FROM teachers WHERE id='$teacher_id'";
$result = $conn->query($sql);
$teacher_name = $result->fetch_assoc()['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="css/teacher_dashboard.css">

</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($teacher_name); ?></h1>
        <a href="create_assignment.php" class="button">Create New Assignment</a>
        <a href="view_submissions.php" class="button">View Submitted Assignments</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
