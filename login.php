<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    header("Location: login.html");
    exit();
}

require_once "db.php";

$stmt = $conn->prepare(
    "SELECT id, name, email, password FROM students WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (
    $student &&
    (password_verify($password, $student["password"]) ||
        hash_equals($student["password"], $password))
) {
    session_regenerate_id(true);

    $_SESSION["student_id"] = $student["id"];
    $_SESSION["student_name"] = $student["name"];
    $_SESSION["student_email"] = $student["email"];

    header("Location: studentdashboard.php");
    exit();
}

header("Location: login.html?error=invalid_credentials");
exit();

?>