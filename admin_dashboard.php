<?php
session_start();
require 'db.php';

// Tegeka ko umuntu winjiye agomba kuba ari admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: login.php"); 
    exit; 
}

$msg = ""; $class = "";

// LOGIC 1: REGISTER STUDENT (USERS + STUDENTS)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_new_student'])) {
    $email = $_POST['email']; 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $major = $_POST['major'];

    $conn->begin_transaction();
    try {
        // 1. Injiza muri Users mbanze ukore konti
        $stmt_user = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'student')");
        $stmt_user->bind_param("ss", $email, $password);
        $stmt_user->execute();
        
        // Fata ya ID nshya imaze kuremwa muri users
        $new_user_id = $conn->insert_id;

        // 2. Injiza muri Students: hano dushizeho 'student_id' ihwanye na 'user_id' nk'uko constraint ibishaka
        $stmt_stud = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, major) VALUES (?, ?, ?, ?)");
        $stmt_stud->bind_param("isss", $new_user_id, $first_name, $last_name, $major);
        $stmt_stud->execute();

        $conn->commit();
        $msg = "Umunyeshuri yanditswe neza muri sisitemu icyarimwe!";
        $class = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "Ikosa ku munyeshuri: " . $e->getMessage();
        $class = "danger";
    }
}

// LOGIC 2: REGISTER INSTRUCTOR (USERS + INSTRUCTORS)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_new_instructor'])) {
    $email = $_POST['email']; 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['instructor_name']; 
    $department = $_POST['department'];

    $conn->begin_transaction();
    try {
        // 1. Injiza muri Users mbanze ukore konti
        $stmt_user = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'instructor')");
        $stmt_user->bind_param("ss", $email, $password);
        $stmt_user->execute();
        
        // Fata ya ID nshya imaze kuremwa muri users
        $new_user_id = $conn->insert_id;

        // 2. Injiza muri Instructors: niba na mwarimu bimeze bityo, koresha 'instructor_id' cyangwa 'user_id' ihwanye na $new_user_id
        // Niba table ya instructors idafite foreign key nka student, we 'instructor_id' ishobora kuba ari auto-increment (Niba byanze urambwira)
        $stmt_inst = $conn->prepare("INSERT INTO instructors (instructor_id, name, department) VALUES (?, ?, ?)");
        if(!$stmt_inst) {
            // Niba instructor_id ari auto_increment muri db yawe:
            $stmt_inst = $conn->prepare("INSERT INTO instructors (name, department) VALUES (?, ?)");
            $stmt_inst->bind_param("ss", $name, $department);
        } else {
            $stmt_inst->bind_param("iss", $new_user_id, $name, $department);
        }
        $stmt_inst->execute();

        $conn->commit();
        $msg = "Mwarimu yanditswe neza muri sisitemu icyarimwe!";
        $class = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "Ikosa kuri Mwarimu: " . $e->getMessage();
        $class = "danger";
    }
}

// LOGIC 3: ASSIGN COURSE PERMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_permission'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $instructor_id = $_POST['instructor_id'];

    $check = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ? AND instructor_id = ?");
    $check->bind_param("iii", $student_id, $course_id, $instructor_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $msg = "Iri sano rya kaminuza ryari ryamaze kubaho!";
        $class = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, instructor_id, grade) VALUES (?, ?, ?, NULL)");
        $stmt->bind_param("iii", $student_id, $course_id, $instructor_id);
        if ($stmt->execute()) {
            $msg = "Umunyeshuri yajyanywe mu isomo rya mwarimu neza!";
            $class = "success";
        } else {
            $msg = "Hakorwemo ikosa ryo kubika isano rishya.";
            $class = "danger";
        }
    }
}

