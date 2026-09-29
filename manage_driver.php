<?php
include 'db_connect.php';
if(isset($_GET['id'])){
	$row = db_fetch("SELECT * FROM drivers WHERE id = ?", [$_GET['id']]);
	extract($row);
}
?>
<div class="container-fluid">
	<form id="manage-driver">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Full Name</label>
					<input type="text" class="form-control" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : '' ?>" required>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Phone</label>
					<input type="text" class="form-control" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>" placeholder="+2547XX XXX XXX" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">National ID</label>
					<input type="text" class="form-control" name="national_id" value="<?php echo isset($national_id) ? htmlspecialchars($national_id) : '' ?>" placeholder="Kenyan ID number">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" class="form-control" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Vehicle Type</label>
					<select name="vehicle_type" class="custom-select browser-default">
						<option value="">— Select —</option>
						<option value="Motorcycle" <?php echo isset($vehicle_type) && $vehicle_type == 'Motorcycle' ? 'selected' : '' ?>>Motorcycle (Boda Bod)</option>
						<option value="Bicycle" <?php echo isset($vehicle_type) && $vehicle_type == 'Bicycle' ? 'selected' : '' ?>>Bicycle</option>
						<option value="Car" <?php echo isset($vehicle_type) && $vehicle_type == 'Car' ? 'selected' : '' ?>>Car</option>
						<option value="Van" <?php echo isset($vehicle_type) && $vehicle_type == 'Van' ? 'selected' : '' ?>>Van</option>
						<option value="Tuk Tuk" <?php echo isset($vehicle_type) && $vehicle_type == 'Tuk Tuk' ? 'selected' : '' ?>>Tuk Tuk</option>
						<option value="On Foot" <?php echo isset($vehicle_type) && $vehicle_type == 'On Foot' ? 'selected' : '' ?>>On Foot</option>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Vehicle Registration</label>
					<input type="text" class="form-control" name="vehicle_reg" value="<?php echo isset($vehicle_reg) ? htmlspecialchars($vehicle_reg) : '' ?>" placeholder="e.g. KCB 123M">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Status</label>
					<select name="is_active" class="custom-select browser-default">
						<option value="1" <?php echo isset($is_active) && $is_active == 1 ? 'selected' : '' ?>>Active</option>
						<option value="0" <?php echo isset($is_active) && $is_active == 0 ? 'selected' : '' ?>>Inactive</option>
					</select>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	$('#manage-driver').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_driver',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Driver saved", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
