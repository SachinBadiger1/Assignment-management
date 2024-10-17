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

// Fetch submitted assignments including grade
$sql = "SELECT assignment_submissions.assignment_id, assignment_submissions.student_id, assignment_submissions.submission_date, 
        assignment_submissions.status, assignment_submissions.grade, students.name AS student_name, 
        assignments.assignment_id AS assignment_title, assignment_submissions.submission_id
        FROM assignment_submissions
        JOIN students ON assignment_submissions.student_id = students.id
        JOIN assignments ON assignment_submissions.assignment_id = assignments.assignment_id
        ORDER BY assignment_submissions.submission_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/view_submissions.css">
    <title>View Submissions</title>
</head>
<body>
    <h1>Submitted Assignments</h1>
    
    <table>
        <thead>
            <tr>
                <th>Assignment Id</th>
                <th>Student Name</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>View/Download File</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="download_file.php?submission_id=<?php echo $row['submission_id']; ?>">Download File</a>
                        </td>
                        <td>
                            <?php if (empty($row['grade'])) { ?>
                                <form action="grade_submission.php" method="POST">
                                    <input type="hidden" name="assignment_id" value="<?php echo $row['assignment_id']; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                    <input type="text" name="grade" placeholder="Enter Grade">
                                    <button type="submit">Submit Grade</button>
                                </form>
                            <?php } else { ?>
                                <?php echo htmlspecialchars($row['grade']); ?>
                            <?php } ?>
                        </td>
                    </tr>
            <?php } } else { ?>
                <tr>
                    <td colspan="6">No submissions found</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    
</body>
</html>

<?php
$conn->close();
?>
