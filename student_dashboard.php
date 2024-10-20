<?php
// Start session
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

$student_id = $_SESSION['user_id']; 

// Fetch student details
$sql = "SELECT name FROM students WHERE id='$student_id'";
$result = $conn->query($sql);
$student_name = $result->fetch_assoc()['name'];

$selected_subject = isset($_GET['subject']) ? $_GET['subject'] : null;

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';

// Fetch assignments for the student, filtered by subject if selected
$sql_assignments = "
    SELECT a.assignment_id, a.subject, a.description, a.given_date, a.submission_date, 
           asu.status, asu.grade 
    FROM assignments a
    LEFT JOIN assignment_submissions asu ON a.assignment_id = asu.assignment_id AND asu.student_id = '$student_id'
    WHERE a.class_id IN (SELECT class_id FROM classes WHERE class_id = (SELECT class_id FROM students WHERE id = '$student_id'))
";

// Add subject filter if a subject is selected
if ($selected_subject) {
    $sql_assignments .= " AND a.subject = '" . $conn->real_escape_string($selected_subject) . "'";
}

// Add status filter if selected
if ($status_filter === 'submitted') {
    $sql_assignments .= " AND asu.status IS NOT NULL";
} elseif ($status_filter === 'unsubmitted') {
    $sql_assignments .= " AND asu.status IS NULL";
}

$result_assignments = $conn->query($sql_assignments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/student_dashboard.css">
</head>
<body>
    <div class="student-dashboard">
        <div class="sidebar">
            <div>
                <h1>Welcome,<br> <?php echo htmlspecialchars($student_name); ?></h1>
            </div>
            <hr><br>
            <h2>Your Subjects</h2>
            <ul>
                <li><a href="student_dashboard.php" class="subject-button">All Subjects</a></li>
                <?php
                // Fetch subjects for the student

                $sql_subjects = "SELECT DISTINCT a.subject FROM assignments a WHERE a.class_id = (SELECT class_id FROM students WHERE id = '$student_id')";
                $result_subjects = $conn->query($sql_subjects);

                // Display subjects as clickable buttons
                while ($row = $result_subjects->fetch_assoc()) {
                    echo "<li><a href='student_dashboard.php?subject=" . urlencode($row['subject']) . "' class='subject-button'>" . htmlspecialchars($row['subject']) . "</a></li>";
                }
                ?>
            </ul>

            <!-- Status Filter Section -->
            <hr><br>
            <h2>Filter by Status</h2>
            <ul>
                <li><a href="student_dashboard.php?status_filter=all<?php echo $selected_subject ? '&subject=' . urlencode($selected_subject) : ''; ?>" class="subject-button">All Assignments</a></li>
                <li><a href="student_dashboard.php?status_filter=submitted<?php echo $selected_subject ? '&subject=' . urlencode($selected_subject) : ''; ?>" class="subject-button">Submitted Assignments</a></li>
                <li><a href="student_dashboard.php?status_filter=unsubmitted<?php echo $selected_subject ? '&subject=' . urlencode($selected_subject) : ''; ?>" class="subject-button">Unsubmitted Assignments</a></li>
            </ul>
        </div>

        <div class="assignments">
            <h2>Your Assignments <?php echo $selected_subject ? "for " . htmlspecialchars($selected_subject) : ""; ?></h2>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Given Date</th>
                    <th>Submission Date</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Grade</th>
                </tr>
                <?php while ($assignment = $result_assignments->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['subject']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['given_date']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['status'] ?? 'Not Submitted'); ?></td>
                        <td>
                            <form action="submit_assignment.php" method="POST" enctype="multipart/form-data" id="form_<?php echo htmlspecialchars($assignment['assignment_id']); ?>">
                                <?php if (htmlspecialchars($assignment['status'])) { ?>
                                    <label class="upload-label" style="display: inline-block; background-color: #ccc; color: white; padding: 8px 12px; border-radius: 5px; cursor: not-allowed; text-align: center; font-weight: bold; font-size: 14px;">
                                        File Submitted
                                    </label>
                                    <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignment['assignment_id']); ?>">
                                    <button type="button" disabled style="display: none;">Submit</button>
                                <?php } else { ?>
                                    <label for="file_<?php echo htmlspecialchars($assignment['assignment_id']); ?>" class="upload-label" id="label_<?php echo htmlspecialchars($assignment['assignment_id']); ?>" style="display: inline-block; background-color: #007bff; color: white; padding: 8px 12px; border-radius: 5px; cursor: pointer; text-align: center; font-weight: bold; font-size: 14px;">Add File</label>
                                    <input type="file" id="file_<?php echo htmlspecialchars($assignment['assignment_id']); ?>" name="assignment_file" style="display:none;" onchange="showSubmit('<?php echo htmlspecialchars($assignment['assignment_id']); ?>')">
                                    <p id="file_name_<?php echo htmlspecialchars($assignment['assignment_id']); ?>" class="file-name"></p>
                                    <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignment['assignment_id']); ?>">
                                    <button type="submit" id="submit_<?php echo htmlspecialchars($assignment['assignment_id']); ?>" style="display:none;">Submit</button>
                                <?php } ?>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($assignment['grade'] ?? 'N/A'); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <script>
    function showSubmit(assignmentId) {
        const fileInput = document.getElementById('file_' + assignmentId);
        const label = document.getElementById('label_' + assignmentId);
        const fileNameDisplay = document.getElementById('file_name_' + assignmentId);
        const submitButton = document.getElementById('submit_' + assignmentId);

        const fileName = fileInput.files[0].name;
        fileNameDisplay.textContent = fileName;

        label.style.display = 'none';
        submitButton.style.display = 'inline-block';
    }
    </script>
</body>
</html>

<?php
$conn->close();
?>
