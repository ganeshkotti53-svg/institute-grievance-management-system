<?php

include "admin_auth.php";
include "db.php";

$result = $conn->query("
SELECT
    g.id,
    g.subject AS title,
    g.priority,
    g.status,
    g.created_at,
    COALESCE(s.name, g.student, 'Anonymous') AS student_name
FROM grievances g
LEFT JOIN students s
ON g.student_id = s.id
WHERE
    LOWER(g.status) = 'escalated'
    OR g.escalation_level > 1
    OR (
        g.status IN ('Pending','Under Review','In Progress')
        AND g.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    )
ORDER BY g.created_at ASC
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Escalated Complaints</title>

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

<h3>Escalated Complaints</h3>

</div>

<div class="card mt-4 p-4">

<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Student</th>
<th>Title</th>
<th>Priority</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td>
#<?= $row["id"] ?>
</td>

<td>
<?= htmlspecialchars(
$row["student_name"] ?? "Anonymous"
) ?>
</td>

<td>
<?= htmlspecialchars($row["title"]) ?>
</td>

<td>
<?= $row["priority"] ?>
</td>

<td>
<span class="badge bg-danger">
<?= $row["status"] ?>
</span>
</td>

<td>
<?= $row["created_at"] ?>
</td>

<td>

<a
href="complaint_details.php?id=<?= $row["id"] ?>"
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

</body>

</html>