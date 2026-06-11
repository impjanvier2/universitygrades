<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifying password (works with password_hash)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == 'admin') header("Location: admin_dashboard.php");
            elseif ($role == 'instructor') header("Location: instructor_dashboard.php");
            elseif ($role == 'student') header("Location: student_dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with those credentials/role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <title>University Login</title>
</head>
<body>
<div class="container" style="max-width: 450px; margin-top: 50px;">
    <h2>Portal Login</h2>
    <?php if($error) echo "<div class='alert' style='background:#fde8e8; color:#e74c3c;'>$error</div>"; ?>
    <form action="" method="POST">
        <label>Email</label>
        <input type="email" name="email" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <label>Login As</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Admin</option>
        </select>
        
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>