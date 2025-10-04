<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$u = current_user();
if ($u['role'] !== 'Admin') {
    header("Location: login.php"); exit;
}
?>
<h2>Admin Dashboard - <?=htmlspecialchars($u['name'])?></h2>
<a href="logout.php">Logout</a>

<hr>
<h3>Company Setup</h3>
<p>Company: <?=htmlspecialchars($_SESSION['company_id'])?></p>
<p>Default currency is set at signup.</p>

<hr>
<h3>Manage Users</h3>
<form id="createUser">
  Name: <input name="name"><br>
  Email: <input name="email"><br>
  Password: <input name="password" type="password"><br>
  Role: <select name="role">
    <option value="Employee">Employee</option>
    <option value="Manager">Manager</option>
  </select><br>
  Sub-role: <select name="sub_role">
    <option value="None">None</option>
    <option value="Finance">Finance</option>
    <option value="Director">Director</option>
    <option value="CFO">CFO</option>
  </select><br>
  Manager ID (for employees): <input name="manager_id"><br>
  <button>Create</button>
</form>
<div id="userList"></div>

<hr>
<h3>Define Approval Rules</h3>
<form id="createRule">
  Step Order: <input name="step_order"><br>
  Approver Role:
  <select name="approver_role">
    <option>Manager</option>
    <option>Finance</option>
    <option>Director</option>
    <option>CFO</option>
  </select><br>
  Min Amount: <input name="min_amount"><br>
  Specific Approver ID (optional): <input name="specific_approver_id"><br>
  <button>Add Rule</button>
</form>

<hr>
<h3>All Expenses</h3>
<div id="allExpenses"></div>

<script>
async function loadUsers(){
  let r = await fetch("../api/list_users.php");
  let j = await r.json();
  document.getElementById("userList").innerText = JSON.stringify(j,null,2);
}
async function loadExpenses(){
  let r = await fetch("../api/all_expenses.php");
  let j = await r.json();
  document.getElementById("allExpenses").innerText = JSON.stringify(j,null,2);
}
document.getElementById("createUser").onsubmit = async e=>{
  e.preventDefault();
  let f = new FormData(e.target);
  await fetch("../api/create_user.php",{method:"POST",body:f});
  loadUsers();
};
document.getElementById("createRule").onsubmit = async e=>{
  e.preventDefault();
  let obj={};
  new FormData(e.target).forEach((v,k)=>obj[k]=v);
  await fetch("../api/create_rule.php",{method:"POST",body:JSON.stringify(obj),headers:{'Content-Type':'application/json'}});
  alert("Rule added");
};
loadUsers();
loadExpenses();
</script>
