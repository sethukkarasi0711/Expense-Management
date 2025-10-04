<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$me = current_user();
$uid = intval($me['id']);
$res = $mysqli->query("SELECT * FROM expenses WHERE employee_id=$uid ORDER BY created_at DESC");
json_response(['expenses'=>$res->fetch_all(MYSQLI_ASSOC)]);
