<?php include 'db_connect.php' ?>
<style>
	.card-counter { box-shadow: 2px 2px 10px #DADADA; margin: 5px; padding: 20px 10px; background-color: #fff; height: 100px; border-radius: 5px; transition: .3s linear all; }
	.card-counter:hover { box-shadow: 4px 4px 20px #DADADA; transition: .3s linear all; }
	.card-counter .count-numbers { position: absolute; right: 35px; top: 20px; font-size: 32px; display: block; }
	.card-counter .count-name { position: absolute; right: 35px; top: 65px; font-style: italic; text-transform: capitalize; opacity: 0.5; display: block; font-size: 14px; }
</style>

<div class="containe-fluid">
	<div class="row mt-3 ml-3 mr-3">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-body">
					<h4>Welcome back, <?php echo $_SESSION['login_name'] ?>!</h4>
				</div>
			</div>
		</div>
	</div>

	<!-- Stats Cards -->
	<div class="row mt-3 ml-3 mr-3">
		<?php
		$today = date('Y-m-d');
		$this_month_start = date('Y-m-01');
		$this_month_end = date('Y-m-t');
		$today_revenue = db_fetch("SELECT COALESCE(SUM(total_amount),0) as total FROM laundry_list WHERE pay_status=1 AND date(date_created)=?", [$today])['total'];
		$month_revenue = db_fetch("SELECT COALESCE(SUM(total_amount),0) as total FROM laundry_list WHERE pay_status=1 AND date(date_created) BETWEEN ? AND ?", [$this_month_start, $this_month_end])['total'];
		$today_orders = db_fetch("SELECT COUNT(*) as cnt FROM laundry_list WHERE date(date_created)=?", [$today])['cnt'];
		$pending_orders = db_fetch("SELECT COUNT(*) as cnt FROM laundry_list WHERE status=0")['cnt'];
		$total_customers = db_fetch("SELECT COUNT(*) as cnt FROM customers")['cnt'];
		$total_estates = db_fetch("SELECT COUNT(*) as cnt FROM estates")['cnt'];
		$month_expenses = db_fetch("SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE date_incurred BETWEEN ? AND ?", [$this_month_start, $this_month_end])['total'];
		?>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #28a745;">
				<div class="count-numbers">KES <?php echo number_format($today_revenue, 0) ?></div>
				<div class="count-name">Today's Revenue</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #007bff;">
				<div class="count-numbers">KES <?php echo number_format($month_revenue, 0) ?></div>
				<div class="count-name">This Month Revenue</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #ffc107;">
				<div class="count-numbers"><?php echo $today_orders ?></div>
				<div class="count-name">Orders Today</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #dc3545;">
				<div class="count-numbers"><?php echo $pending_orders ?></div>
				<div class="count-name">Pending Orders</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #17a2b8;">
				<div class="count-numbers"><?php echo $total_customers ?></div>
				<div class="count-name">Customers</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #6f42c1;">
				<div class="count-numbers"><?php echo $total_estates ?></div>
				<div class="count-name">Estates</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #fd7e14;">
				<div class="count-numbers">KES <?php echo number_format($month_expenses, 0) ?></div>
				<div class="count-name">This Month Expenses</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card-counter" style="border-left: 5px solid #20c997;">
				<div class="count-numbers">KES <?php echo number_format($month_revenue - $month_expenses, 0) ?></div>
				<div class="count-name">Net Profit (Month)</div>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="row mt-3 ml-3 mr-3">
		<div class="col-lg-6">
			<div class="card">
				<div class="card-header"><b>Orders by Estate (This Month)</b></div>
				<div class="card-body">
					<table class="table table-sm table-hover">
						<thead><tr><th>Estate</th><th class="text-right">Orders</th><th class="text-right">Revenue</th></tr></thead>
						<tbody>
							<?php
							$estate_stats = db_query("
								SELECT e.name, COUNT(l.id) as order_count, COALESCE(SUM(l.total_amount),0) as revenue
								FROM estates e
								LEFT JOIN laundry_list l ON l.estate_id=e.id AND date(l.date_created) BETWEEN ? AND ?
								GROUP BY e.id, e.name
								ORDER BY revenue DESC
							", [$this_month_start, $this_month_end]);
							foreach($estate_stats as $es):
							?>
							<tr>
								<td><?php echo htmlspecialchars($es['name']) ?></td>
								<td class="text-right"><?php echo $es['order_count'] ?></td>
								<td class="text-right">KES <?php echo number_format($es['revenue'], 2) ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="card">
				<div class="card-header"><b>Recent Orders</b></div>
				<div class="card-body">
					<table class="table table-sm table-hover">
						<thead><tr><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
						<tbody>
							<?php
							$recent = db_query("
								SELECT l.id, l.customer_name, l.total_amount, l.status, l.date_created, c.name as cname
								FROM laundry_list l
								LEFT JOIN customers c ON l.customer_id = c.id
								ORDER BY l.id DESC LIMIT 10
							");
							$slabels = ['Pending','Received','Washing','Ironing','Ready','Out for Delivery','Delivered','Cancelled'];
							$sclasses = ['badge-secondary','badge-info','badge-primary','badge-warning','badge-success','badge-dark','badge-success','badge-danger'];
							foreach($recent as $r):
								$name = $r['cname'] ?: $r['customer_name'];
							?>
							<tr>
								<td><?php echo htmlspecialchars($name) ?></td>
								<td>KES <?php echo number_format($r['total_amount'], 2) ?></td>
								<td><span class="badge <?php echo $sclasses[$r['status']] ?>"><?php echo $slabels[$r['status']] ?></span></td>
								<td><?php echo date('d M H:i', strtotime($r['date_created'])) ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- Top Estates -->
	<div class="row mt-3 ml-3 mr-3">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header"><b>Top Estates by Volume</b></div>
				<div class="card-body">
					<?php
					$top_estates = db_query("
						SELECT e.name, COUNT(l.id) as order_count, COALESCE(SUM(l.total_amount),0) as revenue
						FROM estates e
						LEFT JOIN laundry_list l ON l.estate_id = e.id
						GROUP BY e.id, e.name
						ORDER BY order_count DESC
						LIMIT 5
					");
					$rank = 1;
					foreach($top_estates as $te):
					?>
					<div class="mb-2">
						<b>#<?php echo $rank++ ?>. <?php echo htmlspecialchars($te['name']) ?></b>
						<div class="progress" style="height: 25px;">
							<div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, ($te['order_count'] / max(1, $te['order_count'])) * 100) ?>%;">
								<?php echo $te['order_count'] ?> orders — KES <?php echo number_format($te['revenue'], 0) ?>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>
