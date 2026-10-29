<?php
session_start();

require_once "db.php";

// 2. Session Check for Logged-In Student
$current_user_id = $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$current_user_id) {
    header("Location: login.php");
    exit();
}

$message = "";

// 3. Handle Student Verification Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $grievance_id = intval($_POST['grievance_id']);
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE grievances SET status = 'Closed', verification_status = 'Verified & Satisfied' WHERE id = ? AND student_id = ?");
        $stmt->bind_param("ii", $grievance_id, $current_user_id);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Thank you! Complaint marked as Verified and Closed.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update resolution status.</div>";
        }
        $stmt->close();

    } elseif ($action === 'reject') {
        $remarks = trim($_POST['rejection_remarks'] ?? 'Resolution unsatisfactory.');
        $stmt = $conn->prepare("UPDATE grievances SET status = 'In Progress', verification_status = 'Rejected', resolution_remarks = ? WHERE id = ? AND student_id = ?");
        $stmt->bind_param("sii", $remarks, $grievance_id, $current_user_id);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-warning'>Complaint re-opened for further review by administration.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to submit feedback.</div>";
        }
        $stmt->close();
    }
}

// 4. Fetch Grievances Needing Verification (Resolved or Closed status)
$sql = "SELECT * FROM grievances 
        WHERE student_id = ? AND (status = 'Resolved' OR status = 'Under Verification' OR status = 'Closed')
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
    <title>Resolution Verification</title>
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

        /* Top Blue Banner Matching Dashboard */
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
        .alert-warning { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .badge-resolved { background-color: #d1e7dd; color: #0f5132; }
        .badge-closed { background-color: #e2e8f0; color: #334155; }

        .verification-actions {
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .remarks-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 12px;
            outline: none;
            background-color: #ffffff;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        /* Buttons */
        .btn-confirm {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-reject {
            background-color: #ef4444;
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-confirm:hover, .btn-reject:hover {
            opacity: 0.9;
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
    <h1>Resolution Verification</h1>
    <p>Verify whether your grievance has been completely resolved to your satisfaction.</p>
</div>

<!-- Main Section -->
<div class="page-container">
    <div class="content-card">
        <h2 class="section-title">Resolved Complaints</h2>

        <?= $message; ?>

        <?php if ($grievances && $grievances->num_rows > 0): ?>
            <?php while ($row = $grievances->fetch_assoc()): ?>
                <div class="grievance-item">
                    <span class="status-badge <?= strtolower($row['status']) === 'closed' ? 'badge-closed' : 'badge-resolved'; ?>">
                        <?= htmlspecialchars($row['status']); ?>
                    </span>

                    <h3><?= htmlspecialchars($row['subject'] ?? $row['title'] ?? 'Grievance #' . $row['id']); ?></h3>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['description'] ?? $row['complaint'] ?? 'N/A')); ?></p>
                    
                    <?php if (!empty($row['resolution_remarks'] ?? '')): ?>
                        <p><strong>Resolution Remarks:</strong> <?= nl2br(htmlspecialchars($row['resolution_remarks'])); ?></p>
                    <?php endif; ?>

                    <?php if ($row['status'] !== 'Closed'): ?>
                        <div class="verification-actions">
                            <form method="POST" action="">
                                <input type="hidden" name="grievance_id" value="<?= $row['id']; ?>">
                                <input type="text" name="rejection_remarks" class="remarks-input" placeholder="Optional: If rejecting, state what is still pending...">
                                
                                <div class="action-buttons">
                                    <button type="submit" name="action" value="confirm" class="btn-confirm">✓ Satisfied & Confirm</button>
                                    <button type="submit" name="action" value="reject" class="btn-reject">✗ Unsatisfied (Re-open)</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <p style="margin-top: 10px; color: #10b981; font-weight: 700; font-size: 13px;">
                            ✓ Verified and Closed
                        </p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">No complaints currently pending verification.</p>
        <?php endif; ?>

        <a href="studentdashboard.php" class="btn-back">&larr; Back to Dashboard</a>
    </div>
</div>

</body>
</html>