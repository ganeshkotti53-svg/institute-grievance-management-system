<?php
session_start();
include("db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];
$message = "";
$error = "";

/* UPDATE PROFILE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);

    if ($name == "" || $email == "" || $department == "") {
        $error = "Please fill all required fields.";
    } else {

        $sql = "UPDATE students
                SET name = ?, email = ?, phone = ?, department = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $name,
            $email,
            $phone,
            $department,
            $student_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $_SESSION['student_name'] = $name;
            $message = "Profile updated successfully!";

        } else {
            $error = "Unable to update profile. Email may already exist.";
        }

        mysqli_stmt_close($stmt);
    }
}

/* GET STUDENT DETAILS */
$sql = "SELECT id, name, email, phone, department
        FROM students
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    die("Student profile not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Profile - IGMS</title>

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

/* HEADER */

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

nav a:hover {
    text-decoration: underline;
}

/* MAIN */

.container {
    width: 90%;
    max-width: 1000px;
    margin: 40px auto;
}

h1 {
    color: #0b4ea2;
    font-size: 40px;
    margin-bottom: 10px;
}

.welcome {
    color: #555;
    font-size: 18px;
    margin-bottom: 30px;
}

/* CARD */

.profile-card {
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.10);
}

.profile-card h2 {
    color: #0b4ea2;
    margin-bottom: 25px;
}

/* FORM */

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 7px;
    font-size: 16px;
}

input:focus {
    outline: none;
    border-color: #0b4ea2;
}

/* BUTTON */

button {
    background: #0b4ea2;
    color: white;
    border: none;
    padding: 13px 25px;
    border-radius: 7px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #083b7d;
}

/* MESSAGES */

.success {
    background: #d1e7dd;
    color: #0f5132;
    padding: 12px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.error {
    background: #f8d7da;
    color: #842029;
    padding: 12px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.back {
    display: inline-block;
    margin-top: 20px;
    background: #0b4ea2;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 6px;
}

</style>
<link rel="stylesheet" href="student-portal.css">

</head>

<body>

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


<div class="container">

    <h1>Profile</h1>

    <p class="welcome">
        Welcome, <?php echo htmlspecialchars($student['name']); ?>.
        View and update your profile details.
    </p>


    <div class="profile-card">

        <h2>My Profile</h2>


        <?php if ($message != "") { ?>

            <div class="success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <?php if ($error != "") { ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST" action="profile.php">

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($student['name']); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($student['email']); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Phone</label>

                <input
                    type="text"
                    name="phone"
                    value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                >

            </div>


            <div class="form-group">

                <label>Department</label>

                <input
                    type="text"
                    name="department"
                    value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>"
                    required
                >

            </div>


            <button type="submit">
                Update Profile
            </button>

        </form>


        <a href="studentdashboard.php" class="back">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>