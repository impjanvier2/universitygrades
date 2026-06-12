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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        /* Full Screen Background Layout matching index.php */
        .full-screen-wrapper {
            height: 100vh;
            width: 100vw;
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.9)), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1920') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Glassmorphic Login Card */
        .login-card {
            max-width: 420px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            color: #ffffff;
            animation: fadeIn 0.8s ease-out;
        }

        .login-card h2 {
            font-size: 2.1rem;
            text-align: center;
            margin-bottom: 8px;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            text-align: center;
            font-size: 0.95rem;
            color: #cbd5e1;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            margin-top: 6px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            box-sizing: border-box;
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        /* Style for Select Dropdown Options */
        select option {
            background-color: #1e293b;
            color: #ffffff;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
        }

        /* Placeholder text color */
        input::placeholder {
            color: #94a3b8;
        }

        button {
            background-color: #2563eb;
            color: #fff;
            padding: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 30px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        button:hover { 
            background-color: #3b82f6; 
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        /* Error Alert Box */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid rgba(239, 68, 68, 0.4);
            background-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            text-align: center;
            font-weight: 500;
        }

        /* Back link to Home */
        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-home:hover {
            color: #ffffff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Responsive UI */
        @media (max-width: 480px) {
            .login-card { padding: 30px 20px; }
            .login-card h2 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="full-screen-wrapper">

    <div class="login-card">
        <h2>Sign In</h2>
        <p class="login-subtitle">Catholic University of Rwanda</p>
        
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

        <a href="index.php" class="back-home">← Back to Welcome Page</a>
    </div>

</div>

</body>
</html>