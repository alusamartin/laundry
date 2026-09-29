<?php
include 'db_connect.php';
if(isset($_GET['id'])){
	$row = db_fetch("SELECT * FROM expenses WHERE id = ?", [$_GET['id']]);
	extract($row);
}
?>
<div class="container-fluid">
	<form id="manage-expense">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Expense Type</label>
					<select name="expense_type" class="custom-select browser-default" required>
						<option value="">— Select Type —</option>
						<option value="detergent" <?php echo isset($expense_type) && $expense_type == 'detergent' ? 'selected' : '' ?>>Detergent / Chemicals</option>
						<option value="water" <?php echo isset($expense_type) && $expense_type == 'water' ? 'selected' : '' ?>>Water</option>
						<option value="electricity" <?php echo isset($expense_type) && $expense_type == 'electricity' ? 'selected' : '' ?>>Electricity</option>
						<option value="transport" <?php echo isset($expense_type) && $expense_type == 'transport' ? 'selected' : '' ?>>Transport / Fuel</option>
						<option value="staff" <?php echo isset($expense_type) && $expense_type == 'staff' ? 'selected' : '' ?>>Staff / Wages</option>
						<option value="rent" <?php echo isset($expense_type) && $expense_type == 'rent' ? 'selected' : '' ?>>Rent</option>
						<option value="maintenance" <?php echo isset($expense_type) && $expense_type == 'maintenance' ? 'selected' : '' ?>>Maintenance / Repairs</option>
						<option value="marketing" <?php echo isset($expense_type) && $expense_type == 'marketing' ? 'selected' : '' ?>>Marketing / Advertising</option>
						<option value="other" <?php echo isset($expense_type) && $expense_type == 'other' ? 'selected' : '' ?>>Other</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Amount (KES)</label>
					<input type="number" step="0.5" min="0" class="form-control text-right" name="amount" value="<?php echo isset($amount) ? $amount : '' ?>" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Date Incurred</label>
					<input type="date" class="form-control" name="date_incurred" value="<?php echo isset($date_incurred) ? $date_incurred : date('Y-m-d') ?>" required>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Receipt / Reference No.</label>
					<input type="text" class="form-control" name="receipt_no" value="<?php echo isset($receipt_no) ? htmlspecialchars($receipt_no) : '' ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" rows="3"><?php echo isset($description) ? htmlspecialchars($description) : '' ?></textarea>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	$('#manage-expense').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_expense',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Expense saved", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
