<?php
// api/create_user.php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
if (!is_admin()) json_response(['error'=>'Forbidden'],403);

$data = $_POST; // normal HTML form
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$pass = password_hash($data['password'], PASSWORD_BCRYPT);
$role = trim($data['role'] ?? '');
$company_id = intval($_SESSION['company_id']);

if (!$name || !$email || !$role) {
    echo "Missing required fields";
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO users (name,email,password,role,company_id) VALUES (?,?,?,?,?)");
$stmt->bind_param("ssssi", $name, $email, $pass, $role, $company_id);

if ($stmt->execute()) {
    echo "User created successfully";
} else {
    echo "Error: ".$stmt->error;
}
$stmt->close();
