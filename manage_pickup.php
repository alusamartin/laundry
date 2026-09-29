<?php
include 'db_connect.php';
if(isset($_GET['id'])){
	$row = db_fetch("SELECT * FROM pickup_schedule WHERE id = ?", [$_GET['id']]);
	extract($row);
}
?>
<div class="container-fluid">
	<form id="manage-schedule">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Schedule Type</label>
					<select name="schedule_type" class="custom-select browser-default" required>
						<option value="pickup" <?php echo isset($schedule_type) && $schedule_type == 'pickup' ? 'selected' : '' ?>>Pickup</option>
						<option value="delivery" <?php echo isset($schedule_type) && $schedule_type == 'delivery' ? 'selected' : '' ?>>Delivery</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Scheduled Date</label>
					<input type="date" class="form-control" name="scheduled_date" value="<?php echo isset($scheduled_date) ? $scheduled_date : date('Y-m-d') ?>" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Time Slot</label>
					<select name="time_slot" class="custom-select browser-default" required>
						<option value="morning" <?php echo isset($time_slot) && $time_slot == 'morning' ? 'selected' : '' ?>>Morning (8AM - 12PM)</option>
						<option value="afternoon" <?php echo isset($time_slot) && $time_slot == 'afternoon' ? 'selected' : '' ?>>Afternoon (12PM - 5PM)</option>
						<option value="evening" <?php echo isset($time_slot) && $time_slot == 'evening' ? 'selected' : '' ?>>Evening (5PM - 8PM)</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Estate</label>
					<select name="estate_id" class="custom-select browser-default">
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
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Customer</label>
					<select name="customer_id" class="custom-select browser-default">
						<option value="">— Select Customer —</option>
						<?php
						$customers = db_query("SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC");
						foreach($customers as $c):
						?>
						<option value="<?php echo $c['id'] ?>" <?php echo isset($customer_id) && $customer_id == $c['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($c['name']) ?> (<?php echo htmlspecialchars($c['phone']) ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Driver / Rider</label>
					<select name="driver_id" class="custom-select browser-default">
						<option value="">— Unassigned —</option>
						<?php
						$drivers = db_query("SELECT * FROM drivers WHERE is_active = 1 ORDER BY name ASC");
						foreach($drivers as $d):
						?>
						<option value="<?php echo $d['id'] ?>" <?php echo isset($driver_id) && $driver_id == $d['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($d['name']) ?> (<?php echo htmlspecialchars($d['vehicle_type'] ?? '—') ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Status</label>
					<select name="status" class="custom-select browser-default">
						<option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Pending</option>
						<option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Assigned</option>
						<option value="2" <?php echo isset($status) && $status == 2 ? 'selected' : '' ?>>Completed</option>
						<option value="3" <?php echo isset($status) && $status == 3 ? 'selected' : '' ?>>Cancelled</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Order (optional)</label>
					<select name="order_id" class="custom-select browser-default">
						<option value="">— No specific order —</option>
						<?php
						$orders = db_query("SELECT id, customer_name FROM laundry_list ORDER BY id DESC LIMIT 50");
						foreach($orders as $o):
						?>
						<option value="<?php echo $o['id'] ?>" <?php echo isset($order_id) && $order_id == $o['id'] ? 'selected' : '' ?>>#<?php echo $o['id'] ?> - <?php echo htmlspecialchars($o['customer_name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">Notes / Instructions</label>
					<textarea class="form-control" name="notes" rows="2"><?php echo isset($notes) ? htmlspecialchars($notes) : '' ?></textarea>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	$('#manage-schedule').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_schedule',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Schedule saved", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
