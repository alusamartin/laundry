<?php
include 'db_connect.php';

if(isset($_GET['id'])){
	$row = db_fetch("SELECT * FROM customers WHERE id = ?", [$_GET['id']]);
	extract($row);
}
?>
<div class="container-fluid">
	<form id="manage-customer">
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
					<label class="control-label">Phone (Safaricom/Airtel)</label>
					<input type="text" class="form-control" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>" placeholder="+2547XX XXX XXX" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" class="form-control" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">National ID (Optional)</label>
					<input type="text" class="form-control" name="national_id" value="<?php echo isset($national_id) ? htmlspecialchars($national_id) : '' ?>" placeholder="Kenyan ID number">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Customer Type</label>
					<select name="customer_type" id="customer_type" class="custom-select browser-default" required>
						<option value="1" <?php echo isset($customer_type) && $customer_type == 1 ? 'selected' : '' ?>>Individual Tenant</option>
						<option value="2" <?php echo isset($customer_type) && $customer_type == 2 ? 'selected' : '' ?>>Estate Management / Caretaker</option>
						<option value="3" <?php echo isset($customer_type) && $customer_type == 3 ? 'selected' : '' ?>>Walk-in Customer</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group" id="estate_group">
					<label class="control-label">Estate</label>
					<select name="estate_id" id="estate_id" class="custom-select browser-default">
						<option value="">— Select Estate —</option>
						<?php
						$estates = db_query("SELECT * FROM estates WHERE is_active = 1 ORDER BY name ASC");
						foreach($estates as $e):
						?>
						<option value="<?php echo $e['id'] ?>" <?php echo isset($estate_id) && $estate_id == $e['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($e['name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row" id="location_fields">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Block / House</label>
					<select name="block_id" id="block_id" class="custom-select browser-default">
						<option value="">— Select Block —</option>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Unit / Flat Number</label>
					<select name="unit_id" id="unit_id" class="custom-select browser-default">
						<option value="">— Select Unit —</option>
					</select>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Preferred Pickup Location</label>
					<input type="text" class="form-control" name="preferred_pickup_location" value="<?php echo isset($preferred_pickup_location) ? htmlspecialchars($preferred_pickup_location) : '' ?>" placeholder="Gate, Office, Block, etc.">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">Notes</label>
					<textarea class="form-control" name="notes" rows="2"><?php echo isset($notes) ? htmlspecialchars($notes) : '' ?></textarea>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	// Toggle estate fields based on customer type
	function toggleEstateFields(){
		var type = $('#customer_type').val()
		if(type == '3'){ // Walk-in
			$('#estate_group').hide()
			$('#location_fields').hide()
		} else {
			$('#estate_group').show()
			$('#location_fields').show()
		}
	}
	toggleEstateFields()
	$('#customer_type').change(toggleEstateFields)

	// Load blocks when estate changes
	$('#estate_id').change(function(){
		var eid = $(this).val()
		$('#block_id').html('<option value="">— Select Block —</option>')
		$('#unit_id').html('<option value="">— Select Unit —</option>')
		if(eid){
			$.ajax({
				url: 'ajax.php?action=get_blocks',
				method: 'POST',
				data: {estate_id: eid},
				success: function(resp){
					if(resp){
						var blocks = JSON.parse(resp)
						var opts = '<option value="">— Select Block —</option>'
						blocks.forEach(function(b){
							opts += '<option value="'+b.id+'">'+b.name+'</option>'
						})
						$('#block_id').html(opts)
						<?php if(isset($block_id)): ?>
						$('#block_id').val('<?php echo $block_id ?>')
						$('#block_id').trigger('change')
						<?php endif; ?>
					}
				}
			})
		}
	})

	// Load units when block changes
	$('#block_id').change(function(){
		var bid = $(this).val()
		$('#unit_id').html('<option value="">— Select Unit —</option>')
		if(bid){
			$.ajax({
				url: 'ajax.php?action=get_units',
				method: 'POST',
				data: {block_id: bid},
				success: function(resp){
					if(resp){
						var units = JSON.parse(resp)
						var opts = '<option value="">— Select Unit —</option>'
						units.forEach(function(u){
							opts += '<option value="'+u.id+'">'+u.unit_number+(u.floor ? ' ('+u.floor+')' : '')+'</option>'
						})
						$('#unit_id').html(opts)
						<?php if(isset($unit_id)): ?>
						$('#unit_id').val('<?php echo $unit_id ?>')
						<?php endif; ?>
					}
				}
			})
		}
	})

	// Trigger load if editing
	<?php if(isset($estate_id)): ?>
	$('#estate_id').trigger('change')
	<?php endif; ?>

	$('#manage-customer').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_customer',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Customer saved successfully", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
