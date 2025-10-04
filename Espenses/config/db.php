<?php
// config/db.php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'expense_management';
$DB_USER = 'root';
$DB_PASS = ''; // put your DB password

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
session_start();
?>
