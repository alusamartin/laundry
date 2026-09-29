<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-lg-12">
			<button class="btn btn-primary float-right btn-sm" id="new_user"><i class="fa fa-plus"></i> New user</button>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="card col-lg-12">
			<div class="card-body">
				<table class="table-striped table-bordered col-md-12">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Name</th>
							<th class="text-center">Username</th>
							<th class="text-center">Type</th>
							<th class="text-center">Role</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$users = db_query("SELECT * FROM users ORDER BY name ASC");
						$i = 1;
						$type_labels = [1 => 'Admin', 2 => 'Staff'];
						$role_labels = [1 => 'Super Admin', 2 => 'Staff', 3 => 'Estate Manager', 4 => 'Driver'];
						$role_classes = ['','badge-danger','badge-primary','badge-warning','badge-info'];
						foreach($users as $row):
						?>
						<tr>
							<td class="text-center"><?php echo $i++ ?></td>
							<td><?php echo htmlspecialchars($row['name']) ?></td>
							<td><?php echo htmlspecialchars($row['username']) ?></td>
							<td class="text-center"><?php echo $type_labels[$row['type']] ?? 'Unknown' ?></td>
							<td class="text-center">
								<span class="badge <?php echo $role_classes[$row['role'] ?? 2] ?>">
									<?php echo $role_labels[$row['role'] ?? 2] ?>
								</span>
							</td>
							<td class="text-center">
								<div class="btn-group">
									<button type="button" class="btn btn-primary btn-sm">Action</button>
									<button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<span class="sr-only">Toggle Dropdown</span>
									</button>
									<div class="dropdown-menu">
										<a class="dropdown-item edit_user" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Edit</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item delete_user" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
									</div>
								</div>
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
	$('#new_user').click(function(){
		uni_modal('New User', 'manage_user.php')
	})
	$('.edit_user').click(function(){
		uni_modal('Edit User', 'manage_user.php?id=' + $(this).attr('data-id'))
	})
	$('.delete_user').click(function(){
		_conf("Are you sure to delete this user?", "delete_user", [$(this).attr('data-id')])
	})
	function delete_user($id){
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_user',
			method: 'POST',
			data: {id: $id},
			success: function(resp){
				if(resp == 1){
					alert_toast("Data successfully deleted", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	}
</script>
