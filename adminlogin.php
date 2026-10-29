<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adminlogin.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    header("Location: adminlogin.html?error=incomplete");
    exit();
}

require_once "db.php";

$stmt = $conn->prepare(
    "SELECT id, name, email, password
     FROM admins
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {
    error_log("Admin login query preparation failed: " . $conn->error);
    header("Location: adminlogin.html?error=service");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

$validPassword = $admin && (
    password_verify($password, $admin["password"]) ||
    hash_equals($admin["password"], $password)
);

if (!$validPassword) {
    header("Location: adminlogin.html?error=invalid_credentials");
    exit();
}

if (!password_verify($password, $admin["password"])) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");

    if (!$update) {
        error_log("Admin password migration preparation failed: " . $conn->error);
        header("Location: adminlogin.html?error=service");
        exit();
    }

    $update->bind_param("si", $newHash, $admin["id"]);
    $update->execute();
    $update->close();
}

session_regenerate_id(true);

$_SESSION["admin_id"] = $admin["id"];
$_SESSION["admin_name"] = $admin["name"];
$_SESSION["admin_email"] = $admin["email"];

$activity = $conn->prepare(
    "INSERT INTO admin_activity_log (admin_id, action) VALUES (?, ?)"
);

if ($activity) {
    $action = "Admin logged in";
    $activity->bind_param("is", $admin["id"], $action);
    $activity->execute();
    $activity->close();
}

header("Location: admin_dashboard.php");
exit();

?>
