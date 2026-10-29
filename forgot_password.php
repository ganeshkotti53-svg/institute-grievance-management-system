<?php

require_once "db.php";

$message = "";
$messageType = "";
$email = "";
$rollNo = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $rollNo = trim($_POST["roll_no"] ?? "");
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $email === "" ||
        $rollNo === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {
        $message = "Please fill in all fields.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "danger";
    } elseif (strlen($newPassword) < 6) {
        $message = "Password must contain at least 6 characters.";
        $messageType = "danger";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT id FROM students WHERE email = ? AND roll_no = ? LIMIT 1"
            );
            $stmt->bind_param("ss", $email, $rollNo);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$student) {
                $message = "The email address and roll number do not match an account.";
                $messageType = "danger";
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare(
                    "UPDATE students SET password = ? WHERE id = ?"
                );
                $update->bind_param("si", $passwordHash, $student["id"]);
                $update->execute();
                $update->close();

                $success = true;
                $message = "Your password was updated successfully.";

                $mailSubject = "IGMS password updated";
                $mailBody = "Hello,\n\nYour IGMS password was successfully updated.\n\nIf you did not make this change, please contact the administrator immediately.";
                $mailHeaders = "From: IGMS <noreply@grievance-system.local>\r\n" .
                    "Reply-To: noreply@grievance-system.local\r\n";

                if (!@mail($email, $mailSubject, $mailBody, $mailHeaders)) {
                    $message .= " The confirmation email could not be sent.";
                }
            }
        } catch (mysqli_sql_exception $exception) {
            error_log("Student password reset failed: " . $exception->getMessage());
            $message = "Unable to reset your password right now. Please try again.";
            $messageType = "danger";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-page">

<div class="card">
    <div class="auth-brand">
        <span class="auth-brand-mark">I</span>
        <span>Institute Grievance Management<small>Account recovery</small></span>
    </div>
    <h2>Reset password</h2>
    <p class="auth-description">Verify your student details and choose a new password.</p>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, "UTF-8") ?>" role="<?= $success ? "status" : "alert" ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            <?php if ($success): ?>
                <a href="login.html">Log in</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="email">Registered email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                    autocomplete="email"
                    required>
            </div>
            <div class="form-group">
                <label for="roll_no">Roll number</label>
                <input
                    id="roll_no"
                    type="text"
                    name="roll_no"
                    value="<?= htmlspecialchars($rollNo, ENT_QUOTES, "UTF-8") ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="new_password">New password</label>
                <input
                    id="new_password"
                    type="password"
                    name="new_password"
                    minlength="6"
                    autocomplete="new-password"
                    required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    minlength="6"
                    autocomplete="new-password"
                    required>
            </div>
            <button type="submit">Update password</button>
        </form>
    <?php endif; ?>

    <a href="login.html" class="back-link">Back to login</a>
</div>

</body>
</html>
