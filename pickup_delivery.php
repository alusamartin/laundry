<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>PICKUP & DELIVERY SCHEDULE</b>
					<button class="btn btn-primary btn-sm float-right" id="new_schedule"><i class="fa fa-plus"></i> New Schedule</button>
				</large>
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="schedule-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Date</th>
							<th class="text-center">Type</th>
							<th class="text-center">Customer</th>
							<th class="text-center">Estate</th>
							<th class="text-center">Time Slot</th>
							<th class="text-center">Driver</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$schedules = db_query("
							SELECT ps.*, c.name as customer_name, e.name as estate_name, d.name as driver_name,
								o.customer_name as order_customer
							FROM pickup_schedule ps
							LEFT JOIN customers c ON ps.customer_id = c.id
							LEFT JOIN estates e ON ps.estate_id = e.id
							LEFT JOIN drivers d ON ps.driver_id = d.id
							LEFT JOIN laundry_list o ON ps.order_id = o.id
							ORDER BY ps.scheduled_date DESC, ps.time_slot ASC
						");
						foreach($schedules as $row):
							$customer = $row['customer_name'] ?: $row['order_customer'] ?: '—';
							$status_labels = ['Pending', 'Assigned', 'Completed', 'Cancelled'];
							$status_classes = ['badge-secondary', 'badge-primary', 'badge-success', 'badge-danger'];
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><?php echo date('d M Y', strtotime($row['scheduled_date'])) ?></td>
							<td class="text-center">
								<span class="badge badge-<?php echo $row['schedule_type'] == 'pickup' ? 'info' : 'warning' ?>">
									<?php echo ucfirst($row['schedule_type']) ?>
								</span>
							</td>
							<td><?php echo htmlspecialchars($customer) ?></td>
							<td><?php echo $row['estate_name'] ? htmlspecialchars($row['estate_name']) : '—' ?></td>
							<td class="text-center"><?php echo ucfirst($row['time_slot']) ?></td>
							<td><?php echo $row['driver_name'] ? htmlspecialchars($row['driver_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
							<td class="text-center"><span class="badge <?php echo $status_classes[$row['status']] ?>"><?php echo $status_labels[$row['status']] ?></span></td>
							<td class="text-center">
								<button class="btn btn-outline-primary btn-sm edit_schedule" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-outline-danger btn-sm delete_schedule" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
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
	$('#schedule-list').dataTable()
	$('#new_schedule').click(function(){
		uni_modal('New Schedule', 'manage_pickup.php', 'mid-large')
	})
	$('.edit_schedule').click(function(){
		uni_modal('Edit Schedule', 'manage_pickup.php?id=' + $(this).attr('data-id'), 'mid-large')
	})
	$('.delete_schedule').click(function(){
		_conf("Are you sure to delete this schedule?", "delete_schedule", [$(this).attr('data-id')])
	})
	function delete_schedule($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_schedule',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Schedule deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
