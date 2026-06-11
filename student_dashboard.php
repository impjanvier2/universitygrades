<?php
session_start();
require 'db.php';
if ($_SESSION['role'] !== 'student') { header("Location: login.php"); exit; }

$student_id = $_SESSION['user_id'];

// Get Student Profile
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get Grades and Credits
$query = "SELECT c.course_name, c.credits, e.grade 
          FROM enrollments e 
          JOIN courses c ON e.course_id = c.course_id 
          WHERE e.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades_res = $stmt->get_result();

// GPA Calculation Function
function gradeToPoints($grade) {
    switch(strtoupper(trim($grade))) {
        case 'A': return 4.0;
        case 'B': return 3.0;
        case 'C': return 2.0;
        case 'D': return 1.0;
        case 'F': return 0.0;
        default: return null; // Ignore non-standard configurations or N/A
    }
}

$total_points = 0;
$total_credits = 0;
$rows = [];

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
    <link rel="stylesheet" href="style.css">
    <title>Student Dashboard</title>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Welcome, <?php echo $profile['first_name'] . " " . $profile['last_name']; ?></h1>
    <p><strong>Major:</strong> <?php echo $profile['major']; ?></p>
    
    <div class="alert" style="font-size: 1.2em; background-color: #e8f5e9; color: #2e7d32;">
        <strong>Calculated Cumulative GPA:</strong> <?php echo $gpa; ?>
    </div>

    <h2>Your Academic Record</h2>
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($rows as $row): ?>
            <tr>
                <td><?php echo $row['course_name']; ?></td>
                <td><?php echo $row['credits']; ?></td>
                <td><strong><?php echo $row['grade']; ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>