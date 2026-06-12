<?php
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') { header("Location: login.php"); exit; }

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$query = "SELECT c.course_name, c.credits, e.grade FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades_res = $stmt->get_result();

function gradeToPoints($grade) {
    switch(strtoupper(trim($grade))) {
        case 'A': return 4.0;
        case 'B': return 3.0;
        case 'C': return 2.0;
        case 'D': return 1.0;
        case 'F': return 0.0;
        default: return null;
    }
}

$total_points = 0; $total_credits = 0; $rows = [];
while($row = $grades_res->fetch_assoc()) {
    $rows[] = $row;
    $points = gradeToPoints($row['grade']);
    if($points !== null) {
        $total_points += ($points * $row['credits']);
        $total_credits += $row['credits'];
    }
}
$gpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : "N/A";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.95)), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1920') no-repeat center center/cover;
            color: #ffffff;
            overflow: hidden;
        }

        /* Full Screen Outer Wrapper */
        .dashboard-wrapper {
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Glassmorphic Dashboard Container */
        .container {
            max-width: 850px;
            width: 100%;
            max-height: 90vh;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            overflow-y: auto;
        }

        /* Custom Dynamic Scrollbar */
        .container::-webkit-scrollbar {
            width: 8px;
        }
        .container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        .container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        .container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        /* Top Header Area */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .student-major {
            color: #cbd5e1;
            font-size: 1rem;
            margin-bottom: 25px;
            display: block;
        }

        h2 {
            font-size: 1.2rem;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modern Premium GPA Display Bar */
        .gpa-card {
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(16, 185, 129, 0.4);
            background: rgba(16, 185, 129, 0.15);
            color: #a7f3d0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
        }

        .gpa-label {
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .gpa-score {
            font-size: 2.4rem;
            font-weight: 900;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Transcript Table Design */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 14px 18px;
        }

        th {
            background-color: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15);
        }

        td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background-color: rgba(255, 255, 255, 0.02);
            font-size: 0.95rem;
            color: #f1f5f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.06);
        }

        /* Premium Badge for Grade Display */
        .badge-grade {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.9rem;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* Action Logout Button */
        .logout-btn {
            background-color: #ef4444;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .logout-btn:hover {
            background-color: #dc2626;
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .logout-btn { align-self: flex-end; }
            .gpa-card { flex-direction: column; text-align: center; gap: 5px; }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <div class="container">
        
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($profile['first_name'] . " " . $profile['last_name']); ?></h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <span class="student-major"><strong>Enrolled Major Branch:</strong> <?php echo htmlspecialchars($profile['major']); ?></span>
        
        <div class="gpa-card">
            <span class="gpa-label">Calculated Cumulative GPA</span>
            <span class="gpa-score"><?php echo $gpa; ?></span>
        </div>

        <h2>Your Complete Academic Transcript Record</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Course Nomenclature</th>
                        <th>Weight Credits</th>
                        <th>Final Grade Mark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['credits']); ?></td>
                        <td><span class="badge-grade"><?php echo htmlspecialchars($row['grade']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    </div>

</div>

</body>
</html>