// LOGIC 4: FETCH RECORDS FOR GRID TABLES
$students_res = $conn->query("SELECT * FROM students ORDER BY student_id DESC");
$instructors_res = $conn->query("SELECT * FROM instructors ORDER BY instructor_id DESC");
$courses_res = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html {
            height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.98)), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1920') no-repeat center center/cover;
            color: #ffffff; overflow: hidden;
        }
        .admin-wrapper { height: 100vh; width: 100vw; display: flex; flex-direction: column; padding: 20px; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; }
        .top-navbar h1 { font-size: 1.5rem; font-weight: 800; color: #3b82f6; }
        .logout-btn { background-color: #ef4444; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; }
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; flex: 1; min-height: 0; }
        .data-tables-section { display: flex; flex-direction: column; gap: 20px; overflow-y: auto; padding-right: 5px; }
        .glass-panel { background: rgba(255, 255, 255, 0.04); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 10px; }
        .data-tables-section::-webkit-scrollbar, .right-scroll-section::-webkit-scrollbar { width: 6px; }
        .data-tables-section::-webkit-scrollbar-thumb, .right-scroll-section::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }
        h2 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; color: #3b82f6; border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 5px; }
        .table-responsive { width: 100%; overflow-x: auto; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 10px 14px; font-size: 0.9rem; }
        th { background: rgba(255,255,255,0.06); color: #94a3b8; }
        td { background: rgba(255,255,255,0.01); border-bottom: 1px solid rgba(255,255,255,0.04); }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-size: 0.8rem; color: #cbd5e1; }
        select, input[type="text"], input[type="password"] { width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; color: white; font-size: 0.9rem; outline: none; }
        .submit-btn { width: 100%; padding: 10px; background-color: #3b82f6; border: none; border-radius: 8px; color: white; font-weight: 700; text-transform: uppercase; cursor: pointer; margin-top: 5px; }
        .submit-btn.green { background-color: #10b981; }
        .alert { padding: 10px 15px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 15px; text-align: center; }
        .alert.success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #a7f3d0; }
        .alert.danger { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5; }
        
        /* Tab Navigation Controls */
        .tab-navigation { display: flex; gap: 10px; margin-bottom: 15px; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 8px; }
        .tab-btn { flex: 1; padding: 8px; background: transparent; border: none; color: #94a3b8; font-weight: 600; cursor: pointer; border-radius: 6px; font-size: 0.85rem; transition: 0.2s; }
        .tab-btn.active { background: #3b82f6; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .right-scroll-section { overflow-y: auto; padding-right: 5px; }

        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } body { overflow-y: auto; } .admin-wrapper { height: auto; } }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <div class="top-navbar">
        <h1>System Command Center (Admin)</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if($msg) echo "<div class='alert $class'>$msg</div>"; ?>

    <div class="dashboard-grid">
        
        <div class="data-tables-section">
            <div class="glass-panel">
                <h2>System Registered Students</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Full Name</th><th>Department/Major</th></tr>
                        </thead>
                        <tbody>
                            <?php while($s = $students_res->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $s['student_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($s['major']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-panel">
                <h2>System Appointed Instructors</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Instructor Name</th><th>Specialty/Dept</th></tr>
                        </thead>
                        <tbody>
                            <?php while($i = $instructors_res->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $i['instructor_id']; ?></strong></td>
                                <td>
                                    <?php 
                                    if(isset($i['name'])) echo htmlspecialchars($i['name']);
                                    elseif(isset($i['first_name'])) echo htmlspecialchars($i['first_name'] . ' ' . ($i['last_name'] ?? ''));
                                    else echo "Instructor " . $i['instructor_id'];
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($i['department'] ?? ($i['specialty'] ?? 'N/A')); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="right-scroll-section">
            
            <div class="glass-panel">
                <h2>1. Add System Profile Accounts</h2>
                
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="switchTab('student-tab')">Student Account</button>
                    <button class="tab-btn" onclick="switchTab('instructor-tab')">Instructor Account</button>
                </div>

                <div id="student-tab" class="tab-content active">
                    <form action="" method="POST">
                        <input type="hidden" name="add_new_student" value="1">
                        <div class="form-group">
                            <label>Login Email Address</label>
                            <input type="text" name="email" placeholder="eg. keza@school.com" required>
                        </div>
                        <div class="form-group">
                            <label>Login Password</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Department / Major</label>
                            <input type="text" name="major" placeholder="eg. Software Engineering" required>
                        </div>
                        <button type="submit" class="submit-btn green">Register Student Profile</button>
                    </form>
                </div>

                <div id="instructor-tab" class="tab-content">
                    <form action="" method="POST">
                        <input type="hidden" name="add_new_instructor" value="1">
                        <div class="form-group">
                            <label>Login Email Address</label>
                            <input type="text" name="email" placeholder="eg. pro.minani@school.com" required>
                        </div>
                        <div class="form-group">
                            <label>Login Password</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label>Full Instructor Name</label>
                            <input type="text" name="instructor_name" placeholder="eg. Minani Bita" required>
                        </div>
                        <div class="form-group">
                            <label>Faculty / Department</label>
                            <input type="text" name="department" placeholder="eg. Software Engineering" required>
                        </div>
                        <button type="submit" class="submit-btn green" style="background-color: #8b5cf6;">Register Instructor Profile</button>
                    </form>
                </div>

            </div>

            <div class="glass-panel">
                <h2>2. Assign Course Permission</h2>
                <form action="" method="POST">
                    <input type="hidden" name="assign_permission" value="1">
                    <div class="form-group">
                        <label>Select Target Student</label>
                        <select name="student_id" required>
                            <option value="">-- Hitamo Umunyeshuri --</option>
                            <?php 
                            $students_res->data_seek(0);
                            while($s = $students_res->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $s['student_id']; ?>">
                                    <?php echo htmlspecialchars($s['first_name'].' '.$s['last_name'].' ('.$s['major'].')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Course Context</label>
                        <select name="course_id" required>
                            <option value="">-- Hitamo Isomo runaka --</option>
                            <?php while($c = $courses_res->fetch_assoc()): ?>
                                <option value="<?php echo $c['course_id']; ?>">
                                    <?php echo htmlspecialchars($c['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Appointed Instructor</label>
                        <select name="instructor_id" required>
                            <option value="">-- Hitamo Mwarimu --</option>
                            <?php 
                            $instructors_res->data_seek(0);
                            while($i = $instructors_res->fetch_assoc()): 
                                $inst_name = isset($i['name']) ? $i['name'] : ($i['first_name'] ?? 'Instructor #'.$i['instructor_id']);
                                $inst_dept = $i['department'] ?? 'N/A';
                            ?>
                                <option value="<?php echo $i['instructor_id']; ?>">
                                    <?php echo htmlspecialchars($inst_name . ' (' . $inst_dept . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Grant Permission</button>
                </form>
            </div>

        </div>

    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

</body>
</html>