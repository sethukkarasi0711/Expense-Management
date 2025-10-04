<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/approval_engine.php';
require_login();
$me = current_user();

$input = json_decode(file_get_contents('php://input'), true);
$approval_id = intval($input['approval_id']);
$status = $input['status'] === 'Reject' ? 'Rejected' : 'Approved';
$comments = $mysqli->real_escape_string($input['comments'] ?? '');

// verify this user can act on this approval
$stmt = $mysqli->prepare("SELECT * FROM approvals WHERE id=? LIMIT 1");
$stmt->bind_param("i", $approval_id);
$stmt->execute();
$ap = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ap) json_response(['error'=>'Approval not found'],404);

// check approver assignment: either explicit approver_id == me OR approver_id==0 and I'm employee's manager
if ($ap['approver_id'] != 0 && intval($ap['approver_id']) !== intval($me['id'])) {
    json_response(['error'=>'Not allowed to act on this approval'],403);
}
if ($ap['approver_id'] == 0) {
    // check manager relation
    $exp = $mysqli->query("SELECT employee_id FROM expenses WHERE id=" . intval($ap['expense_id']))->fetch_assoc();
    $emp_id = intval($exp['employee_id']);
    $mgr = $mysqli->query("SELECT manager_id FROM users WHERE id=$emp_id")->fetch_assoc();
    if (!($mgr && intval($mgr['manager_id']) === intval($me['id']))) {
        json_response(['error'=>'Not the manager for this employee'],403);
    }
}

// update this approval
$stmt2 = $mysqli->prepare("UPDATE approvals SET status=?, comments=?, acted_at=NOW() WHERE id=?");
$stmt2->bind_param("ssi", $status, $comments, $approval_id);
$stmt2->execute();
$stmt2->close();

// check if need to set next approver's approver_id if it was 0 (dynamic manager)
// mark expense finalization if no pending approvals
check_and_finalize(intval($ap['expense_id']));

json_response(['message'=>'Action recorded']);
