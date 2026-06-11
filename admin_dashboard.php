<?php
session_start();
require 'db.php';
if ($_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

$msg = "";

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'add_user') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        // 1. Insert into Users Table
        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $role);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            
            // 2. Insert into respective profile tables
            if ($role == 'student') {
                $stmt2 = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, major) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $new_id, $_POST['first_name'], $_POST['last_name'], $_POST['major']);
                $stmt2->execute();
            } elseif ($role == 'instructor') {
                $stmt2 = $conn->prepare("INSERT INTO instructors (instructor_id, name, department) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $new_id, $_POST['name'], $_POST['department']);
                $stmt2->execute();
            }
            $msg = "User successfully created!";
        } else { $msg = "Error creating user account."; }
    }

    if ($action == 'add_course') {
        $c_name = $_POST['course_name'];
        $credits = $_POST['credits'];
        $stmt = $conn->prepare("INSERT INTO courses (course_name, credits) VALUES (?, ?)");
        $stmt->bind_param("si", $c_name, $credits);
        $stmt->execute();
        $msg = "Course added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Admin Dashboard</title>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Admin Dashboard</h1>
    <?php if($msg) echo "<div class='alert'>$msg</div>"; ?>

    <h2>Register Student or Instructor Account</h2>
    <form action="" method="POST">
        <input type="hidden" name="action" value="add_user">
        <label>Role</label>
        <select name="role" id="roleSelect" onchange="toggleFields()" required>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
        </select>

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Account Password</label>
        <input type="password" name="password" required>

        <div id="studentFields">
            <label>First Name</label>
            <input type="text" name="first_name">
            <label>Last Name</label>
            <input type="text" name="last_name">
            <label>Major</label>
            <input type="text" name="major">
        </div>

        <div id="instructorFields" style="display:none;">
            <label>Full Name</label>
            <input type="text" name="name">
            <label>Department</label>
            <input type="text" name="department">
        </div>

        <button type="submit">Register User</button>
    </form>

    <h2>Add New Course</h2>
    <form action="" method="POST">
        <input type="hidden" name="action" value="add_course">
        <label>Course Name</label>
        <input type="text" name="course_name" required>
        <label>Credits</label>
        <input type="number" name="credits" required>
        <button type="submit">Create Course</button>
    </form>
</div>

<script>
function toggleFields() {
    var role = document.getElementById("roleSelect").value;
    document.getElementById("studentFields").style.display = (role === "student") ? "block" : "none";
    document.getElementById("instructorFields").style.display = (role === "instructor") ? "block" : "none";
}
</script>
</body>
</html>