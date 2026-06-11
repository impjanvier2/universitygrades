<?php
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

$msg = ""; $class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'add_user') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $role);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            if ($role == 'student') {
                $stmt2 = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, major) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $new_id, $_POST['first_name'], $_POST['last_name'], $_POST['major']);
                $stmt2->execute();
            } elseif ($role == 'instructor') {
                $stmt2 = $conn->prepare("INSERT INTO instructors (instructor_id, name, department) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $new_id, $_POST['name'], $_POST['department']);
                $stmt2->execute();
            }
            $msg = "New user profile successfully registered!"; $class = "success";
        } else { $msg = "Email already exists or transaction failed."; $class = "alert"; }
    }

    if ($action == 'add_course') {
        $c_name = $_POST['course_name'];
        $credits = $_POST['credits'];
        $stmt = $conn->prepare("INSERT INTO courses (course_name, credits) VALUES (?, ?)");
        $stmt->bind_param("si", $c_name, $credits);
        $stmt->execute();
        $msg = "Course added successfully!"; $class = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Modern Dashboard Stylesheet */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #10b981;
            --success-hover: #059669;    
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 40px 20px;
            color: var(--text-main);
            line-height: 1.5;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
            background: var(--card-background);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 
                        0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

        /* Clearfix for floating logout button */
        .container::after {
            content: "";
            clear: both;
            display: table;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-top: 0;
            font-weight: 700;
        }

        h1 {
            font-size: 2rem;
            letter-spacing: -0.025em;
            margin-bottom: 30px;
            display: inline-block;
        }

        h2 {
            font-size: 1.4rem;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border-color);
        }

        /* Forms & Fields */
        form {
            background-color: #fafafa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-top: 16px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        label:first-of-type {
            margin-top: 0;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Action Buttons */
        button {
            display: block;
            background-color: var(--success-color);
            color: #ffffff;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
            width: 100%;
            box-sizing: border-box;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        button:hover {
            background-color: var(--success-hover);
        }

        button:active {
            transform: scale(0.99);
        }

        .logout-btn {
            float: right;
            background-color: var(--danger-color);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
            margin-top: 5px;
        }

        .logout-btn:hover {
            background-color: var(--danger-hover);
        }

        /* Feedback Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            border: 1px solid #fca5a5;
            background-color: #fef2f2;
            color: #b91c1c;
        }

        .alert.success {
            border-color: #a7f3d0;
            background-color: #f0fdf4;
            color: #15803d;
        }

        /* Form section groupings */
        #studentFields, #instructorFields {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Admin Central Panel</h1>
    
    <?php if($msg) echo "<div class='alert $class'>$msg</div>"; ?>

    <h2>Register New User Account</h2>
    <form action="" method="POST">
        <input type="hidden" name="action" value="add_user">
        
        <label>Account Role Type</label>
        <select name="role" id="roleSelect" onchange="toggleFields()" required>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
        </select>

        <label>Email Address</label>
        <input type="email" name="email" placeholder="user@university.com" required>

        <label>Secure Password</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <div id="studentFields">
            <label>First Name</label>
            <input type="text" name="first_name" placeholder="John">
            
            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Doe">
            
            <label>Academic Major</label>
            <input type="text" name="major" placeholder="Software Engineering">
        </div>

        <div id="instructorFields" style="display:none;">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Dr. John Smith">
            
            <label>Department</label>
            <input type="text" name="department" placeholder="Computer Science">
        </div>
        
        <button type="submit">Register User Account</button>
    </form>

    <h2>Create New Course Module</h2>
    <form action="" method="POST">
        <input type="hidden" name="action" value="add_course">
        
        <label>Course Name</label>
        <input type="text" name="course_name" placeholder="Introduction to PHP" required>
        
        <label>Credits Value</label>
        <input type="number" name="credits" placeholder="4" required>
        
        <button type="submit">Publish Course</button>
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