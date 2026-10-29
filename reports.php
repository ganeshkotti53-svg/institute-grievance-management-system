<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$total = $conn->query(
    "SELECT COUNT(*) total FROM grievances"
)->fetch_assoc()["total"];

$pending = $conn->query(
    "SELECT COUNT(*) total
    FROM grievances
     WHERE status='Pending'"
)->fetch_assoc()["total"];

$resolved = $conn->query(
    "SELECT COUNT(*) total
    FROM grievances
    WHERE status IN ('Resolved', 'Closed')"
)->fetch_assoc()["total"];

$rejected = $conn->query(
    "SELECT COUNT(*) total
    FROM grievances
     WHERE status='Rejected'"
)->fetch_assoc()["total"];

$escalated = $conn->query(
    "SELECT COUNT(*) total
    FROM grievances
     WHERE status='Escalated'"
)->fetch_assoc()["total"];

$categories = $conn->query("
SELECT category, COUNT(*) total
FROM grievances
GROUP BY category
ORDER BY total DESC
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports</title>

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

<h3>Complaint Reports</h3>

<button
onclick="window.print()"
class="btn btn-primary">

Print Report

</button>

</div>

<div class="card mt-4 p-4">

<table class="table table-bordered">

<tr>
<th>Total Complaints</th>
<td><?= $total ?></td>
</tr>

<tr>
<th>Pending</th>
<td><?= $pending ?></td>
</tr>

<tr>
<th>Resolved</th>
<td><?= $resolved ?></td>
</tr>

<tr>
<th>Rejected</th>
<td><?= $rejected ?></td>
</tr>

<tr>
<th>Escalated</th>
<td><?= $escalated ?></td>
</tr>

</table>

<h4 class="mt-4">
Complaints by Category
</h4>

<table class="table table-bordered">

<thead>

<tr>

<th>Category</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php while ($row = $categories->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars($row["category"]) ?>
</td>

<td>
<?= $row["total"] ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>