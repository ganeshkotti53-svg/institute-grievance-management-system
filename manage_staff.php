<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $department = intval($_POST["department_id"]);

    $stmt = $conn->prepare("
        INSERT INTO staff
        (name,email,department_id)
        VALUES (?,?,?)
    ");

    $stmt->bind_param(
        "ssi",
        $name,
        $email,
        $department
    );

    $stmt->execute();
}

$departments = $conn->query("
SELECT *
FROM departments
ORDER BY name
");

$staff = $conn->query("
SELECT
    st.*,
    d.name AS department_name
FROM staff st
LEFT JOIN departments d
ON st.department_id = d.id
ORDER BY st.name
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Staff</title>

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

<h3>Add Staff</h3>

<form method="POST">

<input
name="name"
class="form-control mb-3"
placeholder="Staff name"
required>

<input
name="email"
type="email"
class="form-control mb-3"
placeholder="Email">

<select
name="department_id"
class="form-select mb-3">

<?php while ($d = $departments->fetch_assoc()): ?>

<option value="<?= $d["id"] ?>">

<?= htmlspecialchars($d["name"]) ?>

</option>

<?php endwhile; ?>

</select>

<button class="btn btn-primary">
Add Staff
</button>

</form>

</div>

<div class="card mt-4 p-4">

<h3>Staff List</h3>

<table class="table">

<thead>

<tr>

<th>Name</th>
<th>Email</th>
<th>Department</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php while ($row = $staff->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row["name"]) ?></td>

<td><?= htmlspecialchars($row["email"]) ?></td>

<td><?= htmlspecialchars(
$row["department_name"] ?? "-"
) ?></td>

<td><?= $row["status"] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>