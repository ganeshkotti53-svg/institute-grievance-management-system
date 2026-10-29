<?php
session_start();

require_once "db.php";

// 2. Flexible Session Check
$current_user_id = $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$current_user_id) {
    header("Location: login.php");
    exit();
}

$message = "";

// 3. Handle Anonymous Grievance Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department  = trim($_POST['department'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $subject     = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($department) || empty($category) || empty($subject) || empty($description)) {
        $message = "<div class='alert alert-danger'>Please fill out all required fields.</div>";
    } else {
        // Save complaint completely anonymously (student_id set to NULL)
        $stmt = $conn->prepare("INSERT INTO grievances (student_id, student, department, category, subject, description, status) VALUES (NULL, 'Anonymous', ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssss", $department, $category, $subject, $description);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Your anonymous complaint has been submitted successfully! Your identity remains completely protected.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to submit complaint. Error: " . htmlspecialchars($conn->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Anonymous Complaint</title>
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

        /* Page Layout */
        .page-container {
            max-width: 900px;
            margin: 30px auto 40px auto;
            padding: 0 20px;
        }

        /* Main Form Card */
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
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        /* Form Group Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #333333;
            background-color: #f8fafc;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            border-color: #005596;
            background-color: #ffffff;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Action Buttons */
        .btn-submit {
            background-color: #005596;
            color: #ffffff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-submit:hover {
            background-color: #003e6d;
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
            margin-left: 10px;
        }

        .btn-back:hover {
            background-color: #003e6d;
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
    <h1>Anonymous Complaint</h1>
    <p>Submit your grievance anonymously while keeping your identity fully protected.</p>
</div>

<!-- Main Form Section -->
<div class="page-container">
    <div class="content-card">
        <h2 class="section-title">Complaint Details</h2>

        <?= $message; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="department">Department</label>
                <select name="department" id="department" class="form-control" required>
                    <option value="">Select Department</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                    <option value="Administration">Administration</option>
                    <option value="Hostel & Amenities">Hostel & Amenities</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Academic">Academic</option>
                    <option value="Infrastructure">Infrastructure</option>
                    <option value="Harassment">Harassment</option>
                    <option value="Fee & Finance">Fee & Finance</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="subject">Complaint Subject</label>
                <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter complaint subject" required>
            </div>

            <div class="form-group">
                <label for="description">Complaint Description</label>
                <textarea name="description" id="description" class="form-control" placeholder="Provide detailed information regarding your complaint..." required></textarea>
            </div>

            <button type="submit" class="btn-submit">Submit Anonymous Complaint</button>
            <a href="studentdashboard.php" class="btn-back">&larr; Back to Dashboard</a>
        </form>
    </div>
</div>

</body>
</html>