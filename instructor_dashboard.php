<?php
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];
$msg = ""; $class = "";

// 1. MANAGEMENT LOGIC: ADD N'IYAKORA UPDATE Y'AMANOTA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_grade'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $grade = strtoupper(trim($_POST['grade']));

    // Iri tegeko rihindura cyangwa rishyiraho inota rishya rya mwarimu ku munyeshuzi
    $stmt = $conn->prepare("UPDATE enrollments SET grade = ? WHERE enrollment_id = ?");
    $stmt->bind_param("si", $grade, $enrollment_id);
    
    if($stmt->execute()) { 
        $msg = "Amanota y'umunyeshuzi yajyanywemo neza/Ayahinduwe neza!"; 
        $class = "success";
    } else {
        $msg = "Hakorwemo ikosa ryo kubika amanota.";
        $class = "danger";
    }
}

// 2. FETCH ROSTER LOGIC: Reba abanyeshuzi BOSE bari mu masomo y'uyu mwarimu (yaba abafite amanota cyangwa abatarayagira)
$query = "SELECT e.enrollment_id, s.first_name, s.last_name, c.course_name, e.grade 
          FROM enrollments e
          INNER JOIN students s ON e.student_id = s.student_id
          INNER JOIN courses c ON e.course_id = c.course_id
          WHERE e.instructor_id = ? 
          ORDER BY c.course_name ASC, s.last_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$roster = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Assessment Console</title>
    <style>
        /* Modern Instructor Dashboard Layout */
        :root {
            --primary-color: #1e293b;    /* Slate 800 */
            --secondary-color: #475569;  /* Slate 600 */
            --success-color: #10b981;    /* Emerald 500 */
            --success-hover: #059669;    /* Emerald 600 */
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
            max-width: 1000px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05),
                        0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

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
            margin-bottom: 30px;
            display: inline-block;
        }

        h2 {
            font-size: 1.4rem;
            margin-top: 10px;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border-color);
        }

        /* Table Roster Styling */
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
            vertical-align: middle;
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

        /* Grade Badges */
        .badge-grade {
            background-color: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            color: var(--primary-color);
            display: inline-block;
        }

        .badge-empty {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px dashed #fca5a5;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Inline Form elements inside table */
        .grade-input {
            width: 75px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 700;
            text-align: center;
            color: var(--primary-color);
            background-color: #f8fafc;
            box-sizing: border-box;
            transition: border-color 0.2s, background-color 0.2s;
        }

        .grade-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: #ffffff;
        }

        .save-btn {
            background-color: var(--success-color);
            color: #ffffff;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 8px;
            width: auto;
            transition: background-color 0.2s, transform 0.1s;
        }

        .save-btn:hover {
            background-color: var(--success-hover);
        }

        .save-btn:active {
            transform: scale(0.97);
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
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 5px;
        }

        .logout-btn:hover {
            background-color: var(--danger-hover);
        }

        /* Notification Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            border: 1px solid #a7f3d0;
            background-color: #f0fdf4;
            color: #15803d;
        }
        .alert.danger {
            border-color: #fca5a5;
            background-color: #fef2f2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Instructor Assessment Console</h1>
    
    <?php if($msg) echo "<div class='alert $class'>$msg</div>"; ?>

    <h2>Active Class Rosters & Performance Grading</h2>
    <table>
        <thead>
            <tr>
                <th>Student Identity</th>
                <th>Course Context</th>
                <th>Current Grade</th>
                <th>Input / Update Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($roster->num_rows == 0): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--secondary-color); padding: 30px;">
                        Nta muryango w'abanyeshuri ufitanye isano n'isomo ryawe kugeza ubu.
                    </td>
                </tr>
            <?php else: ?>
                <?php while($row = $roster->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                    
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    
                    <td>
                        <?php if(empty($row['grade'])): ?>
                            <span class="badge-empty">No Grade (N/A)</span>
                        <?php else: ?>
                            <span class="badge-grade"><?php echo htmlspecialchars($row['grade']); ?></span>
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <form action="" method="POST" style="margin:0; padding:0; border:none; display:inline-flex; background:none; align-items:center;">
                            <input type="hidden" name="enrollment_id" value="<?php echo $row['enrollment_id']; ?>">
                            <input type="text" name="grade" class="grade-input" 
                                   value="<?php echo htmlspecialchars($row['grade']); ?>" 
                                   placeholder="A" maxlength="3" required>
                            <button type="submit" name="update_grade" class="save-btn">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>