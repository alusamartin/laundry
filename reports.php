<?php
include 'db_connect.php';
$d1 = (isset($_GET['d1']) ? date("Y-m-d", strtotime($_GET['d1'])) : date("Y-m-d"));
$d2 = (isset($_GET['d2']) ? date("Y-m-d", strtotime($_GET['d2'])) : date("Y-m-d"));
$estate_filter = isset($_GET['estate_id']) ? $_GET['estate_id'] : '';
$data = $d1 == $d2 ? $d1 : $d1 . ' - ' . $d2;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-body">
				<form id="report-filter" class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">Date From</label>
							<input type="date" class="form-control" name="d1" value="<?php echo $d1 ?>">
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label">Date To</label>
							<input type="date" class="form-control" name="d2" value="<?php echo $d2 ?>">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label">Estate</label>
							<select name="estate_id" class="custom-select browser-default">
								<option value="">All Estates</option>
								<?php
								$estates = db_query("SELECT * FROM estates ORDER BY name ASC");
								foreach($estates as $e):
								?>
								<option value="<?php echo $e['id'] ?>" <?php echo $estate_filter == $e['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($e['name']) ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label">Status</label>
							<select name="status" class="custom-select browser-default">
								<option value="">All Status</option>
								<option value="0" <?php echo $status_filter === '0' ? 'selected' : '' ?>>Pending</option>
								<option value="1" <?php echo $status_filter == '1' ? 'selected' : '' ?>>Received</option>
								<option value="2" <?php echo $status_filter == '2' ? 'selected' : '' ?>>Washing</option>
								<option value="3" <?php echo $status_filter == '3' ? 'selected' : '' ?>>Ironing</option>
								<option value="4" <?php echo $status_filter == '4' ? 'selected' : '' ?>>Ready</option>
								<option value="5" <?php echo $status_filter == '5' ? 'selected' : '' ?>>Out for Delivery</option>
								<option value="6" <?php echo $status_filter == '6' ? 'selected' : '' ?>>Delivered</option>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<label class="control-label">&nbsp;</label>
							<button class="btn-block btn-primary btn-sm" type="submit"><i class="fa fa-filter"></i> Filter</button>
						</div>
					</div>
				</form>
				<hr>

				<!-- Summary (SECURE) -->
				<?php
				$where = "WHERE date(l.date_created) BETWEEN ? AND ?";
				$params = [$d1, $d2];
				if($estate_filter) { $where .= " AND l.estate_id = ?"; $params[] = intval($estate_filter); }
				if($status_filter !== '' && $status_filter !== null) { $where .= " AND l.status = ?"; $params[] = intval($status_filter); }
				$stmt = db()->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(l.total_amount),0) as total_revenue, COALESCE(SUM(CASE WHEN l.pay_status=1 THEN l.total_amount ELSE 0 END),0) as paid_revenue FROM laundry_list l $where");
				$stmt->execute($params);
				$summary = $stmt->fetch(PDO::FETCH_ASSOC);
				$expense_total = db_query("SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE date_incurred BETWEEN ? AND ?", [$d1,$d2])[0]['total'] ?? 0;
				// Commission for filtered estate
				$commission_total = 0;
				if($estate_filter){
					$row = db_query("SELECT COALESCE(SUM(commission_amount),0) as c FROM estate_commissions WHERE estate_id=? AND date(date_created) BETWEEN ? AND ?", [intval($estate_filter),$d1,$d2]);
					$commission_total = $row[0]['c'] ?? 0;
				}
				?>
				<div class="row mb-3">
					<div class="col-md-2">
						<div class="alert alert-info">
							<b>Total Orders</b>
							<h4><?php echo $summary['total_orders'] ?></h4>
						</div>
					</div>
					<div class="col-md-2">
						<div class="alert alert-success">
							<b>Total Revenue</b>
							<h4>KES <?php echo number_format($summary['total_revenue'], 2) ?></h4>
						</div>
					</div>
					<div class="col-md-2">
						<div class="alert alert-warning">
							<b>Collected</b>
							<h4>KES <?php echo number_format($summary['paid_revenue'], 2) ?></h4>
						</div>
					</div>
					<div class="col-md-2">
						<div class="alert alert-danger">
							<b>Expenses</b>
							<h4>KES <?php echo number_format($expense_total, 2) ?></h4>
						</div>
					</div>
					<div class="col-md-2">
						<div class="alert alert-secondary">
							<b>Profit</b>
							<h4>KES <?php echo number_format($summary['paid_revenue'] - $expense_total, 2) ?></h4>
						</div>
					</div>
					<div class="col-md-2">
						<div class="alert alert-dark">
							<b>Estate Comm.</b>
							<h4>KES <?php echo number_format($commission_total, 2) ?></h4>
						</div>
					</div>
				</div>

				<button class="btn btn-sm btn-primary mb-2" id="print"><i class="fa fa-print"></i> Print Report</button>

				<div id="print-data">
					<div style="width:100%">
						<p class="text-center"><large><b>Laundry Management Report</b></large></p>
						<p class="text-center"><large><b><?php echo $data ?></b></large></p>
					</div>
					<table class='table table-bordered'>
						<thead>
							<tr>
								<th>Date</th>
								<th>Customer</th>
								<th>Estate</th>
								<th>Items</th>
								<th>Total Amount</th>
								<th>Payment</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$total = 0;
							$stmt2 = db()->prepare("
								SELECT l.*, c.name as cname, e.name as estate_name,
									(SELECT COUNT(*) FROM laundry_items WHERE laundry_id = l.id) as item_count
								FROM laundry_list l
								LEFT JOIN customers c ON l.customer_id = c.id
								LEFT JOIN estates e ON l.estate_id = e.id
								$where
								ORDER BY l.date_created DESC
							");
							$stmt2->execute($params);
							$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							$slabels = ['Pending','Received','Washing','Ironing','Ready','Out for Delivery','Delivered','Cancelled'];
							foreach($rows as $row):
								$total += $row['total_amount'];
								$cname = $row['cname'] ?: $row['customer_name'];
							?>
							<tr>
								<td><?php echo date("M d, Y", strtotime($row['date_created'])) ?></td>
								<td><?php echo htmlspecialchars($cname) ?></td>
								<td><?php echo $row['estate_name'] ? htmlspecialchars($row['estate_name']) : '—' ?></td>
								<td class="text-center"><?php echo $row['item_count'] ?></td>
								<td class='text-right'>KES <?php echo number_format($row['total_amount'], 2) ?></td>
								<td class="text-center"><?php echo $row['pay_status'] ? 'Paid' : '—' ?></td>
								<td class="text-center"><?php echo $slabels[$row['status']] ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<th class="text-right" colspan="4">Totals</th>
								<th class="text-right">KES <?php echo number_format($total, 2) ?></th>
								<th colspan="2"></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	#print-data p { display: none; }
</style>
<noscript>
	<style>
		#div { width:100%; }
		table { border-collapse: collapse; width:100% !important; }
		tr,th,td { border:1px solid black; }
		.text-right { text-align: right; }
		p { margin:unset; }
		#div p { display: block; }
		p.text-center { text-align: -webkit-center; }
	</style>
</noscript>
<script>
	$('#report-filter').submit(function(e){
		e.preventDefault()
		var params = $(this).serialize()
		location.replace('index.php?page=reports&' + params)
	})
	$('#print').click(function(){
		var newWin = window.open('', '_blank', 'height=500,width=600')
		var _html = $('#print-data').clone()
		var ns = $('noscript').clone()
		newWin.document.write('<html><head><title>Report</title>')
		newWin.document.write('<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">')
		newWin.document.write('</head><body>')
		newWin.document.write(ns.html())
		newWin.document.write(_html.html())
		newWin.document.write('</body></html>')
		newWin.document.close()
		setTimeout(function(){ newWin.print() }, 500)
	})
</script>
