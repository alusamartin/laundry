<?php include 'db_connect.php';
$estate_id = intval($_GET['estate_id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');
$slot = $_GET['slot'] ?? '';
$where = "WHERE l.pickup_date=?"; $params=[$date];
if($estate_id){ $where.=" AND l.estate_id=?"; $params[]=$estate_id; }
if($slot){ $where.=" AND l.pickup_time_slot=?"; $params[]=$slot; }
$where.=" AND l.status IN (0,1)";
$orders = db_query("SELECT l.*, c.name as cname, c.phone as cphone, b.name as block_name, u.unit_number, d.name as driver_name, e.name as ename FROM laundry_list l LEFT JOIN customers c ON l.customer_id=c.id LEFT JOIN estates e ON l.estate_id=e.id LEFT JOIN estate_blocks b ON c.block_id=b.id LEFT JOIN estate_units u ON c.unit_id=u.id LEFT JOIN drivers d ON l.driver_id=d.id $where ORDER BY e.name, b.name, u.unit_number", $params);
$estates = db_query("SELECT * FROM estates WHERE is_active=1 ORDER BY name ASC");
?>
<div class="container-fluid">
<div class="col-lg-12">
<div class="card">
<div class="card-header"><b>DAILY PICKUP MANIFEST — Batch by Estate/Block</b>
<form class="form-inline float-right" method="GET" action="index.php">
<input type="hidden" name="page" value="manifest">
<select name="estate_id" class="custom-select custom-select-sm mr-1"><option value="">All Estates</option><?php foreach($estates as $e): ?><option value="<?php echo $e['id'] ?>" <?php echo $estate_id==$e['id']?'selected':'' ?>><?php echo e($e['name']) ?></option><?php endforeach; ?></select>
<input type="date" name="date" class="form-control form-control-sm mr-1" value="<?php echo $date ?>">
<select name="slot" class="custom-select custom-select-sm mr-1"><option value="">Both Slots</option><option value="morning" <?php echo $slot=='morning'?'selected':'' ?>>Morning</option><option value="afternoon" <?php echo $slot=='afternoon'?'selected':'' ?>>Afternoon</option></select>
<button class="btn btn-primary btn-sm">Filter</button>
<button type="button" class="btn btn-dark btn-sm ml-1" onclick="window.print()">Print Manifest</button>
</form>
</div>
<div class="card-body" id="manifest-print">
<div class="text-center mb-2"><h5>Pickup Manifest — <?php echo date('d M Y', strtotime($date)) ?> <?php echo $slot?ucfirst($slot):'' ?> <?php echo $estate_id? '• '.e(db_fetch("SELECT name FROM estates WHERE id=?",[$estate_id])['name']):'' ?></h5><small><?php echo count($orders) ?> orders • Grouped by Block → Unit → Driver routes</small></div>
<?php
$grouped=[];
foreach($orders as $o){
  $key = ($o['ename']??'Walk-in').' | '.($o['block_name']??'No Block');
  $grouped[$key][]=$o;
}
foreach($grouped as $gname=>$list):
?>
<h6 class="mt-3" style="background:#f0f0f0;padding:6px;"><b><?php echo e($gname) ?></b> (<?php echo count($list) ?>)</h6>
<table class="table table-sm table-bordered">
<thead><tr><th>#</th><th>Customer</th><th>Unit</th><th>Pickup Loc</th><th>Gate PIN</th><th>Service</th><th>Driver</th><th>Amount</th><th>☐ Collected</th></tr></thead>
<tbody>
<?php $i=1; foreach($list as $r): ?>
<tr>
<td><?php echo $i++ ?></td>
<td><b><?php echo e($r['cname']?:$r['customer_name']) ?></b><br><small><?php echo e($r['cphone']??'') ?></small></td>
<td><?php echo e($r['unit_number']??'—') ?></td>
<td><?php echo e($r['pickup_location']??'—') ?><br><small><?php echo e($r['gate_pass_notes']??'') ?></small></td>
<td class="text-center"><b><?php echo e($r['gate_pin']??'—') ?></b></td>
<td><?php echo e($r['service_type']) ?><?php if($r['is_express']): ?><span class="badge badge-danger">EXPRESS</span><?php endif; ?></td>
<td><?php echo e($r['driver_name']??'Unassigned') ?></td>
<td class="text-right">KES <?php echo number_format($r['total_amount'],2) ?></td>
<td class="text-center" style="width:80px;">☐</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endforeach; ?>
<?php if(empty($orders)): ?><div class="alert alert-info">No pickups for this filter. All caught up!</div><?php endif; ?>
<div class="mt-3"><small>Driver signature: ___________________ Date: __________ Security stamp: __________</small></div>
</div>
</div>
</div>
</div>
</div>
