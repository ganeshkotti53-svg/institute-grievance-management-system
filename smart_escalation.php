<?php
session_start();

require_once "db.php";

// Keep older grievances tables compatible with the escalation workflow.
$required_columns = [
    'escalation_level' => "ALTER TABLE grievances ADD COLUMN escalation_level INT NOT NULL DEFAULT 1",
    'escalation_reason' => "ALTER TABLE grievances ADD COLUMN escalation_reason TEXT NULL"
];

foreach ($required_columns as $column_name => $alter_sql) {
    $safe_column_name = $conn->real_escape_string($column_name);
    $column_result = $conn->query("SHOW COLUMNS FROM grievances LIKE '{$safe_column_name}'");

    if ($column_result->num_rows === 0 && !$conn->query($alter_sql)) {
        die("Database migration failed: " . $conn->error);
    }
}

// 2. Flexible Session Check for Logged-In Student
$current_user_id = $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$current_user_id) {
    header("Location: login.php");
    exit();
}

$message = "";

// 3. Handle Manual / Smart Escalation Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'escalate') {
    $grievance_id    = intval($_POST['grievance_id']);
    $escalation_reason = trim($_POST['escalation_reason'] ?? 'Escalated due to lack of timely resolution.');

    // Check if the complaint exists and belongs to the user
    $check_stmt = $conn->prepare("SELECT id, escalation_level FROM grievances WHERE id = ? AND student_id = ?");
    $check_stmt->bind_param("ii", $grievance_id, $current_user_id);
    $check_stmt->execute();
    $res = $check_stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $current_level = intval($row['escalation_level'] ?? 1);
        $new_level     = $current_level + 1;

        // Update Escalation Level and Reason
        $update_stmt = $conn->prepare("UPDATE grievances SET escalation_level = ?, status = 'Escalated', escalation_reason = ? WHERE id = ? AND student_id = ?");
        $update_stmt->bind_param("isii", $new_level, $escalation_reason, $grievance_id, $current_user_id);

        if ($update_stmt->execute()) {
            $message = "<div class='alert alert-success'>Grievance successfully escalated to Level {$new_level} (Higher Authority).</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to escalate grievance. Please try again.</div>";
        }
        $update_stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>Grievance record not found.</div>";
    }
    $check_stmt->close();
}

// 4. Fetch Unresolved / Pending Complaints Eligible for Escalation
$sql = "SELECT *, 
               DATEDIFF(NOW(), created_at) AS days_pending 
        FROM grievances 
        WHERE student_id = ? AND status NOT IN ('Resolved', 'Closed')
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$grievances = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Escalation System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #eef2f5;
            color: #333333;
        }

        /* Top Blue Header Matching Dashboard */
        .hero-banner {
            background-color: #0066b2;
            color: #ffffff;
            padding: 35px 50px;
        }

        .hero-banner h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .hero-banner p {
            font-size: 15px;
            opacity: 0.9;
        }

        /* Container Layout */
        .page-container {
            max-width: 1000px;
            margin: 30px auto 40px auto;
            padding: 0 20px;
        }

        .content-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }

        .section-title {
            font-size: 22px;
            color: #005596;
            font-weight: 700;
            margin-bottom: 25px;
        }

        /* Alert Messages */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        /* Grievance Item Card */
        .grievance-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .grievance-item h3 {
            color: #005596;
            font-size: 18px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .grievance-item p {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .meta-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-escalated { background-color: #fee2e2; color: #991b1b; }
        .badge-level { background-color: #e0f2fe; color: #0369a1; }

        .escalation-box {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 12px;
            outline: none;
            background-color: #ffffff;
        }

        /* Buttons */
        .btn-escalate {
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-escalate:hover {
            background-color: #b91c1c;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 22px;
            background-color: #005596;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            transition: background-color 0.2s ease;
        }

        .btn-back:hover {
            background-color: #003e6d;
        }

        .empty-message {
            text-align: center;
            color: #64748b;
            padding: 40px 0;
            font-size: 15px;
        }
    </style>
    <link rel="stylesheet" href="student-portal.css">
</head>
<body>

<header>
    <div class="logo">IGMS</div>
    <nav aria-label="Main navigation">
        <a href="studentdashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="view_complaints.php">Complaints</a>
        <a href="logout.php">Sign out</a>
    </nav>
</header>

<!-- Dashboard Hero Banner -->
<div class="hero-banner">
    <h1>Smart Escalation</h1>
    <p>Escalate unresolved grievances automatically or manually to higher authorities.</p>
</div>

<!-- Main Section -->
<div class="page-container">
    <div class="content-card">
        <h2 class="section-title">Active & Pending Complaints</h2>

        <?= $message; ?>

        <?php if ($grievances && $grievances->num_rows > 0): ?>
            <?php while ($row = $grievances->fetch_assoc()): ?>
                <div class="grievance-item">
                    <div class="meta-badges">
                        <span class="badge <?= strtolower($row['status']) === 'escalated' ? 'badge-escalated' : 'badge-pending'; ?>">
                            Status: <?= htmlspecialchars($row['status']); ?>
                        </span>
                        <span class="badge badge-level">
                            Escalation Level: <?= intval($row['escalation_level'] ?? 1); ?>
                        </span>
                    </div>

                    <h3><?= htmlspecialchars($row['subject'] ?? $row['title'] ?? 'Grievance #' . $row['id']); ?></h3>
                    <p><strong>Department:</strong> <?= htmlspecialchars($row['department'] ?? 'N/A'); ?></p>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['description'] ?? $row['complaint'] ?? 'N/A')); ?></p>
                    
                    <?php if (!empty($row['escalation_reason'])): ?>
                        <p style="color: #b91c1c;"><strong>Last Escalation Reason:</strong> <?= htmlspecialchars($row['escalation_reason']); ?></p>
                    <?php endif; ?>

                    <div class="escalation-box">
                        <form method="POST" action="">
                            <input type="hidden" name="grievance_id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="action" value="escalate">
                            
                            <input type="text" name="escalation_reason" class="form-control" placeholder="Reason for escalation (e.g., No response for over 3 days)" required>
                            
                            <button type="submit" class="btn-escalate">⚡ Escalate to Higher Authority</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">No pending grievances eligible for escalation.</p>
        <?php endif; ?>

        <a href="studentdashboard.php" class="btn-back">&larr; Back to Dashboard</a>
    </div>
</div>

</body>
</html>