<?php
// public/signup.php
require_once __DIR__ . '/../lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // HTML simple form for convenience
    echo '<form method="POST">
        Company: <input name="company"><br>
        Country: <input name="country"><br>
        Default currency (USD/INR): <input name="currency"><br>
        Admin name: <input name="admin"><br>
        Admin email: <input name="email"><br>
        Password: <input name="password" type="password"><br>
        <button>Sign Up</button>
    </form>';
    exit;
}

$company = trim($_POST['company']);
$country = trim($_POST['country']);
$currency = strtoupper(trim($_POST['currency']));
$admin = trim($_POST['admin']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

$stmt = $mysqli->prepare("INSERT INTO company (name, country, currency) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $company, $country, $currency);
$stmt->execute();
$company_id = $stmt->insert_id;
$stmt->close();

$stmt2 = $mysqli->prepare("INSERT INTO users (name,email,password,role,company_id,sub_role) VALUES(?,?,?,?,?,?)");
$role = 'Admin'; $sub='None';
$stmt2->bind_param("ssssis", $admin, $email, $password, $role, $company_id, $sub);
$stmt2->execute();
$stmt2->close();

echo "Company & Admin created. You can <a href='login.php'>login</a>.";
?>
