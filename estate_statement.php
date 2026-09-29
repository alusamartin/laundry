<?php include 'db_connect.php';
$estate_id = intval($_GET['estate_id'] ?? 0);
$d1 = $_GET['d1'] ?? date('Y-m-01');
$d2 = $_GET['d2'] ?? date('Y-m-t');
if(!$estate_id) die("Select estate: <a href='index.php?page=estates'>Estates</a>");
$estate = db_fetch("SELECT * FROM estates WHERE id=?", [$estate_id]);
$orders = db_query("SELECT l.*, c.name as cname FROM laundry_list l LEFT JOIN customers c ON l.customer_id=c.id WHERE l.estate_id=? AND date(l.date_created) BETWEEN ? AND ? ORDER BY l.date_created DESC", [$estate_id,$d1,$d2]);
$comm = db_query("SELECT * FROM estate_commissions WHERE estate_id=? AND date(date_created) BETWEEN ? AND ? ORDER BY date_created DESC", [$estate_id,$d1,$d2]);
$totals = db_fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as gross, COALESCE(SUM(CASE WHEN pay_status=1 THEN total_amount ELSE 0 END),0) as collected FROM laundry_list WHERE estate_id=? AND date(date_created) BETWEEN ? AND ?", [$estate_id,$d1,$d2]);
$comm_total = array_sum(array_column($comm, 'commission_amount'));
$profit = ($totals['collected']??0) - $comm_total;
?>
<div class="container-fluid">
<div class="col-lg-12">
<div class="card">
<div class="card-header"><b>ESTATE STATEMENT — <?php echo e($estate['name']) ?></b> <span class="badge badge-info"><?php echo $d1 ?> to <?php echo $d2 ?></span>
<a class="btn btn-sm btn-dark float-right ml-1" onclick="window.print()">Print / PDF</a>
<form class="form-inline float-right" method="GET" action="index.php"><input type="hidden" name="page" value="estate_statement"><input type="hidden" name="estate_id" value="<?php echo $estate_id ?>"><input type="date" name="d1" value="<?php echo $d1 ?>" class="form-control form-control-sm mr-1"><input type="date" name="d2" value="<?php echo $d2 ?>" class="form-control form-control-sm mr-1"><button class="btn btn-primary btn-sm">Go</button></form>
</div>
<div class="card-body" id="statement-print">
<div class="row mb-3">
<div class="col-md-6"><b><?php echo e($estate['name']) ?></b><br><?php echo e($estate['location']) ?> • <?php echo e($estate['city']) ?><br><?php echo e($estate['contact_person']) ?> • <?php echo e($estate['phone']) ?><br>Contract Rate: <b><?php echo $estate['contract_rate'] ?>%</b> • Gate: <?php echo e($estate['gate_access_notes']??'—') ?></div>
<div class="col-md-6 text-right"><h5>Statement</h5><small>Generated <?php echo date('d M Y H:i') ?> Africa/Nairobi</small></div>
</div>
<div class="row mb-3">
<div class="col-md-3"><div class="alert alert-primary">Orders<p class="h4"><?php echo $totals['cnt'] ?></p></div></div>
<div class="col-md-3"><div class="alert alert-success">Gross<p class="h4">KES <?php echo number_format($totals['gross'],2) ?></p></div></div>
<div class="col-md-3"><div class="alert alert-warning">Collected<p class="h4">KES <?php echo number_format($totals['collected'],2) ?></p></div></div>
<div class="col-md-3"><div class="alert alert-dark">Commission (<?php echo $estate['contract_rate'] ?>%)<p class="h4">KES <?php echo number_format($comm_total,2) ?></p></div></div>
</div>
<h6>Orders</h6>
<table class="table table-sm table-bordered">
<thead><tr><th>Date</th><th>Customer</th><th>Service</th><th class="text-right">Amount</th><th>Pay</th><th>Status</th></tr></thead>
<tbody>
<?php $labels=['Pending','Received','Washing','Ironing','Ready','Out for Delivery','Delivered','Cancelled']; foreach($orders as $o): ?>
<tr><td><?php echo date('d M Y', strtotime($o['date_created'])) ?></td><td><?php echo e($o['cname']?:$o['customer_name']) ?></td><td><?php echo e($o['service_type']) ?><?php if($o['is_express']) echo ' <span class="badge badge-danger">EXPRESS</span>'; ?></td><td class="text-right">KES <?php echo number_format($o['total_amount'],2) ?></td><td><?php echo $o['pay_status']?'Paid':'Unpaid' ?></td><td><?php echo $labels[$o['status']] ?></td></tr>
<?php endforeach; ?>
</tbody>
<tfoot><tr><th colspan="3" class="text-right">Totals</th><th class="text-right">KES <?php echo number_format($totals['gross'],2) ?></th><th colspan="2"></th></tr></tfoot>
</table>
<h6>Commission Ledger</h6>
<table class="table table-sm table-bordered">
<thead><tr><th>Date</th><th>Order #</th><th class="text-right">Gross</th><th class="text-right">Rate</th><th class="text-right">Commission</th><th>Status</th></tr></thead>
<tbody>
<?php foreach($comm as $c): ?>
<tr><td><?php echo date('d M Y', strtotime($c['date_created'])) ?></td><td>#<?php echo str_pad($c['order_id'],6,'0',STR_PAD_LEFT) ?></td><td class="text-right">KES <?php echo number_format($c['gross_amount'],2) ?></td><td class="text-right"><?php echo $c['commission_rate'] ?>%</td><td class="text-right">KES <?php echo number_format($c['commission_amount'],2) ?></td><td><?php echo $c['payout_status'] ?></td></tr>
<?php endforeach; ?>
<?php if(empty($comm)): ?><tr><td colspan="6" class="text-center text-muted">No commission entries yet</td></tr><?php endif; ?>
</tbody>
<tfoot><tr><th colspan="4" class="text-right">Total Commission</th><th class="text-right">KES <?php echo number_format($comm_total,2) ?></th><th></th></tr>
<tr><th colspan="4" class="text-right">Net Payable to Laundry</th><th class="text-right">KES <?php echo number_format($profit,2) ?></th><th></th></tr></tfoot>
</table>
<p><small>Notes: Commission deducted per order at <?php echo $estate['contract_rate'] ?>%. Payouts marked paid after transfer. For queries contact <?php echo e(db_query("SELECT contact FROM system_settings LIMIT 1")[0]['contact']??'') ?></small></p>
</div>
</div>
</div>
</div>
</div>
