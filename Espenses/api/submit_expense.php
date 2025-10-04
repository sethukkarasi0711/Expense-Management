<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/approval_engine.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);

$employee = current_user();
if (!$employee) json_response(['error'=>'Unauthorized'],401);

$amount = floatval($input['amount']);
$currency = strtoupper($input['currency']);
$category = $mysqli->real_escape_string($input['category']);
$description = $mysqli->real_escape_string($input['description']);
$date = $mysqli->real_escape_string($input['date']);

// get company currency
$company_id = intval($employee['company_id']);
$res = $mysqli->query("SELECT currency FROM company WHERE id=$company_id");
$company = $res->fetch_assoc();
$company_currency = $company ? strtoupper($company['currency']) : 'USD';

// convert amount to company currency
$amount_company = convert_currency($amount, $currency, $company_currency);
if ($amount_company === null) {
    json_response(['error'=>'Currency conversion failed'],500);
}

// insert expense
$stmt = $mysqli->prepare("INSERT INTO expenses (employee_id, amount, currency, amount_company, category, description, date) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("idddsss", $employee['id'], $amount, $currency, $amount_company, $category, $description, $date);
// note: bind types revised because PHP mysqli needs exact types; better to use strings for amounts, but keep simple
$stmt->execute();
$expense_id = $stmt->insert_id;
$stmt->close();

// build approvals based on company rules
build_approval_chain($expense_id, $company_id, $amount_company);

json_response(['message'=>'Expense submitted', 'expense_id'=>$expense_id]);
