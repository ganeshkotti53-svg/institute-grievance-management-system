<?php
session_start();
include("db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

$student_name = isset($_SESSION['student_name'])
    ? $_SESSION['student_name']
    : "Student";

$sql = "SELECT id, subject, category, description, priority, status, created_at
        FROM grievances
        WHERE student_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Track Complaint Status</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f5f7fc;
}

/* Same Header as Student Dashboard */

header{
    background:#0b4ea2;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
}

.logo{
    font-size:28px;
    font-weight:bold;
}

nav a{
    color:white;
    text-decoration:none;
    margin-left:25px;
    font-weight:bold;
}

/* Page Heading */

.hero{
    background:linear-gradient(135deg,#0b4ea2,#0d63d6);
    color:white;
    padding:35px 40px;
}

.hero h1{
    font-size:35px;
    margin-bottom:8px;
}

.hero p{
    font-size:17px;
}

/* Complaint Cards */

.container{
    padding:40px;
}

.complaint-card{
    background:white;
    padding:30px;
    margin-bottom:20px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

.complaint-card h2{
    color:#0b4ea2;
    margin-bottom:20px;
}

.details p{
    margin:10px 0;
    line-height:1.6;
}

.status{
    display:inline-block;
    padding:7px 16px;
    border-radius:20px;
    font-weight:bold;
}

.pending{
    background:#fff3cd;
    color:#856404;
}

.inprogress{
    background:#cfe2ff;
    color:#084298;
}

.resolved{
    background:#d1e7dd;
    color:#0f5132;
}

.no-complaints{
    background:white;
    padding:40px;
    text-align:center;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

.no-complaints h2{
    color:#0b4ea2;
    margin-bottom:10px;
}

/* Same Button Style */

.btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 20px;
    background:#0b4ea2;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

/* Footer */

footer{
    background:#0b4ea2;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:30px;
}

</style>
<link rel="stylesheet" href="student-portal.css">

</head>

<body>

<!-- Header -->

<header>

<div class="logo">
IGMS
</div>

<nav>

<a href="studentdashboard.php">Dashboard</a>

<a href="profile.php">Profile</a>

<a href="view_complaints.php">Complaints</a>

<a href="logout.php">Logout</a>

</nav>

</header>


<!-- Page Heading -->

<section class="hero">

<h1>Track Complaint Status</h1>

<p>
Welcome, <?php echo htmlspecialchars($student_name); ?>.
Track the progress of your submitted grievances.
</p>

</section>


<!-- Complaints -->

<div class="container">

<?php

if (mysqli_num_rows($result) == 0) {

?>

<div class="no-complaints">

<h2>No Complaints Found</h2>

<p>
You have not submitted any complaints yet.
</p>

<a href="submit_grievance.php" class="btn">
Submit Grievance
</a>

</div>

<?php

}

while ($row = mysqli_fetch_assoc($result)) {

    $status = strtolower(trim($row['status']));

    $status_class = "";

    if ($status == "pending") {
        $status_class = "pending";
    }
    elseif ($status == "in progress") {
        $status_class = "inprogress";
    }
    elseif ($status == "resolved") {
        $status_class = "resolved";
    }

?>

<div class="complaint-card">

<h2>
Complaint #<?php echo htmlspecialchars($row['id']); ?>
</h2>

<div class="details">

<p>
<strong>Subject:</strong>
<?php echo htmlspecialchars($row['subject']); ?>
</p>

<p>
<strong>Category:</strong>
<?php echo htmlspecialchars($row['category']); ?>
</p>

<p>
<strong>Description:</strong>
<?php echo htmlspecialchars($row['description']); ?>
</p>

<p>
<strong>Priority:</strong>
<?php echo htmlspecialchars($row['priority']); ?>
</p>

<p>
<strong>Submitted On:</strong>
<?php echo htmlspecialchars($row['created_at']); ?>
</p>

<p>

<strong>Status:</strong>

<span class="status <?php echo $status_class; ?>">

<?php echo htmlspecialchars($row['status']); ?>

</span>

</p>

</div>

</div>

<?php
}

mysqli_stmt_close($stmt);
?>

<a href="studentdashboard.php" class="btn">
← Back to Dashboard
</a>

</div>


<footer>

© 2026 Institute Grievance Management System

</footer>

</body>

</html>