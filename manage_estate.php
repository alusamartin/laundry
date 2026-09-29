<?php
include 'db_connect.php';

// If viewing blocks, show block management screen
if(isset($_GET['view_blocks'])){
	$eid = $_GET['id'];
	$estate = db_fetch("SELECT * FROM estates WHERE id = ?", [$eid]);
	$blocks = db_query("SELECT * FROM estate_blocks WHERE estate_id = ? ORDER BY name ASC", [$eid]);
	?>
	<div class="container-fluid">
		<div class="row mb-3">
			<div class="col-md-8">
				<h5><b><?php echo htmlspecialchars($estate['name']) ?></b> — Blocks & Units</h5>
			</div>
			<div class="col-md-4 text-right">
				<button class="btn btn-primary btn-sm" id="add_block" data-eid="<?php echo $eid ?>"><i class="fa fa-plus"></i> Add Block</button>
			</div>
		</div>
		<div class="row">
			<?php if(count($blocks) == 0): ?>
				<div class="col-12"><p class="text-muted">No blocks added yet.</p></div>
			<?php endif; ?>
			<?php foreach($blocks as $b): 
				$units = db_query("SELECT * FROM estate_units WHERE block_id = ? ORDER BY unit_number ASC", [$b['id']]);
			?>
			<div class="col-md-4 mb-3">
				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<b><?php echo htmlspecialchars($b['name']) ?></b>
						<div>
							<button class="btn btn-sm btn-outline-primary add_unit" data-bid="<?php echo $b['id'] ?>"><i class="fa fa-plus"></i></button>
							<button class="btn btn-sm btn-outline-danger delete_block" data-id="<?php echo $b['id'] ?>"><i class="fa fa-trash"></i></button>
						</div>
					</div>
					<div class="card-body py-2">
						<?php if(count($units) == 0): ?>
							<small class="text-muted">No units</small>
						<?php endif; ?>
						<?php foreach($units as $u): ?>
						<div class="d-flex justify-content-between align-items-center mb-1">
							<small><i class="fa fa-door-open"></i> <?php echo htmlspecialchars($u['unit_number']) ?><?php echo $u['floor'] ? ' ('.$u['floor'].')' : '' ?></small>
							<button class="btn btn-sm btn-outline-danger delete_unit" data-id="<?php echo $u['id'] ?>"><i class="fa fa-times"></i></button>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<script>
		$('#add_block').click(function(){
			var eid = $(this).attr('data-eid')
			var name = prompt("Enter block/house name (e.g. Block A, Phase 1):")
			if(name && name.trim() != ''){
				start_load()
				$.ajax({
					url: 'ajax.php?action=save_block',
					method: 'POST',
					data: {estate_id: eid, name: name.trim()},
					success: function(resp){
						if(resp == 1){
							alert_toast("Block added", 'success')
							setTimeout(function(){ location.reload() }, 1000)
						}
					}
				})
			}
		})
		$('.add_unit').click(function(){
			var bid = $(this).attr('data-bid')
			var unit = prompt("Enter unit number (e.g. Flat 3A, House 12):")
			var floor = prompt("Enter floor (optional, e.g. Ground, 1st, 2nd):")
			if(unit && unit.trim() != ''){
				start_load()
				$.ajax({
					url: 'ajax.php?action=save_unit',
					method: 'POST',
					data: {block_id: bid, unit_number: unit.trim(), floor: floor},
					success: function(resp){
						if(resp == 1){
							alert_toast("Unit added", 'success')
							setTimeout(function(){ location.reload() }, 1000)
						}
					}
				})
			}
		})
		$('.delete_block').click(function(){
			_conf("Delete this block and all its units?", "delete_block", [$(this).attr('data-id')])
		})
		$('.delete_unit').click(function(){
			_conf("Delete this unit?", "delete_unit", [$(this).attr('data-id')])
		})
		function delete_block($id){
			start_load()
			$.ajax({
				url: 'ajax.php?action=delete_block',
				method: 'POST',
				data: {id: $id},
				success: function(resp){
					if(resp == 1){ alert_toast("Deleted",'success'); setTimeout(function(){ location.reload() }, 1000) }
				}
			})
		}
		function delete_unit($id){
			start_load()
			$.ajax({
				url: 'ajax.php?action=delete_unit',
				method: 'POST',
				data: {id: $id},
				success: function(resp){
					if(resp == 1){ alert_toast("Deleted",'success'); setTimeout(function(){ location.reload() }, 1000) }
				}
			})
		}
	</script>
	<?php
	exit;
}

// Main estate form
if(isset($_GET['id'])){
	$row = db_fetch("SELECT * FROM estates WHERE id = ?", [$_GET['id']]);
	extract($row);
}
?>
<div class="container-fluid">
	<form id="manage-estate">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Estate Name</label>
					<input type="text" class="form-control" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : '' ?>" required>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">City</label>
					<select name="city" class="custom-select browser-default">
						<option value="Nairobi" <?php echo isset($city) && $city == 'Nairobi' ? 'selected' : '' ?>>Nairobi</option>
						<option value="Mombasa" <?php echo isset($city) && $city == 'Mombasa' ? 'selected' : '' ?>>Mombasa</option>
						<option value="Kisumu" <?php echo isset($city) && $city == 'Kisumu' ? 'selected' : '' ?>>Kisumu</option>
						<option value="Eldoret" <?php echo isset($city) && $city == 'Eldoret' ? 'selected' : '' ?>>Eldoret</option>
						<option value="Nakuru" <?php echo isset($city) && $city == 'Nakuru' ? 'selected' : '' ?>>Nakuru</option>
						<option value="Other" <?php echo isset($city) && $city == 'Other' ? 'selected' : '' ?>>Other</option>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">Location / Address</label>
					<textarea class="form-control" name="location" rows="2"><?php echo isset($location) ? htmlspecialchars($location) : '' ?></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Contact Person</label>
					<input type="text" class="form-control" name="contact_person" value="<?php echo isset($contact_person) ? htmlspecialchars($contact_person) : '' ?>">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Phone</label>
					<input type="text" class="form-control" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>" placeholder="+2547XX XXX XXX">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" class="form-control" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Gate Access Notes</label>
					<textarea class="form-control" name="gate_access_notes" rows="2" placeholder="e.g. Report to gate, ask for caretaker, etc."><?php echo isset($gate_access_notes) ? htmlspecialchars($gate_access_notes) : '' ?></textarea>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label">Contract Discount (%)</label>
					<input type="number" step="0.1" min="0" max="100" class="form-control" name="contract_rate" value="<?php echo isset($contract_rate) ? $contract_rate : 0 ?>">
					<small class="text-muted">Percentage discount for estate bulk orders</small>
				</div>
			</div>
			<div class="col-md-3">
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
	$('#manage-estate').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_estate',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Estate saved successfully", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
