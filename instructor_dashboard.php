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

        /* Glassmorphic Central Container */
        .container {
            max-width: 1050px;
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

        /* Custom Scrollbar for Inner Container */
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

        /* Top Header Navigation */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        h2 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Table Section wrapper */
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
            vertical-align: middle;
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

        /* Grade Badges styles */
        .badge-grade {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 800;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: inline-block;
        }

        .badge-empty {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px dashed rgba(239, 68, 68, 0.4);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Input Controls inside table */
        .grade-input {
            width: 75px;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 700;
            text-align: center;
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
        }

        .grade-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
        }

        .save-btn {
            background-color: #10b981;
            color: #ffffff;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .save-btn:hover {
            background-color: #059669;
            box-shadow: 0 6px 14px rgba(5, 150, 105, 0.4);
            transform: translateY(-1px);
        }

        .save-btn:active {
            transform: translateY(0);
        }

        /* Logout Action Button */
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
        }

        /* Notification Alert Feedbacks */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            border: 1px solid rgba(16, 185, 129, 0.4);
            background-color: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }
        .alert.danger {
            border: 1px solid rgba(239, 68, 68, 0.4);
            background-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        @media (max-width: 650px) {
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .logout-btn { align-self: flex-end; }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <div class="container">
        
        <div class="dashboard-header">
            <h1>Instructor Assessment Console</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <?php if($msg) echo "<div class='alert $class'>$msg</div>"; ?>

        <h2>Active Class Rosters & Performance Grading</h2>
        
        <div class="table-responsive">
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
                            <td colspan="4" style="text-align: center; color: #cbd5e1; padding: 30px;">
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
        
    </div>

</div>

</body>
</html>