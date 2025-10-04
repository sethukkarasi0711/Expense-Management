<?php
require_once __DIR__ . '/../lib/helpers.php';
require_login();
$u = current_user();
if ($u['role'] !== 'Employee') {
    header("Location: login.php"); exit;
}
?>
<h2>Employee Dashboard - <?=htmlspecialchars($u['name'])?></h2>
<a href="ocr_upload.php">Upload Receipt (OCR)</a> |
<a href="logout.php">Logout</a>

<hr>
<h3>Submit Expense</h3>
<form id="expForm">
  Amount: <input name="amount"><br>
  Currency: <input name="currency" value="INR"><br>
  Category: <input name="category"><br>
  Description: <input name="description"><br>
  Date: <input type="date" name="date"><br>
  <button>Submit</button>
</form>

<hr>
<h3>My Expenses</h3>
<div id="my"></div>

<script>
document.getElementById("expForm").onsubmit = async e=>{
  e.preventDefault();
  let obj={};
  new FormData(e.target).forEach((v,k)=>obj[k]=v);
  let r=await fetch("../api/submit_expense.php",{method:"POST",headers:{'Content-Type':'application/json'},body:JSON.stringify(obj)});
  let j=await r.json();
  alert(JSON.stringify(j));
  loadMine();
};
async function loadMine(){
  let r=await fetch("../api/my_expenses.php");
  let j=await r.json();
  document.getElementById("my").innerText=JSON.stringify(j,null,2);
}
loadMine();
</script>
