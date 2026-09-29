<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>ORDERS (LAUNDRY LIST)</b>
					<button class="col-sm-3 float-right btn btn-primary btn-sm" type="button" id="new_laundry"><i class="fa fa-plus"></i> New Order</button>
				</large>
			</div>
			<div class="card-body">
				<div class="row mb-3">
					<div class="col-md-3">
						<select id="filter_estate" class="custom-select browser-default">
							<option value="">All Estates</option>
							<?php
							$estates = db_query("SELECT * FROM estates ORDER BY name ASC");
							foreach($estates as $e):
							?>
							<option value="<?php echo $e['id'] ?>"><?php echo htmlspecialchars($e['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<select id="filter_status" class="custom-select browser-default">
							<option value="">All Status</option>
							<option value="0">Pending</option>
							<option value="1">Received</option>
							<option value="2">Washing</option>
							<option value="3">Ironing</option>
							<option value="4">Ready</option>
							<option value="5">Out for Delivery</option>
							<option value="6">Delivered</option>
							<option value="7">Cancelled</option>
						</select>
					</div>
					<div class="col-md-2">
						<button class="btn btn-sm btn-default" id="clear_filter"><i class="fa fa-times"></i> Clear</button>
					</div>
				</div>
				<table class="table table-bordered" id="laundry-list">
					<thead>
						<tr>
							<th class="text-center">Date</th>
							<th class="text-center">Queue</th>
							<th class="text-center">Customer</th>
							<th class="text-center">Estate</th>
							<th class="text-center">Items</th>
							<th class="text-center">Amount</th>
							<th class="text-center">Payment</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$list = db_query("
							SELECT l.*, c.name as cname, c.phone as cphone, e.name as estate_name,
								(SELECT COUNT(*) FROM laundry_items WHERE laundry_id = l.id) as item_count
							FROM laundry_list l
							LEFT JOIN customers c ON l.customer_id = c.id
							LEFT JOIN estates e ON l.estate_id = e.id
							ORDER BY l.status ASC, l.id DESC
						");
						$status_labels = ['Pending', 'Received', 'Washing', 'Ironing', 'Ready', 'Out for Delivery', 'Delivered', 'Cancelled'];
						$status_classes = ['badge-secondary', 'badge-info', 'badge-primary', 'badge-warning', 'badge-success', 'badge-dark', 'badge-success', 'badge-danger'];
						foreach($list as $row):
							$customer_display = $row['cname'] ?: $row['customer_name'];
						?>
						<tr data-estate="<?php echo $row['estate_id'] ?>" data-status="<?php echo $row['status'] ?>">
							<td class=""><?php echo date("M d, Y", strtotime($row['date_created'])) ?></td>
							<td class="text-right"><?php echo $row['queue'] ?></td>
							<td>
								<b><?php echo htmlspecialchars($customer_display) ?></b>
								<?php if($row['cphone']): ?><br><small class="text-muted"><?php echo htmlspecialchars($row['cphone']) ?></small><?php endif; ?>
							</td>
							<td><?php echo $row['estate_name'] ? htmlspecialchars($row['estate_name']) : '<span class="text-muted">—</span>' ?></td>
							<td class="text-center"><?php echo $row['item_count'] ?> items</td>
							<td class="text-right"><b>KES <?php echo number_format($row['total_amount'], 2) ?></b></td>
							<td class="text-center">
								<?php if($row['pay_status']): ?>
									<span class="badge badge-success">Paid</span>
									<?php if($row['payment_method']): ?>
									<small><?php echo strtoupper($row['payment_method']) ?></small>
									<?php endif; ?>
								<?php else: ?>
									<span class="badge badge-danger">Unpaid</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<span class="badge <?php echo $status_classes[$row['status']] ?>">
									<?php echo $status_labels[$row['status']] ?>
								</span>
								<?php if($row['is_express']): ?>
									<br><span class="badge badge-danger">EXPRESS</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<div class="btn-group">
									<button type="button" class="btn btn-outline-primary btn-sm edit_laundry" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
									<button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"></button>
									<div class="dropdown-menu">
										<a class="dropdown-item quick_status" href="#" data-id="<?php echo $row['id'] ?>" data-status="1">Mark Received</a>
										<a class="dropdown-item quick_status" href="#" data-id="<?php echo $row['id'] ?>" data-status="4">Mark Ready</a>
										<a class="dropdown-item quick_status" href="#" data-id="<?php echo $row['id'] ?>" data-status="5">Out for Delivery</a>
										<a class="dropdown-item quick_status" href="#" data-id="<?php echo $row['id'] ?>" data-status="6">Delivered</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item" href="#" onclick="stkPush(<?php echo $row['id'] ?>,'<?php echo htmlspecialchars($row['cphone']) ?>',<?php echo $row['total_amount'] ?>);return false;">M-Pesa STK Push</a>
									</div>
								</div>
								<button type="button" class="btn btn-outline-danger btn-sm delete_laundry" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	$('#laundry-list').dataTable()

	// Filters
	$('#filter_estate, #filter_status').change(function(){
		var estate = $('#filter_estate').val()
		var status = $('#filter_status').val()
		$('#laundry-list tbody tr').each(function(){
			var show = true
			if(estate && $(this).attr('data-estate') != estate) show = false
			if(status && $(this).attr('data-status') != status) show = false
			$(this).toggle(show)
		})
	})
	$('#clear_filter').click(function(){
		$('#filter_estate').val('')
		$('#filter_status').val('')
		$('#laundry-list tbody tr').show()
	})

	$('#new_laundry').click(function(){
		uni_modal('New Order', 'manage_laundry.php', 'large')
	})
	$('.edit_laundry').click(function(){
		uni_modal('Edit Order', 'manage_laundry.php?id=' + $(this).attr('data-id'), 'large')
	})
	$('.delete_laundry').click(function(){
		_conf("Are you sure to remove this order?", "delete_laundry", [$(this).attr('data-id')])
	})
	function delete_laundry($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_laundry',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Order deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
	$('.quick_status').click(function(e){
		e.preventDefault(); var id=$(this).data('id'), st=$(this).data('status');
		start_load(); $.post('ajax.php?action=update_order_status', {id:id, status:st}, function(r){ if(r==1){ alert_toast('Status updated','success'); setTimeout(()=>location.reload(),1000);} else alert_toast('Failed','danger'); end_load(); })
	})
	function stkPush(id, phone, amount){
		if(!phone){ alert_toast('No phone on file','warning'); return; }
		if(!confirm('Send M-Pesa STK Push KES '+amount+' to '+phone+'?')) return;
		start_load(); $.post('ajax.php?action=mpesa_stk', {order_id:id, phone:phone, amount:amount}, function(r){
			try{ var j=JSON.parse(r); if(j.success){ alert_toast('STK Sent to '+phone,'success'); } else alert_toast(j.error||'STK failed','danger'); } catch(e){ alert_toast(r,'info'); } end_load();
		})
	}
</script>
