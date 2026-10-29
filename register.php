<?php

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$rollNo = trim($_POST["roll"] ?? "");
$department = trim($_POST["department"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm"] ?? "";

$error = "";

if (
    $name === "" ||
    $email === "" ||
    $rollNo === "" ||
    $department === "" ||
    $password === "" ||
    $confirmPassword === ""
) {
    $error = "Please complete all registration fields.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email address.";
} elseif (strlen($password) < 6) {
    $error = "Password must contain at least 6 characters.";
} elseif ($password !== $confirmPassword) {
    $error = "Passwords do not match.";
}

if ($error !== "") {
    http_response_code(400);
    echo htmlspecialchars($error, ENT_QUOTES, "UTF-8");
    exit();
}

$check = $conn->prepare(
    "SELECT email, roll_no FROM students WHERE email = ? OR roll_no = ? LIMIT 1"
);
$check->bind_param("ss", $email, $rollNo);
$check->execute();
$existingStudent = $check->get_result()->fetch_assoc();
$check->close();

if ($existingStudent) {
    $error = $existingStudent["email"] === $email
        ? "Email is already registered."
        : "Roll number is already registered.";
    http_response_code(409);
    echo htmlspecialchars($error, ENT_QUOTES, "UTF-8");
    exit();
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO students (name, email, roll_no, department, password)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $name, $email, $rollNo, $department, $passwordHash);

try {
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Registration Successful'); window.location='login.html';</script>";
    exit();
} catch (mysqli_sql_exception $exception) {
    $stmt->close();

    if ($exception->getCode() === 1062) {
        http_response_code(409);
        exit("Email or roll number is already registered.");
    }

    error_log("Student registration failed: " . $exception->getMessage());
    http_response_code(500);
    exit("Registration failed. Please try again.");
}

?>
