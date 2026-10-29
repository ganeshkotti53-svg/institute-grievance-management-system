<?php

require_once "admin_auth.php";
require_once "db.php";

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$allowedStatuses = [
    "Pending",
    "Under Review",
    "In Progress",
    "Resolved",
    "Rejected",
    "Escalated"
];
$error = "";

$stmt = $conn->prepare(
    "SELECT id, title, status, admin_remarks FROM complaints WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    http_response_code(404);
    exit("Complaint not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newStatus = $_POST["status"] ?? "";
    $remarks = trim($_POST["admin_remarks"] ?? "");

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $error = "Please select a valid complaint status.";
    } else {
        try {
            $conn->begin_transaction();

            $update = $conn->prepare(
                "UPDATE complaints SET status = ?, admin_remarks = ? WHERE id = ?"
            );
            $update->bind_param("ssi", $newStatus, $remarks, $id);
            $update->execute();
            $update->close();

            $adminId = (int) $_SESSION["admin_id"];
            $history = $conn->prepare(
                "INSERT INTO complaint_history
                    (complaint_id, old_status, new_status, admin_id, remarks)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $history->bind_param(
                "issis",
                $id,
                $complaint["status"],
                $newStatus,
                $adminId,
                $remarks
            );
            $history->execute();
            $history->close();

            $conn->commit();
            header("Location: complaints.php?id=" . $id);
            exit();
        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            error_log("Complaint update failed: " . $exception->getMessage());
            $error = "The complaint could not be updated. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Complaint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css?v=20261006-1">
</head>
<body>

<?php include "sidebar.php"; ?>

<main class="main-content">
    <div class="card p-4">
        <h3>Update Complaint #<?= (int) $complaint["id"] ?></h3>
        <p><?= htmlspecialchars($complaint["title"], ENT_QUOTES, "UTF-8") ?></p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?= (int) $complaint["id"] ?>">

            <label class="form-label" for="status">Status</label>
            <select class="form-select mb-3" id="status" name="status" required>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option
                        value="<?= htmlspecialchars($status, ENT_QUOTES, "UTF-8") ?>"
                        <?= $status === $complaint["status"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($status, ENT_QUOTES, "UTF-8") ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label" for="admin_remarks">Admin remarks</label>
            <textarea
                class="form-control mb-3"
                id="admin_remarks"
                name="admin_remarks"
                rows="5"><?= htmlspecialchars($complaint["admin_remarks"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="complaints.php?id=<?= (int) $complaint["id"] ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

</body>
</html>
