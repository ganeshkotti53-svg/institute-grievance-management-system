<?php

include "admin_auth.php";
include "db.php";

$id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare("
SELECT
    c.*,
    s.name AS student_name,
    s.roll_no AS student_number,
    s.email AS student_email,
    s.phone AS student_phone,
    d.name AS department_name,
    st.name AS staff_name

FROM complaints c

LEFT JOIN students s
ON c.student_id = s.id

LEFT JOIN departments d
ON c.department_id = d.id

LEFT JOIN staff st
ON c.staff_id = st.id

WHERE c.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Complaint not found.");
}

$complaint = $result->fetch_assoc();

$historyStmt = $conn->prepare("
SELECT
    h.*,
    a.name AS admin_name
FROM complaint_history h
LEFT JOIN admins a
ON h.admin_id = a.id
WHERE h.complaint_id = ?
ORDER BY h.created_at DESC
");

$historyStmt->bind_param("i", $id);
$historyStmt->execute();

$history = $historyStmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Complaint Details</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="admin.css">

</head>

<body>

<?php include "sidebar.php"; ?>

<div class="main-content">

<div class="topbar">

<h3>
Complaint #<?= $complaint["id"] ?>
</h3>

</div>

<div class="card mt-4 p-4">

<h4>
<?= htmlspecialchars($complaint["title"]) ?>
</h4>

<hr>

<div class="row">

<div class="col-md-6">

<p>
<strong>Student:</strong>

<?php

if ($complaint["is_anonymous"]) {
    echo "Anonymous";
} else {
    echo htmlspecialchars(
        $complaint["student_name"] ?? "Unknown"
    );
}

?>

</p>

<p>
<strong>Student ID:</strong>
<?= htmlspecialchars(
$complaint["student_number"] ?? "-"
) ?>
</p>

<p>
<strong>Email:</strong>
<?= htmlspecialchars(
$complaint["student_email"] ?? "-"
) ?>
</p>

<p>
<strong>Category:</strong>
<?= htmlspecialchars($complaint["category"]) ?>
</p>

</div>

<div class="col-md-6">

<p>
<strong>Priority:</strong>

<span class="badge bg-warning">
<?= $complaint["priority"] ?>
</span>

</p>

<p>
<strong>Status:</strong>

<span class="badge bg-primary">
<?= $complaint["status"] ?>
</span>

</p>

<p>
<strong>Department:</strong>
<?= htmlspecialchars(
$complaint["department_name"] ?? "-"
) ?>
</p>

<p>
<strong>Staff:</strong>
<?= htmlspecialchars(
$complaint["staff_name"] ?? "-"
) ?>
</p>

</div>

</div>

<hr>

<h5>Description</h5>

<p>
<?= nl2br(
htmlspecialchars($complaint["description"])
) ?>
</p>

<?php if (!empty($complaint["image"])): ?>

<hr>

<h5>Uploaded Image</h5>

<img
src="uploads/complaints/<?= htmlspecialchars(
$complaint["image"]
) ?>"
class="img-fluid"
style="max-width:500px;">

<?php endif; ?>

<hr>

<h5>Admin Remarks</h5>

<p>
<?= nl2br(
htmlspecialchars(
$complaint["admin_remarks"] ?? ""
)
) ?>
</p>

<a
href="update_complaint.php?id=<?= $id ?>"
class="btn btn-primary">

Update Complaint

</a>

</div>

<div class="card mt-4 p-4">

<h4>Status History</h4>

<table class="table">

<thead>

<tr>

<th>Old Status</th>
<th>New Status</th>
<th>Admin</th>
<th>Remarks</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while ($h = $history->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars(
$h["old_status"] ?? "-"
) ?></td>

<td><?= htmlspecialchars(
$h["new_status"] ?? "-"
) ?></td>

<td><?= htmlspecialchars(
$h["admin_name"] ?? "-"
) ?></td>

<td><?= htmlspecialchars(
$h["remarks"] ?? "-"
) ?></td>

<td><?= $h["created_at"] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>