<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$u = current_user();
if ($u['role'] !== 'Manager') {
    header("Location: login.php"); exit;
}
?>
<h2>Manager Dashboard - <?=htmlspecialchars($u['name'])?></h2>
<a href="logout.php">Logout</a>

<hr>
<h3>Pending Approvals</h3>
<div id="pending"></div>

<h3>Team Expense History</h3>
<div id="team"></div>

<script>
async function loadPending(){
  let r=await fetch("../api/pending_approvals.php");
  let j=await r.json();
  let html="";
  j.pending.forEach(p=>{
    html += `<div>
      Expense #${p.expense_id} (${p.amount} ${p.currency}) by ${p.employee_name}
      <button onclick="approve(${p.id},'Approved')">Approve</button>
      <button onclick="approve(${p.id},'Rejected')">Reject</button>
    </div>`;
  });
  document.getElementById("pending").innerHTML=html;
}
async function approve(id,status){
  await fetch("../api/approve.php",{
    method:"POST",
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({approval_id:id,status:status,comments:"Reviewed"})
  });
  loadPending();
}
async function loadTeam(){
  let r=await fetch("../api/all_expenses.php"); // quick hack: admin API; better is dedicated "team_expenses.php"
  let j=await r.json();
  document.getElementById("team").innerText=JSON.stringify(j,null,2);
}
loadPending();
loadTeam();
</script>
