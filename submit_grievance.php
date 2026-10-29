<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

$student_id = $_SESSION['student_id'];
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $department = trim($_POST['department'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = trim($_POST['priority'] ?? '');

    if (
        empty($department) ||
        empty($category) ||
        empty($subject) ||
        empty($description) ||
        empty($priority)
    ) {
        $message = "Please fill all the fields.";
        $message_type = "error";
    } else {

        $status = "Pending";

        $sql = "INSERT INTO grievances
                (student_id, department, category, subject, description, priority, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "issssss",
                $student_id,
                $department,
                $category,
                $subject,
                $description,
                $priority,
                $status
            );

            $stmt->execute();
            $stmt->close();

            $notification_failed = false;
            try {
                $notification_title = "New grievance submitted";
                $notification_message = "A new grievance titled '{$subject}' was submitted and is awaiting review.";
                $notification_stmt = $conn->prepare(
                    "INSERT INTO notifications (admin_id, title, message)
                     SELECT id, ?, ? FROM admins"
                );
                $notification_stmt->bind_param(
                    "ss",
                    $notification_title,
                    $notification_message
                );
                $notification_stmt->execute();
                $notification_stmt->close();
            } catch (mysqli_sql_exception $exception) {
                error_log("Grievance notification failed: " . $exception->getMessage());
                $notification_failed = true;
            }

            $message = $notification_failed
                ? "Your complaint was submitted, but the administrator notification could not be sent."
                : "Complaint Submitted Successfully!";
            $message_type = "success";

            $department = "";
            $category = "";
            $subject = "";
            $description = "";
            $priority = "";
        } catch (mysqli_sql_exception $exception) {
            error_log("Grievance submission failed: " . $exception->getMessage());
            $message = "Your complaint could not be submitted. Please verify the database setup and try again.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Submit Grievance - IGMS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fc;
            color: #222;
        }

        /* HEADER */

        .navbar {
            background: #1458a6;
            height: 92px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }

        .logo {
            color: white;
            font-size: 36px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 38px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: bold;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* MAIN */

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 55px 45px;
        }

        .page-title {
            color: #1458a6;
            font-size: 52px;
            margin: 0 0 12px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 24px;
            color: #444;
            margin-bottom: 38px;
        }

        /* CARD */

        .card {
            background: white;
            border-radius: 18px;
            padding: 42px 52px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .card-title {
            color: #1458a6;
            font-size: 32px;
            margin-top: 0;
            margin-bottom: 32px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 17px 18px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 19px;
            font-family: Arial, Helvetica, sans-serif;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #1458a6;
        }

        textarea {
            min-height: 180px;
            resize: vertical;
        }

        /* BUTTON */

        .submit-btn {
            background: #1458a6;
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 9px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #0e478c;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            background: #1458a6;
            color: white;
            padding: 12px 20px;
            border-radius: 9px;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            background: #0e478c;
        }

        /* MESSAGE */

        .message {
            padding: 15px 20px;
            border-radius: 9px;
            margin-bottom: 25px;
            font-size: 18px;
            font-weight: bold;
        }

        .success {
            background: #e5f7e9;
            color: #207238;
        }

        .error {
            background: #ffe8e8;
            color: #b42323;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .navbar {
                height: auto;
                padding: 20px;
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                gap: 18px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-links a {
                font-size: 17px;
            }

            .container {
                padding: 35px 20px;
            }

            .page-title {
                font-size: 40px;
            }

            .card {
                padding: 30px 22px;
            }
        }

    </style>
    <link rel="stylesheet" href="student-portal.css">
</head>

<body>

<!-- HEADER -->

<div class="navbar">

    <div class="logo">
        IGMS
    </div>

    <div class="nav-links">
        <a href="studentdashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="view_complaints.php">Complaints</a>
        <a href="track.php">Track</a>
        <a href="feedback.php">Feedback</a>
        <a href="logout.php">Logout</a>
    </div>

</div>


<!-- MAIN CONTENT -->

<div class="container">

    <h1 class="page-title">
        Submit Grievance
    </h1>

    <p class="subtitle">
        Submit your complaint through the institute grievance management system.
    </p>


    <div class="card">

        <h2 class="card-title">
            Complaint Details
        </h2>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- DEPARTMENT -->

            <div class="form-group">

                <label for="department">
                    Department
                </label>

                <select name="department" id="department" required>

                    <option value="">
                        Select Department
                    </option>

                    <option value="CSE"
                        <?php if (($department ?? '') == "CSE") echo "selected"; ?>>
                        CSE
                    </option>

                    <option value="ECE"
                        <?php if (($department ?? '') == "ECE") echo "selected"; ?>>
                        ECE
                    </option>

                    <option value="EEE"
                        <?php if (($department ?? '') == "EEE") echo "selected"; ?>>
                        EEE
                    </option>

                    <option value="Mechanical"
                        <?php if (($department ?? '') == "Mechanical") echo "selected"; ?>>
                        Mechanical
                    </option>

                    <option value="Civil"
                        <?php if (($department ?? '') == "Civil") echo "selected"; ?>>
                        Civil
                    </option>

                    <option value="Other"
                        <?php if (($department ?? '') == "Other") echo "selected"; ?>>
                        Other
                    </option>

                </select>

            </div>


            <!-- CATEGORY -->

            <div class="form-group">

                <label for="category">
                    Category
                </label>

                <select name="category" id="category" required>

                    <option value="">
                        Select Category
                    </option>

                    <option value="Infrastructure"
                        <?php if (($category ?? '') == "Infrastructure") echo "selected"; ?>>
                        Infrastructure
                    </option>

                    <option value="Academic"
                        <?php if (($category ?? '') == "Academic") echo "selected"; ?>>
                        Academic
                    </option>

                    <option value="Faculty"
                        <?php if (($category ?? '') == "Faculty") echo "selected"; ?>>
                        Faculty
                    </option>

                    <option value="Hostel"
                        <?php if (($category ?? '') == "Hostel") echo "selected"; ?>>
                        Hostel
                    </option>

                    <option value="Library"
                        <?php if (($category ?? '') == "Library") echo "selected"; ?>>
                        Library
                    </option>

                    <option value="Other"
                        <?php if (($category ?? '') == "Other") echo "selected"; ?>>
                        Other
                    </option>

                </select>

            </div>


            <!-- SUBJECT -->

            <div class="form-group">

                <label for="subject">
                    Complaint Subject
                </label>

                <input
                    type="text"
                    name="subject"
                    id="subject"
                    placeholder="Enter complaint subject"
                    value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                    required
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    Complaint Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    placeholder="Describe your complaint here..."
                    required
                ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

            </div>


            <!-- PRIORITY -->

            <div class="form-group">

                <label for="priority">
                    Priority
                </label>

                <select name="priority" id="priority" required>

                    <option value="">
                        Select Priority
                    </option>

                    <option value="Low"
                        <?php if (($priority ?? '') == "Low") echo "selected"; ?>>
                        Low
                    </option>

                    <option value="Medium"
                        <?php if (($priority ?? '') == "Medium") echo "selected"; ?>>
                        Medium
                    </option>

                    <option value="High"
                        <?php if (($priority ?? '') == "High") echo "selected"; ?>>
                        High
                    </option>

                </select>

            </div>


            <!-- SUBMIT -->

            <button type="submit" class="submit-btn">
                Submit Complaint
            </button>

        </form>

        <a href="studentdashboard.php" class="back-link">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>
</html>