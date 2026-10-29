<?php

include "admin_auth.php";
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name !== "") {

        $stmt = $conn->prepare("
            INSERT INTO departments
            (name, description)
            VALUES (?,?)
        ");

        $stmt->bind_param(
            "ss",
            $name,
            $description
        );

        $stmt->execute();
    }
}

$result = $conn->query("
SELECT *
FROM departments
ORDER BY name
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Departments</title>

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

<h3>Add Department</h3>

<form method="POST">

<div class="mb-3">

<input
type="text"
name="name"
class="form-control"
placeholder="Department name"
required>

</div>

<div class="mb-3">

<textarea
name="description"
class="form-control"
placeholder="Description"></textarea>

</div>

<button class="btn btn-primary">
Add Department
</button>

</form>

</div>

<div class="card mt-4 p-4">

<h3>Departments</h3>

<table class="table">

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Description</th>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $row["id"] ?></td>

<td><?= htmlspecialchars($row["name"]) ?></td>

<td><?= htmlspecialchars($row["description"]) ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>