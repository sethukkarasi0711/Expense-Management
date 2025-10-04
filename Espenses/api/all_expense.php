<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
if (!is_admin()) json_response(['error'=>'Forbidden'],403);
$company_id = intval($_SESSION['company_id']);
$res = $mysqli->query("SELECT e.*, u.name as employee_name FROM expenses e JOIN users u ON e.employee_id=u.id WHERE u.company_id=$company_id ORDER BY e.created_at DESC");
json_response(['expenses'=>$res->fetch_all(MYSQLI_ASSOC)]);
