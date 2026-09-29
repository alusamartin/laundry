<?php include 'db_connect.php';
if(isset($_GET['id'])){ $row=db_fetch("SELECT * FROM estate_subscriptions WHERE id=?", [intval($_GET['id'])]); if($row) foreach($row as $k=>$v) $$k=$v; }
$customers = db_query("SELECT id,name FROM customers WHERE is_active=1 ORDER BY name ASC");
$estates = db_query("SELECT id,name FROM estates WHERE is_active=1 ORDER BY name ASC");
?>
<div class="container-fluid">
<form id="manage-sub">
  <input type="hidden" name="id" value="<?php echo $id ?? '' ?>">
  <div class="form-group"><label>Customer</label><select name="customer_id" class="custom-select" required><option value="">Select</option><?php foreach($customers as $c): ?><option value="<?php echo $c['id'] ?>" <?php echo isset($customer_id)&&$customer_id==$c['id']?'selected':'' ?>><?php echo e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Estate</label><select name="estate_id" class="custom-select" required><option value="">Select</option><?php foreach($estates as $e): ?><option value="<?php echo $e['id'] ?>" <?php echo isset($estate_id)&&$estate_id==$e['id']?'selected':'' ?>><?php echo e($e['name']) ?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label>Plan Name</label><input type="text" name="plan_name" class="form-control" value="<?php echo e($plan_name??'Basic 4x5kg') ?>" required></div>
  <div class="row"><div class="col-md-4"><div class="form-group"><label>Monthly Fee (KES)</label><input type="number" step="0.01" name="monthly_fee" class="form-control" value="<?php echo $monthly_fee??4999 ?>" required></div></div><div class="col-md-4"><div class="form-group"><label>Washes/Month</label><input type="number" name="washes_per_month" class="form-control" value="<?php echo $washes_per_month??4 ?>"></div></div><div class="col-md-4"><div class="form-group"><label>Billing Day</label><input type="number" min="1" max="28" name="billing_day" class="form-control" value="<?php echo $billing_day??1 ?>"></div></div></div>
  <div class="form-group"><label>Status</label><select name="status" class="custom-select"><option value="active" <?php echo ($status??'')=='active'?'selected':'' ?>>Active</option><option value="paused" <?php echo ($status??'')=='paused'?'selected':'' ?>>Paused</option><option value="cancelled" <?php echo ($status??'')=='cancelled'?'selected':'' ?>>Cancelled</option></select></div>
</form>
</div>
<script>
$('#manage-sub').submit(function(e){
  e.preventDefault(); start_load();
  $.ajax({url:'ajax.php?action=save_subscription', method:'POST', data:$(this).serialize(), success:function(r){ if(r==1){ alert_toast('Saved','success'); setTimeout(()=>location.reload(),1200);} else alert_toast('Failed','danger'); end_load(); }})
})
</script>
