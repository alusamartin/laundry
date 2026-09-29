<?php include 'db_connect.php';
$id = intval($_GET['id'] ?? 0);
$order = db_fetch("SELECT l.*, c.name as cname, c.phone as cphone, e.name as ename, e.location as eloc FROM laundry_list l LEFT JOIN customers c ON l.customer_id=c.id LEFT JOIN estates e ON l.estate_id=e.id WHERE l.id=?", [$id]);
if(!$order) die("Order not found");
$items = db_query("SELECT li.*, lc.name as cat_name FROM laundry_items li LEFT JOIN laundry_categories lc ON li.laundry_category_id=lc.id WHERE li.laundry_id=?", [$id]);
$settings = db_query("SELECT * FROM system_settings LIMIT 1")[0] ?? ['name'=>'LaundryPro Kenya','contact'=>'+254700000000','currency'=>'KES'];
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt #<?php echo $order['id'] ?></title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style>@media print{ .no-print{display:none} } body{font-family:monospace;}</style>
</head><body>
<div class="container mt-4" id="receipt">
  <div class="text-center mb-3">
    <h4><?php echo e($settings['name']) ?></h4>
    <small><?php echo e($settings['contact']) ?> | <?php echo e($settings['email']??'') ?></small><br>
    <small>Estate Laundry Service • <?php echo e($settings['timezone']??'Africa/Nairobi') ?></small><br>
    <small>Receipt #<?php echo str_pad($order['id'],6,'0',STR_PAD_LEFT) ?> • Queue #<?php echo $order['queue'] ?></small>
  </div>
  <hr>
  <div class="row">
    <div class="col-6"><b>Customer:</b> <?php echo e($order['cname'] ?: $order['customer_name']) ?><br><small><?php echo e($order['cphone']??'') ?></small><br><?php if($order['ename']): ?><small>Estate: <?php echo e($order['ename']) ?><?php echo $order['eloc']?' • '.e($order['eloc']):'' ?></small><?php endif; ?></div>
    <div class="col-6 text-right"><b>Date:</b> <?php echo date('d M Y H:i', strtotime($order['date_created'])) ?><br><b>Pickup:</b> <?php echo e($order['pickup_date']?:'') ?> <?php echo e($order['pickup_time_slot']??'') ?><br><?php if($order['gate_pin']): ?><span class="badge badge-dark">Gate PIN: <?php echo e($order['gate_pin']) ?></span><?php endif; ?></div>
  </div>
  <?php if($order['pickup_location']): ?><small>Pickup: <?php echo e($order['pickup_location']) ?></small><?php endif; ?>
  <?php if($order['gate_pass_notes']): ?><br><small>Gate Notes: <?php echo e($order['gate_pass_notes']) ?></small><?php endif; ?>
  <table class="table table-sm table-bordered mt-3">
    <thead><tr><th>Service</th><th class="text-right">Qty/Wt</th><th class="text-right">Unit</th><th class="text-right">Amount</th><th class="text-center">QR</th></tr></thead>
    <tbody>
      <?php foreach($items as $it): ?>
      <tr><td><?php echo e($it['cat_name']??'Item') ?></td><td class="text-right"><?php echo $it['weight'] ?></td><td class="text-right">KES <?php echo number_format($it['unit_price'],2) ?></td><td class="text-right">KES <?php echo number_format($it['amount'],2) ?></td><td class="text-center"><img src="https://chart.googleapis.com/chart?chs=60x60&cht=qr&chl=<?php echo urlencode($it['qr_code']??'LP'.$order['id']) ?>" style="width:40px;height:40px;"></td></tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3" class="text-right">TOTAL</th><th class="text-right">KES <?php echo number_format($order['total_amount'],2) ?></th><th></th></tr>
    <tr><td colspan="4" class="text-right"><small>Paid via <?php echo strtoupper(e($order['payment_method']??'cash')) ?> <?php echo $order['pay_status']?'<span class="badge badge-success">PAID</span>':'<span class="badge badge-danger">UNPAID</span>' ?> <?php echo e($order['mpesa_transaction_code']??'') ?></small></td><td></td></tr>
    </tfoot>
  </table>
  <div class="text-center"><img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo urlencode('LP-ORDER:'.$order['id'].';PIN:'.$order['gate_pin']) ?>" style="width:90px;height:90px;"><br><small>Scan to verify at gate</small></div>
  <p class="text-center mt-3"><small>Thank you! Your clothes will be ready soon. Questions? WhatsApp <?php echo e($settings['contact']) ?></small></p>
  <div class="no-print text-center mt-3"><button class="btn btn-primary" onclick="window.print()">Print</button> <a href="index.php?page=laundry" class="btn btn-secondary">Back</a></div>
</div>
</body></html>
