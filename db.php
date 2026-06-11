<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "UniversityGrades";
$port = 3306;

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }

    echo "Database connected successfully!";
} catch (Exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>