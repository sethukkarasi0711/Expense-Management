<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
if (!is_admin()) json_response(['error'=>'Forbidden'],403);

$data = json_decode(file_get_contents('php://input'), true);
$company_id = intval($_SESSION['company_id']);
$step_order = intval($data['step_order']);
$approver_role = $mysqli->real_escape_string($data['approver_role']);
$percentage_required = isset($data['percentage_required']) ? intval($data['percentage_required']) : NULL;
$specific_approver_id = isset($data['specific_approver_id']) ? intval($data['specific_approver_id']) : NULL;
$min_amount = isset($data['min_amount']) ? floatval($data['min_amount']) : 0.0;

$stmt = $mysqli->prepare("INSERT INTO approval_rules (company_id, step_order, approver_role, percentage_required, specific_approver_id, min_amount) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("iisidi", $company_id, $step_order, $approver_role, $percentage_required, $specific_approver_id, $min_amount);
$stmt->execute();
$stmt->close();
json_response(['message'=>'Rule created']);
