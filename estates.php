<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<large class="card-title">
					<b>ESTATES MANAGEMENT</b>
					<button class="btn btn-primary btn-sm float-right" id="new_estate"><i class="fa fa-plus"></i> New Estate</button>
				</large>
			</div>
			<div class="card-body">
				<table class="table table-bordered" id="estate-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Estate Name</th>
							<th class="text-center">Location / City</th>
							<th class="text-center">Contact</th>
							<th class="text-center">Blocks</th>
							<th class="text-center">Contract Rate</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$estates = db_query("SELECT e.*, (SELECT COUNT(*) FROM estate_blocks WHERE estate_id = e.id) as block_count FROM estates e ORDER BY e.name ASC");
						foreach($estates as $row):
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><b><?php echo htmlspecialchars($row['name']) ?></b></td>
							<td><?php echo htmlspecialchars($row['location']) ?><br><small><?php echo htmlspecialchars($row['city']) ?></small></td>
							<td>
								<?php if($row['contact_person']): ?><i class="fa fa-user"></i> <?php echo htmlspecialchars($row['contact_person']) ?><br><?php endif; ?>
								<?php if($row['phone']): ?><i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['phone']) ?><br><?php endif; ?>
								<?php if($row['email']): ?><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($row['email']) ?><?php endif; ?>
							</td>
							<td class="text-center"><span class="badge badge-info"><?php echo $row['block_count'] ?> Blocks</span></td>
							<td class="text-right"><?php echo $row['contract_rate'] ?>%</td>
							<td class="text-center">
								<?php if($row['is_active']): ?>
									<span class="badge badge-success">Active</span>
								<?php else: ?>
									<span class="badge badge-secondary">Inactive</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<button class="btn btn-outline-primary btn-sm edit_estate" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-outline-info btn-sm view_blocks" data-id="<?php echo $row['id'] ?>" data-name="<?php echo htmlspecialchars($row['name']) ?>"><i class="fa fa-building"></i></button>
								<button class="btn btn-outline-danger btn-sm delete_estate" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
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
	$('#estate-list').dataTable()
	
	$('#new_estate').click(function(){
		uni_modal('New Estate', 'manage_estate.php', 'mid-large')
	})
	
	$('.edit_estate').click(function(){
		uni_modal('Edit Estate', 'manage_estate.php?id=' + $(this).attr('data-id'), 'mid-large')
	})
	
	$('.view_blocks').click(function(){
		uni_modal('Blocks of ' + $(this).attr('data-name'), 'manage_estate.php?view_blocks=1&id=' + $(this).attr('data-id'), 'large')
	})
	
	$('.delete_estate').click(function(){
		_conf("Are you sure to delete this estate and all its blocks?", "delete_estate", [$(this).attr('data-id')])
	})
	
	function delete_estate($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_estate',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Estate deleted successfully", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
