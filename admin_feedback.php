<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$feedback = $conn->query("
SELECT f.message, f.created_at,
       s.name AS student_name,
       s.email AS student_email
FROM feedback f
LEFT JOIN students s ON s.id = f.student_id
ORDER BY f.created_at DESC
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Feedback</title>

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

<h3>Student Feedback</h3>

<button
onclick="window.print()"
class="btn btn-primary">

Print Feedback

</button>

</div>

<div class="card mt-4 p-4">

<table class="table table-bordered">

<thead>

<tr>

<th>Student</th>
<th>Email</th>
<th>Feedback</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while ($row = $feedback->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars($row["student_name"]) ?>
</td>

<td>
<?= htmlspecialchars($row["student_email"] ?? "Unknown") ?>
</td>

<td>
<?= htmlspecialchars($row["message"] ?? "") ?>
</td>

<td>
<?= htmlspecialchars($row["created_at"]) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>