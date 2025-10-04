<?php
// lib/approval_engine.php
require_once __DIR__ . '/helpers.php';

/**
 * Build approval chain for a new expense
 * - Uses approval_rules for the company
 * - Only includes steps where rule.min_amount <= expense amount
 */
function build_approval_chain($expense_id, $company_id, $expense_amount_company) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM approval_rules WHERE company_id=? ORDER BY step_order ASC");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rules = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // for each rule step, determine approver id(s). For simplicity, choose:
    // - if specific_approver_id set: use that user
    // - else find any user with role Manager and matching sub_role as rule (Finance/Director/CFO)
    $step = 1;
    foreach ($rules as $r) {
        if ($expense_amount_company < floatval($r['min_amount'])) continue;

        $approver_id = null;
        if (!empty($r['specific_approver_id'])) {
            $approver_id = intval($r['specific_approver_id']);
        } else {
            // find a user in company with role Manager and sub_role matching rule if needed
            if ($r['approver_role'] === 'Manager') {
                // choose the employee's manager (we'll link later)
                // placeholder: set approver_id to 0 (means dynamic manager)
                $approver_id = 0; // special flag meaning "use employee's manager"
            } else {
                $role = $r['approver_role']; // Finance/Director/CFO
                $stmt2 = $mysqli->prepare("SELECT id FROM users WHERE company_id=? AND sub_role=? LIMIT 1");
                $stmt2->bind_param("is", $company_id, $role);
                $stmt2->execute();
                $rr = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
                if ($rr) $approver_id = intval($rr['id']);
            }
        }

        // insert into approvals: if approver_id=0, we'll fill later when creating chain with employee manager.
        $stmtA = $mysqli->prepare("INSERT INTO approvals (expense_id, approver_id, step_order) VALUES (?, ?, ?)");
        $stmtA->bind_param("iii", $expense_id, $approver_id, $step);
        $stmtA->execute();
        $stmtA->close();
        $step++;
    }
}

/**
 * After a manager approves, move approval to next step; also check conditional rules like:
 * - percentage_required (not implemented fully here)
 * - specific approver quick-approve (handled by approval rows)
 */
function check_and_finalize($expense_id) {
    global $mysqli;

    // if any pending approvals exist -> leave as pending
    $stmt = $mysqli->prepare("SELECT COUNT(*) as pending FROM approvals WHERE expense_id=? AND status='Pending'");
    $stmt->bind_param("i", $expense_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($r['pending'] > 0) {
        return;
    }

    // all approvals done -> if any rejected -> mark expense Rejected else Approved
    $stmt2 = $mysqli->prepare("SELECT COUNT(*) as rejected FROM approvals WHERE expense_id=? AND status='Rejected'");
    $stmt2->bind_param("i", $expense_id);
    $stmt2->execute();
    $rr = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    $new_status = ($rr['rejected'] > 0) ? 'Rejected' : 'Approved';
    $stmt3 = $mysqli->prepare("UPDATE expenses SET status=? WHERE id=?");
    $stmt3->bind_param("si", $new_status, $expense_id);
    $stmt3->execute();
    $stmt3->close();
}
?>
