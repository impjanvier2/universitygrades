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
        /* Modern Student Dashboard CSS */
        :root {
            --primary-color: #1e293b;    /* Slate 800 */
            --secondary-color: #475569;  /* Slate 600 */
            --success-bg: #f0fdf4;       /* Emerald 50 */
            --success-border: #bbf7d0;   /* Emerald 200 */
            --success-text: #166534;     /* Emerald 800 */
            --danger-color: #ef4444;     /* Red 500 */
            --danger-hover: #dc2626;     /* Red 600 */
            --bg-color: #f8fafc;         /* Slate 50 */
            --card-bg: #ffffff;
            --border-color: #e2e8f0;     /* Slate 200 */
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 40px 20px;
            color: var(--primary-color);
            line-height: 1.5;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 
                        0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

        /* Clearfix layout for floats */
        .container::after {
            content: "";
            clear: both;
            display: table;
        }

        h1, h2 {
            color: var(--primary-color);
            margin-top: 0;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 6px;
            display: inline-block;
        }

        .student-major {
            color: var(--secondary-color);
            font-size: 1.05rem;
            margin-top: 0;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 1.4rem;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border-color);
        }

        /* Premium GPA Banner Display */
        .gpa-card {
            padding: 20px 24px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid var(--success-border);
            background-color: var(--success-bg);
            color: var(--success-text);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .gpa-label {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .gpa-score {
            font-size: 2.2rem;
            font-weight: 800;
        }

        /* Transcript Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        th, td {
            padding: 14px 18px;
            text-align: left;
        }

        th {
            background-color: #f1f5f9;
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            border-bottom: 1px solid var(--border-color);
            background-color: #ffffff;
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Grade Box Styling */
        .badge-grade {
            background-color: #f1f5f9;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-block;
        }

        /* Action Buttons */
        .logout-btn {
            float: right;
            background-color: var(--danger-color);
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-top: 5px;
        }

        .logout-btn:hover {
            background-color: var(--danger-hover);
        }

        .logout-btn:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Welcome, <?php echo htmlspecialchars($profile['first_name'] . " " . $profile['last_name']); ?></h1>
    <p class="student-major"><strong>Enrolled Major Branch:</strong> <?php echo htmlspecialchars($profile['major']); ?></p>
    
    <div class="gpa-card">
        <span class="gpa-label">Calculated Cumulative GPA</span>
        <span class="gpa-score"><?php echo $gpa; ?></span>
    </div>

    <h2>Your Complete Academic Transcript Record</h2>
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

</body>
</html>