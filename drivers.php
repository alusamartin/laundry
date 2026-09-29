<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>DRIVERS / RIDERS</b>
					<button class="btn btn-primary btn-sm float-right" id="new_driver"><i class="fa fa-plus"></i> New Driver</button>
				</large>
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="driver-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Name</th>
							<th class="text-center">Phone</th>
							<th class="text-center">National ID</th>
							<th class="text-center">Vehicle</th>
							<th class="text-center">Reg No.</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$drivers = db_query("SELECT * FROM drivers ORDER BY name ASC");
						foreach($drivers as $row):
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><b><?php echo htmlspecialchars($row['name']) ?></b></td>
							<td><a href="tel:<?php echo htmlspecialchars($row['phone']) ?>"><?php echo htmlspecialchars($row['phone']) ?></a></td>
							<td><?php echo htmlspecialchars($row['national_id'] ?? '—') ?></td>
							<td><?php echo htmlspecialchars($row['vehicle_type'] ?? '—') ?></td>
							<td><?php echo htmlspecialchars($row['vehicle_reg'] ?? '—') ?></td>
							<td class="text-center">
								<?php if($row['is_active']): ?>
									<span class="badge badge-success">Active</span>
								<?php else: ?>
									<span class="badge badge-secondary">Inactive</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<button class="btn btn-outline-primary btn-sm edit_driver" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-outline-danger btn-sm delete_driver" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
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
	$('#driver-list').dataTable()
	$('#new_driver').click(function(){
		uni_modal('New Driver', 'manage_driver.php', 'mid-large')
	})
	$('.edit_driver').click(function(){
		uni_modal('Edit Driver', 'manage_driver.php?id=' + $(this).attr('data-id'), 'mid-large')
	})
	$('.delete_driver').click(function(){
		_conf("Are you sure to delete this driver?", "delete_driver", [$(this).attr('data-id')])
	})
	function delete_driver($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_driver',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Driver deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
