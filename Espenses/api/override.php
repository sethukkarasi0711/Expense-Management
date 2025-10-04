<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
if (!is_admin()) json_response(['error'=>'Forbidden'],403);
$data = json_decode(file_get_contents('php://input'), true);
$expense_id = intval($data['expense_id']);
$status = $mysqli->real_escape_string($data['status']); // Approved or Rejected
$stmt = $mysqli->prepare("UPDATE expenses SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $expense_id);
$stmt->execute();
$stmt->close();
// set all related approvals accordingly
$stmt2 = $mysqli->prepare("UPDATE approvals SET status=? WHERE expense_id=?");
$stmt2->bind_param("si", $status, $expense_id);
$stmt2->execute();
$stmt2->close();
json_response(['message'=>'Expense overridden']);
