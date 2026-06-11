<?php
session_start();
require 'db.php';

$error = "";

// ==========================================================================
// FORCE RESET ADMIN EMAIL & PASSWORD (KUGIRA NGO BIHINDURE AMAKURU MURI DATABASE)
// ==========================================================================
$admin_email = 'admin@gmail.com';
$forced_hash = password_hash('123', PASSWORD_DEFAULT);

// Iri tegeko rirahita rikosora email na password bya admin muri database ako kanya ufunguye uru rupapuro
$conn->query("UPDATE users SET email = '$admin_email', password = '$forced_hash' WHERE role = 'admin'");
// ==========================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $selected_role = $_POST['role'];

    // 1. Fetch user by email only
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 2. Validate password
        if (password_verify($password, $user['password'])) {
            
            // 3. Confirm chosen form role matches database reality
            if ($user['role'] !== $selected_role) {
                $error = "Role mismatch! This account is registered as a " . ucfirst($user['role']) . ".";
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') header("Location: admin_dashboard.php");
                elseif ($user['role'] == 'instructor') header("Location: instructor_dashboard.php");
                elseif ($user['role'] == 'student') header("Location: student_dashboard.php");
                exit;
            }
        } else { 
            $error = "Invalid password."; 
        }
    } else { 
        $error = "No account found with that email address."; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal Login</title>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            color: #1e293b;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }
        .login-card h2 {
            font-size: 1.8rem;
            text-align: center;
            margin: 0 0 8px 0;
            color: #0f172a;
        }
        .login-subtitle {
            text-align: center;
            font-size: 0.9rem;
            color: #64748b;
            margin: 0 0 30px 0;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-sizing: border-box;
            background-color: #f8fafc;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: #fff;
        }
        button {
            background-color: #3b82f6;
            color: #fff;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
            width: 100%;
        }
        button:hover { background-color: #2563eb; }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #fca5a5;
            background-color: #fef2f2;
            color: #b91c1c;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>University Portal</h2>
    <p class="login-subtitle">Enter credentials to log in.</p>
    
    <?php if($error) echo "<div class='alert'>$error</div>"; ?>
    
    <form action="" method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="admin@gmail.com" required>
        
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
        
        <label>Login As:</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="admin" selected>Admin</option>
        </select>
        
        <button type="submit">Sign In</button>
    </form>
</div>

</body>
</html>