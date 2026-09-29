<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>EXPENSE TRACKING</b>
					<button class="btn btn-primary btn-sm float-right" id="new_expense"><i class="fa fa-plus"></i> New Expense</button>
				</large>
			</div>
			<div class="card-body">
				<?php
				// Summary cards
				$month_start = date('Y-m-01');
				$month_end = date('Y-m-t');
				$month_total = db_fetch("SELECT SUM(amount) as total FROM expenses WHERE date_incurred BETWEEN ? AND ?", [$month_start, $month_end]);
				$by_type = db_query("SELECT expense_type, SUM(amount) as total FROM expenses WHERE date_incurred BETWEEN ? AND ? GROUP BY expense_type ORDER BY total DESC", [$month_start, $month_end]);
				?>
				<div class="row mb-3">
					<div class="col-md-3">
						<div class="alert alert-info">
							<b>This Month Total</b>
							<h4>KES <?php echo number_format($month_total['total'] ?? 0, 2) ?></h4>
						</div>
					</div>
					<?php foreach($by_type as $bt): ?>
					<div class="col-md-3">
						<div class="alert alert-secondary">
							<b><?php echo ucfirst($bt['expense_type']) ?></b>
							<h5>KES <?php echo number_format($bt['total'], 2) ?></h5>
						</div>
					</div>
					<?php endforeach; ?>
				</div>

				<table class="table table-bordered" id="expense-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Date</th>
							<th class="text-center">Type</th>
							<th class="text-center">Description</th>
							<th class="text-center">Amount</th>
							<th class="text-center">Receipt</th>
							<th class="text-center">Entered By</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$expenses = db_query("
							SELECT e.*, u.name as user_name
							FROM expenses e
							LEFT JOIN users u ON e.entered_by = u.id
							ORDER BY e.date_incurred DESC, e.date_created DESC
						");
						foreach($expenses as $row):
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><?php echo date('d M Y', strtotime($row['date_incurred'])) ?></td>
							<td><span class="badge badge-primary"><?php echo ucfirst($row['expense_type']) ?></span></td>
							<td><?php echo htmlspecialchars($row['description']) ?></td>
							<td class="text-right"><b>KES <?php echo number_format($row['amount'], 2) ?></b></td>
							<td class="text-center"><?php echo $row['receipt_no'] ? htmlspecialchars($row['receipt_no']) : '—' ?></td>
							<td><?php echo $row['user_name'] ? htmlspecialchars($row['user_name']) : '—' ?></td>
							<td class="text-center">
								<button class="btn btn-outline-primary btn-sm edit_expense" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-outline-danger btn-sm delete_expense" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="4" class="text-right">Total</th>
							<th class="text-right" id="expense_total"></th>
							<th colspan="3"></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	$('#expense-list').dataTable()
	// Calculate total
	var total = 0
	$('#expense-list tbody tr').each(function(){
		var amt = parseFloat($(this).find('td:eq(4) b').text().replace(/[^0-9.]/g, ''))
		if(!isNaN(amt)) total += amt
	})
	$('#expense_total').text('KES ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}))

	$('#new_expense').click(function(){
		uni_modal('New Expense', 'manage_expense.php', 'mid-large')
	})
	$('.edit_expense').click(function(){
		uni_modal('Edit Expense', 'manage_expense.php?id=' + $(this).attr('data-id'), 'mid-large')
	})
	$('.delete_expense').click(function(){
		_conf("Are you sure to delete this expense?", "delete_expense", [$(this).attr('data-id')])
	})
	function delete_expense($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_expense',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Expense deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
