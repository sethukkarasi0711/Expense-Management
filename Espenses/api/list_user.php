<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$company_id = intval($_SESSION['company_id']);
$res = $mysqli->query("SELECT id,name,email,role,sub_role,manager_id FROM users WHERE company_id=$company_id");
$users = $res->fetch_all(MYSQLI_ASSOC);
json_response(['users'=>$users]);
