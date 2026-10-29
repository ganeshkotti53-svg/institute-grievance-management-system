<?php

include "admin_auth.php";
include "db.php";

$search = $_GET["search"] ?? "";
$status = $_GET["status"] ?? "";
$priority = $_GET["priority"] ?? "";

$sql = "
SELECT
    g.id,
    g.student_id,
    g.subject AS title,
    g.category,
    g.priority,
    g.status,
    g.created_at,
    CASE WHEN g.student_id IS NULL THEN 1 ELSE 0 END AS is_anonymous,
    COALESCE(s.name, g.student, 'Unknown') AS student_name,

    (
        SELECT COUNT(*)
        FROM grievance_votes gv
        WHERE gv.grievance_id = g.id
    ) AS supporters

FROM grievances g

LEFT JOIN students s
ON g.student_id = s.id

WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= "
    AND (
        g.subject LIKE ?
        OR g.category LIKE ?
        OR s.name LIKE ?
        OR g.student_id LIKE ?
    )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}

if ($status !== "") {

    if ($status === "Resolved") {
        $sql .= " AND g.status IN ('Resolved', 'Closed')";
    } else {
        $sql .= " AND g.status = ?";

        $params[] = $status;
        $types .= "s";
    }
}

if ($priority !== "") {

    $sql .= " AND g.priority = ?";

    $params[] = $priority;
    $types .= "s";
}

$sql .= " ORDER BY g.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Manage Complaints</title>

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

<h3>Manage Complaints</h3>

</div>

<div class="card mt-4 p-4">

<form method="GET">

<div class="row g-2">

<div class="col-md-4">

<input
type="text"
name="search"
value="<?= htmlspecialchars($search) ?>"
class="form-control"
placeholder="Search complaints...">

</div>

<div class="col-md-3">

<select name="status"
class="form-select">

<option value="">All Status</option>

<?php

$statuses = [
    "Pending",
    "Under Review",
    "In Progress",
    "Resolved",
    "Rejected"
];

foreach ($statuses as $s):

?>

<option
value="<?= $s ?>"
<?= $status === $s ? "selected" : "" ?>>

<?= $s ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3">

<select name="priority"
class="form-select">

<option value="">All Priority</option>

<option
value="High"
<?= $priority === "High" ? "selected" : "" ?>>

High

</option>

<option
value="Medium"
<?= $priority === "Medium" ? "selected" : "" ?>>

Medium

</option>

<option
value="Low"
<?= $priority === "Low" ? "selected" : "" ?>>

Low

</option>

</select>

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">
Filter
</button>

</div>

</div>

</form>

<div class="table-responsive mt-4">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>
<th>Student</th>
<th>Title</th>
<th>Category</th>
<th>Priority</th>
<th>Status</th>
<th>Support</th>
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

<?php if ($row["is_anonymous"]): ?>

<span class="badge bg-secondary">
Anonymous
</span>

<?php else: ?>

<?= htmlspecialchars(
$row["student_name"] ?? "Unknown"
) ?>

<?php endif; ?>

</td>

<td>
<?= htmlspecialchars($row["title"]) ?>
</td>

<td>
<?= htmlspecialchars($row["category"]) ?>
</td>

<td>

<?php

$priorityClass = match ($row["priority"]) {
    "High" => "danger",
    "Medium" => "warning",
    default => "success"
};

?>

<span class="badge bg-<?= $priorityClass ?>">

<?= $row["priority"] ?>

</span>

</td>

<td>

<?php

$statusClass = match ($row["status"]) {

    "Resolved" => "success",
    "Rejected" => "danger",
    "Escalated" => "dark",
    "In Progress" => "primary",
    default => "warning"

};

?>

<span class="badge bg-<?= $statusClass ?>">

<?= $row["status"] ?>

</span>

</td>

<td>
<?= $row["supporters"] ?>
</td>

<td>
<?= date(
"d-m-Y",
strtotime($row["created_at"])
) ?>
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

</div>

</body>

</html>