<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>CUSTOMERS</b>
					<button class="btn btn-primary btn-sm float-right" id="new_customer"><i class="fa fa-plus"></i> New Customer</button>
				</large>
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="customer-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Name</th>
							<th class="text-center">Phone</th>
							<th class="text-center">Type</th>
							<th class="text-center">Estate / Location</th>
							<th class="text-center">Orders</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$customers = db_query("
							SELECT c.*, e.name as estate_name, b.name as block_name, u.unit_number,
								(SELECT COUNT(*) FROM laundry_list WHERE customer_id = c.id) as order_count
							FROM customers c
							LEFT JOIN estates e ON c.estate_id = e.id
							LEFT JOIN estate_blocks b ON c.block_id = b.id
							LEFT JOIN estate_units u ON c.unit_id = u.id
							ORDER BY c.name ASC
						");
						foreach($customers as $row):
							$type_labels = [1 => 'Individual Tenant', 2 => 'Estate Mgmt', 3 => 'Walk-in'];
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><b><?php echo htmlspecialchars($row['name']) ?></b></td>
							<td><a href="tel:<?php echo htmlspecialchars($row['phone']) ?>"><?php echo htmlspecialchars($row['phone']) ?></a></td>
							<td class="text-center"><span class="badge badge-<?php echo $row['customer_type'] == 1 ? 'info' : ($row['customer_type'] == 2 ? 'warning' : 'secondary') ?>"><?php echo $type_labels[$row['customer_type']] ?></span></td>
							<td>
								<?php if($row['estate_name']): ?>
									<small><?php echo htmlspecialchars($row['estate_name']) ?></small>
									<?php if($row['block_name']): ?> / <small><?php echo htmlspecialchars($row['block_name']) ?></small><?php endif; ?>
									<?php if($row['unit_number']): ?> / <small><?php echo htmlspecialchars($row['unit_number']) ?></small><?php endif; ?>
								<?php else: ?>
									<small class="text-muted">Walk-in</small>
								<?php endif; ?>
							</td>
							<td class="text-center"><span class="badge badge-primary"><?php echo $row['order_count'] ?></span></td>
							<td class="text-center">
								<?php if($row['is_active']): ?>
									<span class="badge badge-success">Active</span>
								<?php else: ?>
									<span class="badge badge-secondary">Inactive</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<button class="btn btn-outline-primary btn-sm edit_customer" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-outline-danger btn-sm delete_customer" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<style>
	td { vertical-align: middle !important; }
</style>

<script>
	$('#customer-list').dataTable()

	$('#new_customer').click(function(){
		uni_modal('New Customer', 'manage_customer.php', 'mid-large')
	})
	$('.edit_customer').click(function(){
		uni_modal('Edit Customer', 'manage_customer.php?id=' + $(this).attr('data-id'), 'mid-large')
	})
	$('.delete_customer').click(function(){
		_conf("Are you sure to delete this customer?", "delete_customer", [$(this).attr('data-id')])
	})
	function delete_customer($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_customer',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Customer deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
