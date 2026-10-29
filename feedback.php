<?php
session_start();
include("db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];
$message = "";
$success = "";
$error = "";

/* Submit Feedback */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $message = trim($_POST['message']);

    if ($message == "") {
        $error = "Please enter your feedback.";
    } else {

        $sql = "INSERT INTO feedback (student_id, message)
                VALUES (?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $student_id, $message);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Feedback submitted successfully!";
        } else {
            $error = "Unable to submit feedback.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Feedback - IGMS</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background: #f5f7fc;
    color: #222;
}

/* Header - same as existing pages */

header {
    background: #0b4ea2;
    color: white;
    padding: 18px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 28px;
    font-weight: bold;
}

nav a {
    color: white;
    text-decoration: none;
    margin-left: 25px;
    font-weight: bold;
}

/* Main */

.container {
    width: 90%;
    max-width: 1100px;
    margin: 50px auto;
}

h1 {
    color: #0b4ea2;
    margin-bottom: 10px;
    font-size: 48px;
}

.welcome {
    color: #555;
    font-size: 24px;
    margin-bottom: 35px;
}

/* Feedback Card */

.feedback-card {
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
    max-width: 900px;
}

.feedback-card h2 {
    color: #0b4ea2;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
}

textarea {
    width: 100%;
    min-height: 180px;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    resize: vertical;
}

textarea:focus {
    outline: none;
    border-color: #0b4ea2;
}

/* Button */

button {
    margin-top: 20px;
    background: #0b4ea2;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #083b7c;
}

/* Messages */

.success {
    background: #d1e7dd;
    color: #0f5132;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.error {
    background: #f8d7da;
    color: #842029;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Back button */

.back {
    display: inline-block;
    margin-top: 25px;
    background: #0b4ea2;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
}

</style>
<link rel="stylesheet" href="student-portal.css">
</head>

<body>

<header>

    <div class="logo">IGMS</div>

    <nav>
        <a href="studentdashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="view_complaints.php">Complaints</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>

<div class="container">

    <h1>Feedback</h1>

    <p class="welcome">
        Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>.
        Share your feedback about the grievance system.
    </p>

    <?php if ($success != "") { ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <div class="feedback-card">

        <h2>Share Your Feedback</h2>

        <form method="POST" action="feedback.php">

            <label for="message">Your Feedback</label>

            <textarea
                name="message"
                id="message"
                placeholder="Enter your feedback here..."
                required></textarea>

            <button type="submit">
                Submit Feedback
            </button>

        </form>

    </div>

    <a href="studentdashboard.php" class="back">
        ← Back to Dashboard
    </a>

</div>

</body>
</html>