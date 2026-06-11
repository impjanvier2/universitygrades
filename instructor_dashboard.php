<?php
session_start();
require 'db.php';
if ($_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];
$msg = "";

// Handle Grade Submission or Modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_grade'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $grade = $_POST['grade'];

    $stmt = $conn->prepare("UPDATE enrollments SET grade = ? WHERE enrollment_id = ? AND instructor_id = ?");
    $stmt->bind_param("sii", $grade, $enrollment_id, $instructor_id);
    if($stmt->execute()) { $msg = "Grade updated successfully!"; }
}

// Fetch all student enrollments under this specific instructor
$query = "SELECT e.enrollment_id, s.first_name, s.last_name, c.course_name, e.grade 
          FROM enrollments e
          JOIN students s ON e.student_id = s.student_id
          JOIN courses c ON e.course_id = c.course_id
          WHERE e.instructor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$roster = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Instructor Dashboard</title>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Instructor Dashboard</h1>
    <?php if($msg) echo "<div class='alert'>$msg</div>"; ?>

    <h2>Your Course Rosters & Grading</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Course</th>
                <th>Current Grade</th>
                <th>Update Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $roster->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
                <td><?php echo $row['course_name']; ?></td>
                <td><strong><?php echo $row['grade']; ?></strong></td>
                <td>
                    <form action="" method="POST" style="margin:0; padding:0; background:none; border:none; display:inline-flex;">
                        <input type="hidden" name="enrollment_id" value="<?php echo $row['enrollment_id']; ?>">
                        <input type="text" name="grade" value="<?php echo $row['grade']; ?>" style="width:60px; margin-right:10px; padding:5px;" required>
                        <button type="submit" name="update_grade" style="margin:0; padding:5px 10px; font-size:14px;">Save</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>