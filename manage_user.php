<?php
include('db_connect.php');
if(isset($_GET['id'])){
	$id = intval($_GET['id']);
	$row = db_fetch("SELECT * FROM users WHERE id = ?", [$id]);
	if($row) $meta = $row;
}
?>
<div class="container-fluid">
	<form action="" id="manage-user">
		<input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id']: '' ?>">
		<div class="form-group">
			<label for="name">Name</label>
			<input type="text" name="name" id="name" class="form-control" value="<?php echo isset($meta['name']) ? htmlspecialchars($meta['name']): '' ?>" required>
		</div>
		<div class="form-group">
			<label for="username">Username</label>
			<input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? htmlspecialchars($meta['username']): '' ?>" required>
		</div>
		<div class="form-group">
			<label for="password">Password <?php if(isset($meta['id'])) echo '<small class="text-muted">(leave blank to keep current)</small>'; ?></label>
			<input type="password" name="password" id="password" class="form-control" value="" <?php echo isset($meta['id']) ? '' : 'required' ?>>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="type">User Type</label>
					<select name="type" id="type" class="custom-select">
						<option value="1" <?php echo isset($meta['type']) && $meta['type'] == 1 ? 'selected': '' ?>>Admin</option>
						<option value="2" <?php echo isset($meta['type']) && $meta['type'] == 2 ? 'selected': '' ?>>Staff</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="role">Role</label>
					<select name="role" id="role" class="custom-select">
						<option value="1" <?php echo isset($meta['role']) && $meta['role'] == 1 ? 'selected': '' ?>>Super Admin</option>
						<option value="2" <?php echo isset($meta['role']) && $meta['role'] == 2 ? 'selected': '' ?>>Staff (Laundry)</option>
						<option value="3" <?php echo isset($meta['role']) && $meta['role'] == 3 ? 'selected': '' ?>>Estate Manager</option>
						<option value="4" <?php echo isset($meta['role']) && $meta['role'] == 4 ? 'selected': '' ?>>Driver</option>
					</select>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	$('#manage-user').submit(function(e){
		e.preventDefault();
		start_load()
		$.ajax({
			url:'ajax.php?action=save_user',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp ==1){
					alert_toast("Data successfully saved",'success')
					setTimeout(function(){ location.reload() },1500)
				}
			}
		})
	})
</script>
