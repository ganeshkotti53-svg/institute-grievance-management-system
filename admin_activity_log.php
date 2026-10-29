<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$result = $conn->query("
SELECT
    l.*,
    a.name AS admin_name
FROM admin_activity_log l
LEFT JOIN admins a
ON l.admin_id = a.id
ORDER BY l.created_at DESC
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Activity Logs</title>

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

<h3>Admin Activity Logs</h3>

<table class="table table-bordered">

<thead>

<tr>

<th>Admin</th>
<th>Action</th>
<th>Complaint</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php if ($result->num_rows === 0): ?>

<tr>
<td colspan="4" class="text-center text-muted">
No activity recorded yet.
</td>
</tr>

<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars(
$row["admin_name"] ?? "-"
) ?>
</td>

<td>
<?= htmlspecialchars($row["action"]) ?>
</td>

<td>
<?= $row["complaint_id"]
? "#" . $row["complaint_id"]
: "-" ?>
</td>

<td>
<?= $row["created_at"] ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>