 <?php
session_start();

require_once "db.php";

// 2. Flexible Session Check for Logged-In Student
$current_user_id = $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$current_user_id) {
    header("Location: login.php");
    exit();
}

$message = "";

// 3. Handle Support / Vote Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $grievance_id = intval($_POST['grievance_id']);

    // Check if student already voted
    $check_stmt = $conn->prepare("SELECT vote_id FROM grievance_votes WHERE grievance_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $grievance_id, $current_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert alert-warning'>You have already supported this grievance.</div>";
    } else {
        $vote_stmt = $conn->prepare("INSERT INTO grievance_votes (grievance_id, user_id) VALUES (?, ?)");
        $vote_stmt->bind_param("ii", $grievance_id, $current_user_id);

        if ($vote_stmt->execute()) {
            $message = "<div class='alert alert-success'>Thank you for supporting this grievance!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to submit vote. Please try again.</div>";
        }
        $vote_stmt->close();
    }
    $check_stmt->close();
}

// 4. Query Complaints From 'grievances' Table
$sql = "SELECT c.*, 
               COUNT(v.vote_id) AS total_votes,
               EXISTS(SELECT 1 FROM grievance_votes gv WHERE gv.grievance_id = c.id AND gv.user_id = ?) AS user_voted
        FROM grievances c
        LEFT JOIN grievance_votes v ON c.id = v.grievance_id
        WHERE c.student_id != ? OR c.student_id IS NULL
        GROUP BY c.id
        ORDER BY total_votes DESC, c.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$grievances = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Grievance</title>
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

        /* Top Blue Header Matching Student Dashboard */
        .hero-banner {
            background-color: #0066b2; /* Exact Dashboard Blue */
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

        /* Page Layout */
        .page-container {
            max-width: 1000px;
            margin: 30px auto 40px auto;
            padding: 0 20px;
        }

        /* Main Card Container */
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

        /* Message Alerts */
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

        /* Grievance Items */
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
            margin-bottom: 15px;
        }

        .grievance-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
        }

        .vote-count {
            font-weight: 700;
            color: #334155;
            font-size: 14px;
        }

        /* Buttons matching dashboard buttons */
        .btn-vote {
            background-color: #005596; /* Exact Button Blue */
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-vote:hover {
            background-color: #003e6d;
        }

        .btn-voted {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: not-allowed;
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
    <h1>Support a Grievance</h1>
    <p>Vote for existing grievances to raise priority with administrators.</p>
</div>

<!-- Main Section -->
<div class="page-container">
    <div class="content-card">
        <h2 class="section-title">Available Complaints</h2>

        <?= $message; ?>

        <?php if ($grievances && $grievances->num_rows > 0): ?>
            <?php while ($row = $grievances->fetch_assoc()): ?>
                <div class="grievance-item">
                    <h3><?= htmlspecialchars($row['subject'] ?? $row['title'] ?? 'Grievance #' . $row['id']); ?></h3>
                    <p><?= nl2br(htmlspecialchars($row['description'] ?? $row['complaint'] ?? 'No detailed description provided.')); ?></p>
                    
                    <div class="grievance-footer">
                        <span class="vote-count">👍 Total Support: <?= $row['total_votes']; ?></span>
                        <form method="POST" action="" style="margin:0;">
                            <input type="hidden" name="grievance_id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="action" value="vote">
                            
                            <?php if ($row['user_voted']): ?>
                                <button type="button" class="btn-voted" disabled>✓ Supported</button>
                            <?php else: ?>
                                <button type="submit" class="btn-vote">Support</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-message">No complaints available to support at this time.</p>
        <?php endif; ?>

        <a href="studentdashboard.php" class="btn-back">&larr; Back to Dashboard</a>
    </div>
</div>

</body>
</html>