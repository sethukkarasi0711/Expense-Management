<?php
require_once __DIR__ . '/../lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="POST">
        Email: <input name="email"><br>
        Password: <input name="password" type="password"><br>
        <button>Login</button>
    </form>';
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $mysqli->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc() ?: null;
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo "Invalid credentials. <a href='login.php'>Try again</a>";
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['company_id'] = $user['company_id'];

if ($user['role'] === 'Admin') {
    header('Location: admin_dashboard.php');
} elseif ($user['role'] === 'Manager') {
    header('Location: manager_dashboard.php');
} else {
    header('Location: employee_dashboard.php');
}
exit;
?>
