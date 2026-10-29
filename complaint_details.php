<?php

include "admin_auth.php";
include __DIR__ . "/db.php";

$id = (int) ($_GET["id"] ?? 0);

$allowedStatuses = [
    "Pending",
    "Under Review",
    "In Progress",
    "Resolved",
    "Rejected"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newStatus = $_POST["status"] ?? "";

    if (in_array($newStatus, $allowedStatuses, true)) {
        $update = $conn->prepare(
            "UPDATE grievances SET status = ? WHERE id = ?"
        );
        $update->bind_param("si", $newStatus, $id);
        $update->execute();
        $update->close();

        $action = "Changed complaint status to " . $newStatus;
        $activity = $conn->prepare(
            "INSERT INTO admin_activity_log (admin_id, action, complaint_id) VALUES (?, ?, ?)"
        );
        $activity->bind_param("isi", $_SESSION["admin_id"], $action, $id);
        $activity->execute();
        $activity->close();
    }

    header("Location: complaint_details.php?id=" . $id);
    exit();
}

$stmt = $conn->prepare("
SELECT g.*, COALESCE(s.name, g.student, 'Anonymous') AS student_name,
       s.email AS student_email
FROM grievances g
LEFT JOIN students s ON s.id = g.student_id
WHERE g.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) {
    http_response_code(404);
    die("Complaint not found.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Complaint Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin.css?v=20261006-1">
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div class="topbar">
        <h3>Complaint Details</h3>
        <a href="admin_complaints.php" class="btn btn-secondary">Back to Complaints</a>
    </div>

    <div class="card mt-4 p-4">
        <h4><?= htmlspecialchars($complaint["subject"]) ?></h4>
        <dl class="row mt-4">
            <dt class="col-sm-3">Complaint ID</dt>
            <dd class="col-sm-9">#<?= (int) $complaint["id"] ?></dd>

            <dt class="col-sm-3">Student</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["student_name"]) ?></dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["student_email"] ?? "-") ?></dd>

            <dt class="col-sm-3">Department</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["department"] ?? "-") ?></dd>

            <dt class="col-sm-3">Category</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["category"] ?? "-") ?></dd>

            <dt class="col-sm-3">Priority</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["priority"] ?? "-") ?></dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <form method="POST" class="d-flex gap-2">
                    <select name="status" class="form-select" required>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $complaint["status"] === $status ? "selected" : "" ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9"><?= nl2br(htmlspecialchars($complaint["description"] ?? "")) ?></dd>

            <dt class="col-sm-3">Submitted</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($complaint["created_at"]) ?></dd>
        </dl>
    </div>
</div>

</body>
</html>
