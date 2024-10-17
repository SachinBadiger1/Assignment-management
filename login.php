<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $servername = "localhost";
    $username = "root"; // Change if your database username is different
    $password = ""; // Change if your database password is different
    $dbname = "assignment_management"; // Change to your actual database name

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Verify user based on role
    if ($role == 'student') {
        $sql = "SELECT * FROM students WHERE id='$student_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo $user['password'];
            // Verify password using password_verify (assuming the stored password is hashed)
            if ($password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'student';
                header("Location: student_dashboard.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with the given ID.";
        }
    } else if ($role == 'teacher') {
        $sql = "SELECT * FROM teachers WHERE id='$student_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify password using password_verify (assuming the stored password is hashed)
            if (($password == $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'teacher';
                header("Location: teacher_dashboard.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with the given ID.";
        }
    }

    $conn->close();
} else {
    // If the request method is not POST, show an error
    echo "Invalid request method.";
}
