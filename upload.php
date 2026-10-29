<?php

session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: studentdashboard.php");
    exit();
}

$category = trim($_POST["category"] ?? "");
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");

if ($title === "" || $description === "") {
    http_response_code(400);
    exit("Please provide a complaint title and description.");
}

$image = null;
$upload = $_FILES["image"] ?? null;

if ($upload && $upload["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($upload["error"] !== UPLOAD_ERR_OK || $upload["size"] > 5 * 1024 * 1024) {
        http_response_code(400);
        exit("The image could not be uploaded or exceeds the 5 MB limit.");
    }

    $imageInfo = getimagesize($upload["tmp_name"]);
    $extensions = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/gif" => "gif",
        "image/webp" => "webp"
    ];
    $mimeType = $imageInfo["mime"] ?? "";

    if (!isset($extensions[$mimeType])) {
        http_response_code(400);
        exit("Please upload a JPEG, PNG, GIF, or WebP image.");
    }

    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "complaints";
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        http_response_code(500);
        exit("The upload directory is unavailable.");
    }

    $image = bin2hex(random_bytes(16)) . "." . $extensions[$mimeType];
    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $image;
    if (!move_uploaded_file($upload["tmp_name"], $destination)) {
        http_response_code(500);
        exit("The image could not be saved.");
    }
}

$studentId = isset($_SESSION["student_id"]) ? (int) $_SESSION["student_id"] : null;
$status = "Pending";
$priority = "Low";
$stmt = $conn->prepare(
    "INSERT INTO complaints
        (student_id, category, title, description, image, status, priority)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "issssss",
    $studentId,
    $category,
    $title,
    $description,
    $image,
    $status,
    $priority
);

try {
    $stmt->execute();
    $stmt->close();
    header("Location: complaint_success.php");
    exit();
} catch (mysqli_sql_exception $exception) {
    $stmt->close();
    if ($image !== null) {
        $savedImage = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "complaints" . DIRECTORY_SEPARATOR . $image;
        if (is_file($savedImage) && !unlink($savedImage)) {
            error_log("Unable to remove unlinked complaint image: " . $savedImage);
        }
    }
    error_log("Complaint upload failed: " . $exception->getMessage());
    http_response_code(500);
    exit("The complaint could not be saved. Please try again.");
}

?>
