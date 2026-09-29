<?php include 'db_connect.php';
$id = intval($_GET['id'] ?? 0);
$order = db_fetch("SELECT l.*, c.name as cname, c.phone as cphone, e.name as ename, d.name as dname FROM laundry_list l LEFT JOIN customers c ON l.customer_id=c.id LEFT JOIN estates e ON l.estate_id=e.id LEFT JOIN drivers d ON l.driver_id=d.id WHERE l.id=?", [$id]);
if(!$order) die("Order not found");
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pod_pin = trim($_POST['pod_pin']??'');
  $notes = trim($_POST['pod_notes']??'');
  // Validate gate PIN if present
  if($order['gate_pin'] && $pod_pin !== $order['gate_pin']){
    $err="Gate PIN mismatch. Askari PIN is ".$order['gate_pin'];
  } else {
    db_execute("UPDATE laundry_list SET status=6 WHERE id=?", [$id]);
    db_insert("INSERT INTO order_status_history (order_id,status,changed_by,notes) VALUES (?,?,?,?)", [$id,6,$_SESSION['login_id']??null, $notes]);
    require_once __DIR__.'/includes/sms.php';
    if($order['customer_id']){ $c=db_fetch("SELECT phone,name FROM customers WHERE id=?", [$order['customer_id']]); if($c) send_status_sms($c['phone'],$id,6,$c['name']); }
    header("Location: driver_pod.php?id=$id&done=1"); exit;
  }
}
?>
<div class="container-fluid">
<div class="col-lg-6 mx-auto">
<div class="card">
<div class="card-header"><b>Proof of Delivery — Order #<?php echo str_pad($order['id'],6,'0',STR_PAD_LEFT) ?></b> <span class="badge badge-<?php echo $order['status']==5?'dark':'secondary' ?>"><?php echo ['Pending','Received','Washing','Ironing','Ready','Out for Delivery','Delivered','Cancelled'][$order['status']] ?></span></div>
<div class="card-body">
<?php if(isset($_GET['done'])): ?><div class="alert alert-success">Delivered & SMS sent to customer!</div><?php endif; ?>
<?php if(isset($err)): ?><div class="alert alert-danger"><?php echo e($err) ?></div><?php endif; ?>
<p><b>Customer:</b> <?php echo e($order['cname']?:$order['customer_name']) ?> • <?php echo e($order['cphone']??'') ?><br>
<b>Estate:</b> <?php echo e($order['ename']??'Walk-in') ?> • <?php echo e($order['pickup_location']??'') ?><br>
<b>Amount:</b> KES <?php echo number_format($order['total_amount'],2) ?> • <?php echo $order['pay_status']?'<span class="badge badge-success">Paid</span>':'<span class="badge badge-danger">UNPAID</span>' ?> • <?php echo e($order['payment_method']) ?><br>
<b>Gate PIN:</b> <span class="h5 badge badge-dark"><?php echo e($order['gate_pin']??'—') ?></span> <small class="text-muted">Ask Askari for verification</small></p>
<?php if($order['status']!=6): ?>
<form method="POST">
<div class="form-group"><label>Enter Gate PIN shown to Askari</label><input type="text" name="pod_pin" class="form-control" placeholder="e.g. 4821" required></div>
<div class="form-group"><label>Delivery Notes / Signature name</label><input type="text" name="pod_notes" class="form-control" placeholder="Received by John"></div>
<button class="btn btn-success btn-block">Confirm Delivered</button>
</form>
<?php else: ?><div class="alert alert-info">Order already delivered on <?php echo date('d M Y H:i', strtotime($order['date_created'])) ?></div><?php endif; ?>
<a href="index.php?page=laundry" class="btn btn-secondary btn-block mt-2">Back to Orders</a>
</div>
</div>
</div>
</div>
