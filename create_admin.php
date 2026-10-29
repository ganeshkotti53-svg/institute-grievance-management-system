<?php

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit("Not found.");
}

require_once "db.php";

$existingAdmins = $conn->query("SELECT COUNT(*) AS total FROM admins");
if (!$existingAdmins) {
    fwrite(STDERR, "Unable to check for existing administrator accounts.\n");
    exit(1);
}

if ((int) $existingAdmins->fetch_assoc()["total"] > 0) {
    fwrite(STDERR, "An administrator account already exists; no account was created.\n");
    exit(1);
}

$name = trim((string) readline("Administrator name: "));
$email = trim((string) readline("Administrator email: "));
$password = (string) readline("Administrator password (input is visible): ");

if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    fwrite(STDERR, "Enter a name, a valid email address, and a non-empty password.\n");
    exit(1);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)"
);

if (!$stmt) {
    error_log("Administrator setup query preparation failed: " . $conn->error);
    fwrite(STDERR, "Unable to create the administrator account.\n");
    exit(1);
}

$stmt->bind_param("sss", $name, $email, $passwordHash);

try {
    $stmt->execute();
    fwrite(STDOUT, "Administrator account created successfully.\n");
} catch (mysqli_sql_exception $exception) {
    error_log("Administrator setup failed: " . $exception->getMessage());
    fwrite(STDERR, "Unable to create the administrator account. Check whether the email is already in use.\n");
    $stmt->close();
    exit(1);
}

$stmt->close();

?>
