<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student = $_SESSION['student_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

:root {
    color-scheme: light;
    --ink: #172b4d;
    --muted: #66758b;
    --brand: #155eef;
    --brand-dark: #1048bd;
    --line: #e6ebf2;
    --surface: #ffffff;
    --canvas: #f4f7fb;
}

body {
    margin: 0;
    color: var(--ink);
    background: var(--canvas);
    font-family: Inter, "Segoe UI", Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
}

a {
    color: inherit;
}

.site-header {
    position: relative;
    z-index: 1;
    background: var(--surface);
    border-bottom: 1px solid var(--line);
}

.header-inner {
    width: min(1160px, calc(100% - 48px));
    min-height: 76px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
}

.brand {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: var(--ink);
    font-size: 19px;
    font-weight: 750;
    letter-spacing: -.04em;
    text-decoration: none;
    white-space: nowrap;
}

.brand-mark {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: #fff;
    background: var(--brand);
    font-size: 16px;
}

.brand-caption {
    display: block;
    margin-top: 2px;
    color: var(--muted);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.primary-nav {
    display: flex;
    align-items: center;
    gap: 8px;
}

.primary-nav a {
    padding: 10px 13px;
    border-radius: 9px;
    color: #53637a;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: color .18s ease, background .18s ease;
}

.primary-nav a:hover,
.primary-nav a[aria-current="page"] {
    color: var(--brand);
    background: #eef4ff;
}

.primary-nav .sign-out {
    margin-left: 5px;
    border: 1px solid var(--line);
    color: #475569;
}

.primary-nav .sign-out:hover {
    border-color: #fecaca;
    color: #b42318;
    background: #fff5f4;
}

.page-content {
    width: min(1160px, calc(100% - 48px));
    margin: 34px auto 0;
}

.welcome-panel {
    position: relative;
    overflow: hidden;
    min-height: 230px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    padding: 38px 48px;
    border-radius: 20px;
    color: #fff;
    background: linear-gradient(115deg, #123b82 0%, #155eef 60%, #4388ff 100%);
    box-shadow: 0 14px 32px rgba(21, 94, 239, .16);
}

.welcome-panel::after {
    position: absolute;
    top: -135px;
    right: 82px;
    width: 350px;
    height: 350px;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 50%;
    content: "";
    box-shadow: 0 0 0 35px rgba(255, 255, 255, .035), 0 0 0 72px rgba(255, 255, 255, .025);
}

.welcome-copy {
    position: relative;
    z-index: 1;
    max-width: 650px;
}

.welcome-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    color: #d7e5ff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.welcome-copy h1 {
    margin: 0 0 10px;
    font-size: clamp(28px, 4vw, 38px);
    font-weight: 700;
    letter-spacing: -.04em;
}

.welcome-copy p {
    max-width: 510px;
    margin: 0;
    color: #e2ecff;
    font-size: 15px;
    line-height: 1.7;
}

.welcome-art {
    position: relative;
    z-index: 1;
    width: 128px;
    height: 128px;
    flex: 0 0 128px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .24);
    border-radius: 32px;
    color: #fff;
    background: rgba(255, 255, 255, .12);
    font-size: 57px;
    transform: rotate(3deg);
}

.section-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    margin: 38px 0 18px;
}

.section-heading h2 {
    margin: 0 0 5px;
    font-size: 21px;
    font-weight: 700;
    letter-spacing: -.025em;
}

.section-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
}

.section-label {
    color: #718198;
    font-size: 12px;
    font-weight: 650;
}

.cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    padding-bottom: 40px;
}

