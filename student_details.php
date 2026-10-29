<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$id = (int) ($_GET["id"] ?? 0);

$stmt = $conn->prepare("
SELECT id, name, email, phone, department
FROM students
WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    http_response_code(404);
    die("Student not found.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin.css?v=20261006-1">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h3>Student Details</h3>
        <a href="manage_students.php" class="btn btn-secondary">Back to Students</a>
    </div>

    <div class="card mt-4 p-4">
        <dl class="row">
            <dt class="col-sm-3">Student ID</dt>
            <dd class="col-sm-9">#<?= (int) $student["id"] ?></dd>

            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($student["name"]) ?></dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($student["email"]) ?></dd>

            <dt class="col-sm-3">Phone</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($student["phone"] ?? "-") ?></dd>

            <dt class="col-sm-3">Department</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($student["department"] ?? "-") ?></dd>
        </dl>
    </div>
</div>

</body>
</html>
