<?php

session_start();
include("db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM grievances WHERE student_id = ? ORDER BY id DESC"
);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>My Complaints - IGMS</title>

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

/* MAIN CONTAINER */

.container {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

h1 {
    color: #0b4ea2;
    font-size: 40px;
    margin-bottom: 10px;
}

.subtitle {
    color: #555;
    margin-bottom: 30px;
}

/* CARD */

.table-card {
    background: white;
    padding: 25px;
    border-radius: 12px;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.10);

    overflow-x: auto;
}

/* TABLE */

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #0b4ea2;
    color: white;
    padding: 14px;
    text-align: left;
}

td {
    padding: 14px;
    border-bottom: 1px solid #ddd;
    vertical-align: middle;
}

tr:hover {
    background: #f5f7fc;
}

/* STATUS */

.status {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    font-weight: bold;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.inprogress {
    background: #cfe2ff;
    color: #084298;
}

.resolved {
    background: #d1e7dd;
    color: #0f5132;
}

.rejected {
    background: #f8d7da;
    color: #842029;
}

/* IMAGE */

.complaint-image {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

/* BACK BUTTON */

.back {
    display: inline-block;
    margin-top: 25px;

    background: #0b4ea2;
    color: white;

    padding: 10px 20px;

    text-decoration: none;
    border-radius: 6px;

    font-weight: bold;
}

.back:hover {
    background: #083b7d;
}

/* MOBILE */

@media (max-width: 700px) {

    header {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
    }

    nav a {
        margin-left: 10px;
        font-size: 14px;
    }

    .container {
        width: 95%;
    }

    h1 {
        font-size: 32px;
    }

    th,
    td {
        padding: 10px;
        font-size: 14px;
    }

}

</style>
<link rel="stylesheet" href="student-portal.css">

</head>

<body>


<!-- HEADER -->

<header>

    <div class="logo">
        IGMS
    </div>

    <nav>

        <a href="studentdashboard.php">
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="track.php">
            Track
        </a>

        <a href="feedback.php">
            Feedback
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<!-- MAIN -->

<div class="container">

    <h1>
        My Complaints
    </h1>

    <p class="subtitle">
        View the complaints submitted through the grievance system.
    </p>


    <div class="table-card">

        <table>

            <tr>

                <th>ID</th>

                <th>Category</th>

                <th>Title</th>

                <th>Status</th>

                <th>Priority</th>

                <th>Image</th>

            </tr>


            <?php

            if (mysqli_num_rows($query) == 0) {

            ?>

            <tr>

                <td colspan="6"
                    style="text-align:center; padding:30px;">

                    No complaints found.

                </td>

            </tr>

            <?php

            }


            while ($row = mysqli_fetch_assoc($query)) {

                $status = strtolower(
                    trim($row['status'])
                );

            ?>

            <tr>

                <td>
                    <?php
                    echo htmlspecialchars($row['id']);
                    ?>
                </td>


                <td>
                    <?php
                    echo htmlspecialchars($row['category']);
                    ?>
                </td>


                <td>
                    <?php
                    echo htmlspecialchars($row['subject']);
                    ?>
                </td>


                <td>

                    <span class="status

                    <?php

                    if ($status == "pending") {

                        echo " pending";

                    } elseif ($status == "in progress") {

                        echo " inprogress";

                    } elseif ($status == "resolved") {

                        echo " resolved";

                    } elseif ($status == "rejected") {

                        echo " rejected";

                    }

                    ?>">

                        <?php
                        echo htmlspecialchars(
                            $row['status']
                        );
                        ?>

                    </span>

                </td>


                <td>
                    <?php
                    echo htmlspecialchars($row['priority']);
                    ?>
                </td>


                <td>

                    <?php

                    if (!empty($row['image'])) {

                    ?>

                        <img
                            src="uploads/<?php
                            echo htmlspecialchars(
                                $row['image']
                            );
                            ?>"
                            class="complaint-image"
                            alt="Complaint Image">

                    <?php

                    } else {

                        echo "No image";

                    }

                    ?>

                </td>

            </tr>

            <?php

            }

            ?>

        </table>

    </div>


    <a
        href="studentdashboard.php"
        class="back">

        ← Back to Dashboard

    </a>

</div>

</body>

</html>