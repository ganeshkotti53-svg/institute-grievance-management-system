<?php

require_once "admin_auth.php";
require_once "db.php";

$adminId = (int) $_SESSION["admin_id"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
        $error = "Please complete all password fields.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Your new password must be at least 8 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "The new password and confirmation do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ? LIMIT 1");

        if (!$stmt) {
            $error = "Unable to verify your password right now. Please try again.";
        } else {
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$admin) {
                $error = "Admin account not found.";
            } elseif (
                !password_verify($currentPassword, $admin["password"])
                && !hash_equals($admin["password"], $currentPassword)
            ) {
                $error = "Your current password is incorrect.";
            } else {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");

                if (!$update) {
                    $error = "Unable to change your password right now. Please try again.";
                } else {
                    $update->bind_param("si", $newPasswordHash, $adminId);

                    if ($update->execute()) {
                        $message = "Password changed successfully.";
                    } else {
                        $error = "Unable to change your password right now. Please try again.";
                    }

                    $update->close();
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password - Admin</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="admin.css?v=20261006-1">
</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="card p-4">
        <h3>Change Password</h3>
        <p class="text-muted">Choose a new password for your administrator account.</p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-success" role="status">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="current_password" class="form-label">Current password</label>
            <input
                id="current_password"
                type="password"
                name="current_password"
                class="form-control mb-3"
                autocomplete="current-password"
                required>

            <label for="new_password" class="form-label">New password</label>
            <input
                id="new_password"
                type="password"
                name="new_password"
                class="form-control mb-3"
                minlength="8"
                autocomplete="new-password"
                required>

            <label for="confirm_password" class="form-label">Confirm new password</label>
            <input
                id="confirm_password"
                type="password"
                name="confirm_password"
                class="form-control mb-3"
                minlength="8"
                autocomplete="new-password"
                required>

            <button type="submit" class="btn btn-primary">Change Password</button>
            <a href="admin_profile.php" class="btn btn-secondary">Back to Profile</a>
        </form>
    </div>
</div>

</body>

</html>
