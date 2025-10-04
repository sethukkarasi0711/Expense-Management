<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$me = current_user();
if (!$me) json_response(['error'=>'Unauthorized'],401);

$uid = intval($me['id']);
$company_id = intval($me['company_id']);

// approvals assigned to this approver OR approvals with approver_id=0 (dynamic manager) where the expense employee's manager == this user
$q = "
SELECT a.*, e.employee_id, e.amount, e.amount_company, e.currency, e.description, e.category, e.date, u.name as employee_name
FROM approvals a
JOIN expenses e ON a.expense_id = e.id
JOIN users u ON e.employee_id = u.id
WHERE a.status='Pending' AND (a.approver_id = ? OR a.approver_id = 0)
ORDER BY a.step_order ASC
";
$stmt = $mysqli->prepare($q);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$filtered = [];
foreach ($rows as $r) {
    if ($r['approver_id'] == 0) {
        // dynamic: only show if current user is the employee's manager
        $emp_id = intval($r['employee_id']);
        $rr = $mysqli->query("SELECT manager_id FROM users WHERE id=$emp_id")->fetch_assoc();
        if ($rr && intval($rr['manager_id']) === $uid) {
            $filtered[] = $r;
        }
    } else {
        $filtered[] = $r;
    }
}
json_response(['pending'=>$filtered]);
