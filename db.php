<?php
$host = "127.0.0.1"; // Twakoresheje IP aho kuba localhost kwinda ibibazo bya port
$user = "root";
$password = "";
$database = "UniversityGrades2";

$conn = new mysqli($host, $user, $password, $database);
if($conn){
    echo " ";
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>