.card {
    min-height: 204px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 22px;
    border: 1px solid var(--line);
    border-radius: 15px;
    background: var(--surface);
    box-shadow: 0 2px 7px rgba(23, 43, 77, .035);
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.card:hover {
    border-color: #c7d7fe;
    box-shadow: 0 12px 24px rgba(23, 43, 77, .08);
    transform: translateY(-3px);
}

.card-icon {
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    margin-bottom: 16px;
    border-radius: 12px;
    color: var(--brand);
    background: #eef4ff;
    font-size: 17px;
}

.card h3 {
    margin: 0 0 7px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -.01em;
}

.card p {
    margin: 0 0 17px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.55;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: auto;
    color: var(--brand);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.card-link i {
    font-size: 11px;
    transition: transform .18s ease;
}

.card-link:hover i {
    transform: translateX(3px);
}

a:focus-visible {
    outline: 3px solid #84adff;
    outline-offset: 3px;
}

footer {
    padding: 20px 24px;
    border-top: 1px solid var(--line);
    color: var(--muted);
    background: var(--surface);
    text-align: center;
    font-size: 12px;
}

@media (max-width: 760px) {
    .header-inner {
        width: min(100% - 32px, 600px);
        min-height: auto;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 0;
    }

    .primary-nav {
        width: 100%;
        flex-wrap: wrap;
        gap: 4px;
    }

    .primary-nav a {
        padding: 9px 10px;
        font-size: 13px;
    }

    .primary-nav .sign-out {
        margin-left: 0;
    }

    .page-content {
        width: min(100% - 32px, 600px);
        margin-top: 22px;
    }

    .welcome-panel {
        min-height: 0;
        padding: 30px;
    }

    .welcome-art {
        width: 90px;
        height: 90px;
        flex-basis: 90px;
        border-radius: 24px;
        font-size: 40px;
    }

    .cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .welcome-panel {
        padding: 26px 22px;
    }

    .welcome-art {
        display: none;
    }

    .section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 7px;
        margin-top: 30px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .card {
        min-height: 185px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>

</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="studentdashboard.php" aria-label="Institute Grievance Management System home">
            <span class="brand-mark"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
            <span>IGMS<span class="brand-caption">Student portal</span></span>
        </a>
        <nav class="primary-nav" aria-label="Main navigation">
            <a href="studentdashboard.php" aria-current="page">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="view_complaints.php">Complaints</a>
            <a class="sign-out" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out</a>
        </nav>
    </div>
</header>

<main class="page-content">
    <section class="welcome-panel" aria-labelledby="welcome-title">
        <div class="welcome-copy">
            <span class="welcome-eyebrow"><i class="fa-solid fa-sparkles" aria-hidden="true"></i> Student dashboard</span>
            <h1 id="welcome-title">Welcome, <?php echo htmlspecialchars($student, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Manage your grievances, follow progress, and connect with the right support team from one place.</p>
        </div>
        <div class="welcome-art" aria-hidden="true">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
    </section>

    <section aria-labelledby="services-title">
        <div class="section-heading">
            <div>
                <h2 id="services-title">Grievance services</h2>
                <p>Choose a service to get started or manage an existing request.</p>
            </div>
            <span class="section-label">Your support, all in one place</span>
        </div>

        <div class="cards">
            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></span>
                <h3>Submit a grievance</h3>
                <p>Tell us about an issue and send it to the appropriate team.</p>
                <a href="submit_grievance.php" class="card-link">Start a submission <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                <h3>My complaints</h3>
                <p>Review the grievances you have already submitted.</p>
                <a href="view_complaints.php" class="card-link">View complaints <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                <h3>Track status</h3>
                <p>Check the latest progress on a grievance.</p>
                <a href="track.php" class="card-link">Track a grievance <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i></span>
                <h3>Share feedback</h3>
                <p>Let us know how we handled a resolved grievance.</p>
                <a href="feedback.php" class="card-link">Give feedback <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
                <h3>Your profile</h3>
                <p>Review and update your student profile details.</p>
                <a href="profile.php" class="card-link">Manage profile <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-thumbs-up" aria-hidden="true"></i></span>
                <h3>Support a grievance</h3>
                <p>Show support for an existing grievance that affects you.</p>
                <a href="support_grievance.php" class="card-link">Explore grievances <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-user-secret" aria-hidden="true"></i></span>
                <h3>Anonymous complaint</h3>
                <p>Submit an issue anonymously when you need added privacy.</p>
                <a href="anonymous_complaint.php" class="card-link">Submit anonymously <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                <h3>Verify resolution</h3>
                <p>Confirm whether a resolved grievance has been addressed.</p>
                <a href="verify_resolution.php" class="card-link">Verify a resolution <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>

            <article class="card">
                <span class="card-icon"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></span>
                <h3>Smart escalation</h3>
                <p>Escalate an unresolved grievance to the appropriate authority.</p>
                <a href="smart_escalation.php" class="card-link">View escalation options <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </article>
        </div>
    </section>
</main>

<footer>Institute Grievance Management System <span aria-hidden="true">&middot;</span> Student support portal</footer>

</body>
</html>