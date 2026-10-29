<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$id = $_SESSION["admin_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);

    $stmt = $conn->prepare("
        UPDATE admins
        SET name=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "si",
        $name,
        $id
    );

    $stmt->execute();

    $_SESSION["admin_name"] = $name;

    $message = "Profile updated successfully.";
}

$stmt = $conn->prepare("
SELECT *
FROM admins
WHERE id=?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$admin = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Profile</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="admin.css?v=20261006-1">

</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<div class="card p-4">

<h3>Admin Profile</h3>

<?php if (isset($message)): ?>

<div class="alert alert-success">
<?= $message ?>
</div>

<?php endif; ?>

<form method="POST">

<label>Name</label>

<input
name="name"
class="form-control mb-3"
value="<?= htmlspecialchars(
$admin["name"]
) ?>"
required>

<label>Email</label>

<input
class="form-control mb-3"
value="<?= htmlspecialchars(
$admin["email"]
) ?>"
disabled>

<label>Role</label>

<input
class="form-control mb-3"
value="Administrator"
disabled>

<button class="btn btn-primary">
Update Profile
</button>

<a
href="change_password.php"
class="btn btn-secondary">

Change Password

</a>

</form>

</div>

</div>

</body>

</html>