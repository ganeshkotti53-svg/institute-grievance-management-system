 <?php

 $host = "YOUR_MYSQL_HOST";
 $username = "YOUR_MYSQL_USERNAME";
 $dbPassword = "YOUR_MYSQL_PASSWORD";
 $database = "YOUR_DATABASE_NAME";

 mysqli_report(MYSQLI_REPORT_OFF);

 $conn = new mysqli(
     $host,
     $username,
     $dbPassword,
     $database
 );

 if ($conn->connect_errno) {
     error_log("Database connection failed: " . $conn->connect_error);
     http_response_code(500);
     exit("Database connection unavailable. Check the hosting database settings.");
 }

 if (!$conn->set_charset("utf8mb4")) {
     error_log("Could not set database character set: " . $conn->error);
     http_response_code(500);
     exit("Database setup failed. Please try again later.");
 }

 ?>