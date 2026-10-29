<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * Check whether admin is logged in
 */
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {

    header("Location: adminlogin.html");
    exit();

}
?>