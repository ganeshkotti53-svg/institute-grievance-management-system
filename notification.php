<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$adminId = $_SESSION["admin_id"];

$conn->query("\
	INSERT INTO notifications (admin_id, title, message)
	SELECT a.id,
		   'New grievance submitted',
		   CONCAT('A new grievance titled \"', g.subject, '\" was submitted and is awaiting review.')
	FROM admins a
	CROSS JOIN grievances g
	WHERE NOT EXISTS (
		SELECT 1
		FROM notifications n
		WHERE n.admin_id = a.id
		  AND n.title = 'New grievance submitted'
		  AND n.message = CONCAT('A new grievance titled \"', g.subject, '\" was submitted and is awaiting review.')
	)
");

$stmt = $conn->prepare("
SELECT *
FROM notifications
WHERE admin_id = ?
ORDER BY created_at DESC
");

$stmt->bind_param("i", $adminId);
$stmt->execute();

$result = $stmt->get_result();

$conn->query("
UPDATE notifications
SET is_read = 1
WHERE admin_id = $adminId
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notifications</title>

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

<h3>Notifications</h3>

<?php if ($result->num_rows === 0): ?>

<div class="alert alert-info">
No notifications available.
</div>

<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>

<div class="border-bottom py-3">

<h5>
<?= htmlspecialchars($row["title"]) ?>
</h5>

<p>
<?= nl2br(
htmlspecialchars($row["message"])
) ?>
</p>

<small class="text-muted">
<?= $row["created_at"] ?>
</small>

</div>

<?php endwhile; ?>

</div>

</div>

</body>

</html>