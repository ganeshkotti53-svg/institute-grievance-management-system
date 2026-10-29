<?php

require_once "admin_auth.php";
require_once "db.php";

$totalComplaints = 0;
$pendingComplaints = 0;
$inProgressComplaints = 0;
$resolvedComplaints = 0;
$rejectedComplaints = 0;

/* Total complaints */

$result = $conn->query("SELECT COUNT(*) AS total FROM grievances");

if ($result) {
    $row = $result->fetch_assoc();
    $totalComplaints = $row['total'];
}

/* Pending */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM grievances
    WHERE status = 'Pending'
");

if ($result) {
    $row = $result->fetch_assoc();
    $pendingComplaints = $row['total'];
}

/* In Progress */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM grievances
    WHERE status IN ('In Progress', 'Under Review')
");

if ($result) {
    $row = $result->fetch_assoc();
    $inProgressComplaints = $row['total'];
}

/* Resolved */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM grievances
    WHERE status IN ('Resolved', 'Closed')
");

if ($result) {
    $row = $result->fetch_assoc();
    $resolvedComplaints = $row['total'];
}

/* Rejected */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM grievances
    WHERE status = 'Rejected'
");

if ($result) {
    $row = $result->fetch_assoc();
    $rejectedComplaints = $row['total'];
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - IGMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="admin.css?v=20261006-1">

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg admin-navbar">

    <div class="container-fluid">

        <span class="navbar-brand">
            IGMS Admin Panel
        </span>

        <span class="navbar-text">

            Welcome,
            <?= htmlspecialchars($_SESSION['admin_name']); ?>

            &nbsp; | &nbsp;

            <a
                href="admin_logout.php"
                class="btn btn-danger btn-sm">

                Logout

            </a>

        </span>

    </div>

</nav>


<div class="admin-layout">

    <?php include "sidebar.php"; ?>

    <main class="main-content dashboard">

            <h2 class="mb-4">
                Admin Dashboard
            </h2>


            <div class="row g-4">

                <!-- TOTAL -->

                <div class="col-md-3">

                    <a href="admin_complaints.php" class="text-decoration-none text-dark">
                    <div class="card p-4">

                        <h6>Total Complaints</h6>

                        <div class="stat-number text-primary">
                            <?= $totalComplaints; ?>
                        </div>

                    </div>
                    </a>

                </div>


                <!-- PENDING -->

                <div class="col-md-3">

                    <a href="admin_complaints.php?status=Pending" class="text-decoration-none text-dark">
                    <div class="card p-4">

                        <h6>Pending Complaints</h6>

                        <div class="stat-number text-warning">
                            <?= $pendingComplaints; ?>
                        </div>

                    </div>
                    </a>

                </div>


                <!-- IN PROGRESS -->

                <div class="col-md-3">

                    <a href="admin_complaints.php?status=In%20Progress" class="text-decoration-none text-dark">
                    <div class="card p-4">

                        <h6>In Progress</h6>

                        <div class="stat-number text-info">
                            <?= $inProgressComplaints; ?>
                        </div>

                    </div>
                    </a>

                </div>


                <!-- RESOLVED -->

                <div class="col-md-3">

                    <a href="admin_complaints.php?status=Resolved" class="text-decoration-none text-dark">
                    <div class="card p-4">

                        <h6>Resolved</h6>

                        <div class="stat-number text-success">
                            <?= $resolvedComplaints; ?>
                        </div>

                    </div>
                    </a>

                </div>


                <!-- REJECTED -->

                <div class="col-md-3">

                    <a href="admin_complaints.php?status=Rejected" class="text-decoration-none text-dark">
                    <div class="card p-4">

                        <h6>Rejected</h6>

                        <div class="stat-number text-danger">
                            <?= $rejectedComplaints; ?>
                        </div>

                    </div>
                    </a>

                </div>

            </div>


            <div class="card mt-5 p-4">

                <h4>
                    Admin Login Successful
                </h4>

                <p class="mb-1">

                    Admin ID:
                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_id']); ?>
                    </strong>

                </p>

                <p class="mb-1">

                    Name:
                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_name']); ?>
                    </strong>

                </p>

                <p>

                    Email:
                    <strong>
                        <?= htmlspecialchars($_SESSION['admin_email']); ?>
                    </strong>

                </p>

                <div class="alert alert-success">

                    Your admin session is working correctly.

            </main>

        </div>

    </div>

</div>

</body>

</html>