<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$search = $_GET["search"] ?? "";

$stmt = $conn->prepare("
SELECT id, name, roll_no, email, phone, department
FROM students
WHERE name LIKE ?
   OR roll_no LIKE ?
   OR email LIKE ?
ORDER BY id DESC
");

$value = "%" . $search . "%";

$stmt->bind_param(
    "sss",
    $value,
    $value,
    $value
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Students</title>

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

<div class="topbar">

<h3>Manage Students</h3>

</div>

<div class="card mt-4 p-4">

<form class="mb-3">

<div class="input-group">

<input
type="text"
name="search"
value="<?= htmlspecialchars($search) ?>"
class="form-control"
placeholder="Search student">

<button class="btn btn-primary">
Search
</button>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered">

<thead>

<tr>

<th>Name</th>
<th>Roll Number</th>
<th>Email</th>
<th>Phone</th>
<th>Department</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row["name"]) ?></td>

<td><?= htmlspecialchars($row["roll_no"] ?? "-") ?></td>

<td><?= htmlspecialchars($row["email"]) ?></td>

<td><?= htmlspecialchars($row["phone"] ?? "-") ?></td>

<td><?= htmlspecialchars($row["department"] ?? "-") ?></td>

<td>

<a
href="student_details.php?id=<?= $row["id"] ?>"
class="btn btn-sm btn-primary">

View

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>