<?php
include "db_connect.php";
if (file_exists(__DIR__.'/includes/security.php')) require_once __DIR__.'/includes/security.php';

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
	$row = db_fetch("SELECT * FROM laundry_list WHERE id = ?", [$id]);
	if($row){ foreach($row as $k=>$v) $$k=$v; }
}
$status_labels = ['Pending', 'Received', 'Washing', 'Ironing', 'Ready', 'Out for Delivery', 'Delivered', 'Cancelled'];
$customers = db_query("SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC");
$estates = db_query("SELECT * FROM estates WHERE is_active = 1 ORDER BY name ASC");
$drivers = db_query("SELECT * FROM drivers WHERE is_active = 1 ORDER BY name ASC");
?>
<div class="container-fluid">
	<form action="" id="manage-laundry">
		<div class="col-lg-12">
			<input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
			
			<!-- Customer Selection -->
			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
						<label class="control-label">Customer</label>
						<select name="customer_id" id="customer_id" class="custom-select browser-default">
							<option value="">— Walk-in (type name below) —</option>
							<?php foreach($customers as $c): ?>
							<option value="<?php echo $c['id'] ?>" 
								data-phone="<?php echo htmlspecialchars($c['phone']) ?>"
								data-estate="<?php echo $c['estate_id'] ?>"
								data-pickup="<?php echo htmlspecialchars($c['preferred_pickup_location']) ?>"
								<?php echo isset($customer_id) && $customer_id == $c['id'] ? 'selected' : '' ?>>
								<?php echo htmlspecialchars($c['name']) ?> (<?php echo htmlspecialchars($c['phone']) ?>)
							</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Or Walk-in Name</label>
						<input type="text" class="form-control" name="customer_name" id="customer_name" value="<?php echo isset($customer_name) ? htmlspecialchars($customer_name) : '' ?>">
					</div>
				</div>
			</div>

			<!-- Estate & Pickup Info -->
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Estate</label>
						<select name="estate_id" id="estate_id" class="custom-select browser-default">
							<option value="">— Not applicable —</option>
							<?php foreach($estates as $e): ?>
							<option value="<?php echo $e['id'] ?>" <?php echo isset($estate_id) && $estate_id == $e['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($e['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Pickup Location</label>
						<input type="text" class="form-control" name="pickup_location" id="pickup_location" value="<?php echo isset($pickup_location) ? htmlspecialchars($pickup_location) : '' ?>" placeholder="Gate, Office, etc.">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Delivery Location</label>
						<input type="text" class="form-control" name="delivery_location" value="<?php echo isset($delivery_location) ? htmlspecialchars($delivery_location) : '' ?>" placeholder="Same as pickup, or specify">
					</div>
				</div>
			</div>

			<!-- Scheduling -->
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label">Pickup Date</label>
						<input type="date" class="form-control" name="pickup_date" value="<?php echo isset($pickup_date) ? $pickup_date : date('Y-m-d') ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label class="control-label">Time Slot</label>
						<select name="pickup_time_slot" class="custom-select browser-default">
							<option value="morning" <?php echo isset($pickup_time_slot) && $pickup_time_slot == 'morning' ? 'selected' : '' ?>>Morning</option>
							<option value="afternoon" <?php echo isset($pickup_time_slot) && $pickup_time_slot == 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label">Driver / Rider</label>
						<select name="driver_id" class="custom-select browser-default">
							<option value="">— Assign later —</option>
							<?php foreach($drivers as $d): ?>
							<option value="<?php echo $d['id'] ?>" <?php echo isset($driver_id) && $driver_id == $d['id'] ? 'selected' : '' ?>><?php echo htmlspecialchars($d['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label class="control-label">Express</label>
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" value="1" name="is_express" id="is_express" <?php echo isset($is_express) && $is_express == 1 ? 'checked' : '' ?>>
							<label class="custom-control-label" for="is_express">Express</label>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label class="control-label">Service</label>
						<select name="service_type" class="custom-select browser-default">
							<option value="wash_fold" <?php echo isset($service_type) && $service_type == 'wash_fold' ? 'selected' : '' ?>>Wash & Fold</option>
							<option value="wash_iron" <?php echo isset($service_type) && $service_type == 'wash_iron' ? 'selected' : '' ?>>Wash & Iron</option>
							<option value="dry_clean" <?php echo isset($service_type) && $service_type == 'dry_clean' ? 'selected' : '' ?>>Dry Clean</option>
						</select>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
						<label class="control-label">Gate Pass / Access Notes</label>
						<input type="text" class="form-control" name="gate_pass_notes" value="<?php echo isset($gate_pass_notes) ? htmlspecialchars($gate_pass_notes) : '' ?>" placeholder="e.g. Report to gate guard, ask for John">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Gate PIN (for Askari) <small class="text-muted">Auto-generated</small></label>
						<input type="text" class="form-control" name="gate_pin" value="<?php echo isset($gate_pin) ? htmlspecialchars($gate_pin) : '' ?>" placeholder="e.g. 4821">
					</div>
				</div>
			</div>

			<?php if(isset($_GET['id'])): ?>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label">Status</label>
						<select name="status" class="custom-select browser-default">
							<?php foreach($status_labels as $sk => $sv): ?>
							<option value="<?php echo $sk ?>" <?php echo $status == $sk ? 'selected' : '' ?>><?php echo $sv ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">Remarks</label>
						<textarea name="remarks" cols="30" rows="2" class="form-control"><?php echo isset($remarks) ? htmlspecialchars($remarks) : '' ?></textarea>
					</div>
				</div>
			</div>

			<hr>
			<h5><b>Laundry Items</b></h5>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Category</label>
						<select class="custom-select browser-default" id="laundry_category_id">
							<option value="">— Select —</option>
							<?php
							$cat = db_query("SELECT * FROM laundry_categories ORDER BY name ASC");
							foreach($cat as $row):
							?>
							<option value="<?php echo $row['id'] ?>" data-price="<?php echo $row['price'] ?>" data-pricing="<?php echo $row['pricing_type'] ?? 'per_kg' ?>">
								<?php echo e($row['name']) ?> — KES <?php echo number_format($row['price'], 2) ?>/<?php echo $row['pricing_type'] ?? 'kg' ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label">Qty / Weight</label>
						<input type="number" step="any" min="1" value="1" class="form-control text-right" id="weight">
						<small class="text-muted" id="unit_label">kg or pieces</small>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label">Unit Price (KES)</label>
						<input type="number" step="any" min="0" class="form-control text-right" id="unit_price">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label class="control-label">&nbsp;</label>
						<button class="btn btn-info btn-sm btn-block" type="button" id="add_to_list"><i class="fa fa-plus"></i> Add</button>
					</div>
				</div>
			</div>

			<div class="row">
				<table class="table table-bordered" id="list">
					<colgroup>
						<col width="30%">
						<col width="15%">
						<col width="20%">
						<col width="20%">
						<col width="15%">
					</colgroup>
					<thead>
						<tr>
							<th class="text-center">Category</th>
							<th class="text-center">Qty/Weight</th>
							<th class="text-center">Unit Price</th>
							<th class="text-center">Amount</th>
							<th class="text-center"></th>
						</tr>
					</thead>
					<tbody>
						<?php if(isset($_GET['id'])): ?>
						<?php
						$items = db_query("SELECT * FROM laundry_items WHERE laundry_id = ?", [$id]);
						$cat_names = db_query("SELECT id, name FROM laundry_categories");
						$cname_arr = [];
						foreach($cat_names as $cr) $cname_arr[$cr['id']] = $cr['name'];
						foreach($items as $row):
						?>
						<tr data-id="<?php echo $row['id'] ?>">
							<td>
								<input type="hidden" name="item_id[]" value="<?php echo $row['id'] ?>">
								<input type="hidden" name="laundry_category_id[]" value="<?php echo $row['laundry_category_id'] ?>">
								<?php echo isset($cname_arr[$row['laundry_category_id']]) ? htmlspecialchars($cname_arr[$row['laundry_category_id']]) : 'Unknown' ?>
							</td>
							<td><input type="number" class="text-center form-control-sm" name="weight[]" value="<?php echo $row['weight'] ?>"></td>
							<td class="text-right">
								<input type="hidden" name="unit_price[]" value="<?php echo $row['unit_price'] ?>">
								<?php echo number_format($row['unit_price'], 2) ?>
							</td>
							<td class="text-right">
								<input type="hidden" name="amount[]" value="<?php echo $row['amount'] ?>">
								<?php echo number_format($row['amount'], 2) ?>
							</td>
							<td><button class="btn btn-sm btn-danger" type="button" onclick="rem_list($(this))"><i class="fa fa-times"></i></button></td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
					<tfoot>
						<tr>
							<th class="text-right" colspan="3">Total</th>
							<th class="text-right" id="tamount">0.00</th>
							<th></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<hr>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Payment Method</label>
						<select name="payment_method" class="custom-select browser-default">
							<option value="cash" <?php echo isset($payment_method) && $payment_method == 'cash' ? 'selected' : '' ?>>Cash</option>
							<option value="mpesa" <?php echo isset($payment_method) && $payment_method == 'mpesa' ? 'selected' : '' ?>>M-Pesa</option>
							<option value="bank" <?php echo isset($payment_method) && $payment_method == 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
						</select>
					</div>
				</div>
				<div class="col-md-4" id="mpesa_code_group" style="display:none">
					<div class="form-group">
						<label class="control-label">M-Pesa Transaction Code</label>
						<input type="text" class="form-control" name="mpesa_transaction_code" value="<?php echo isset($mpesa_transaction_code) ? htmlspecialchars($mpesa_transaction_code) : '' ?>" placeholder="e.g. QWE1234R5T">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Payment Status</label>
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" value="1" name="pay" id="paid" <?php echo isset($pay_status) && $pay_status == 1 ? 'checked' : '' ?>>
							<label class="custom-control-label" for="paid">Paid</label>
						</div>
					</div>
				</div>
			</div>
			<div class="row" id="payment">
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Total Amount</label>
						<input type="number" step="any" min="0" value="<?php echo isset($total_amount) ? $total_amount : 0 ?>" class="form-control text-right" name="tamount" readonly>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Amount Tendered</label>
						<input type="number" step="any" min="0" value="<?php echo isset($amount_tendered) ? $amount_tendered : 0 ?>" class="form-control text-right" name="tendered">
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label">Change</label>
						<input type="number" step="any" min="0" value="<?php echo isset($amount_change) ? $amount_change : 0 ?>" class="form-control text-right" name="change" readonly>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
	// Auto-fill customer data
	$('#customer_id').change(function(){
		var opt = $(this).find('option:selected')
		if(opt.val()){
			$('#customer_name').val(opt.text().split(' (')[0])
			if(opt.attr('data-estate')){
				$('#estate_id').val(opt.attr('data-estate'))
			}
			if(opt.attr('data-pickup')){
				$('#pickup_location').val(opt.attr('data-pickup'))
			}
		}
	})

	// Show M-Pesa code field
	$('[name="payment_method"]').change(function(){
		if($(this).val() == 'mpesa'){
			$('#mpesa_code_group').show()
		} else {
			$('#mpesa_code_group').hide()
		}
	})
	if($('[name="payment_method"]').val() == 'mpesa') $('#mpesa_code_group').show()

	// Payment switch
	if($('[name="pay"]').prop('checked') == true){
		$('[name="tendered"]').attr('required', true)
		$('#payment').show()
	} else {
		$('#payment').hide()
		$('[name="tendered"]').attr('required', false)
	}
	$('#paid').click(function(){
		if($('[name="pay"]').prop('checked') == true){
			$('[name="tendered"]').attr('required', true)
			$('#payment').show('slideDown')
		} else {
			$('#payment').hide('slideUp')
			$('[name="tendered"]').attr('required', false)
		}
	})

	// Change calculation
	$('[name="tendered"],[name="tamount"]').on('keyup change input', function(){
		var tend = parseFloat($('[name="tendered"]').val()) || 0
		var amount = parseFloat($('[name="tamount"]').val()) || 0
		var change = tend - amount
		$('[name="change"]').val(change > 0 ? change : 0)
	})

	// Category auto-fills price
	$('#laundry_category_id').change(function(){
		var opt = $(this).find('option:selected')
		if(opt.val()){
			$('#unit_price').val(opt.attr('data-price'))
			$('#unit_label').text(opt.attr('data-pricing') == 'per_piece' ? 'pieces' : 'kg')
		}
	})

	// Add item to list
	$('#add_to_list').click(function(){
		var cat = $('#laundry_category_id').val()
		var qty = $('#weight').val()
		var price = $('#unit_price').val()
		if(!cat || !qty || !price){
			alert_toast('Fill category, quantity and price first.', 'warning')
			return false
		}
		if($('#list tr[data-id="'+cat+'"]').length > 0){
			alert_toast('Category already added. Adjust qty directly.', 'warning')
			return false
		}
		var cname = $('#laundry_category_id option:selected').text().split(' — ')[0]
		var amount = parseFloat(qty) * parseFloat(price)
		var tr = $('<tr data-id="'+cat+'"></tr>')
		tr.append('<td><input type="hidden" name="item_id[]" value=""><input type="hidden" name="laundry_category_id[]" value="'+cat+'">'+cname+'</td>')
		tr.append('<td><input type="number" class="text-center form-control-sm" name="weight[]" value="'+qty+'"></td>')
		tr.append('<td class="text-right"><input type="hidden" name="unit_price[]" value="'+price+'">'+parseFloat(price).toLocaleString('en-US', {minimumFractionDigits:2})+'</td>')
		tr.append('<td class="text-right"><input type="hidden" name="amount[]" value="'+amount+'">'+parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits:2})+'</td>')
		tr.append('<td><button class="btn btn-sm btn-danger" type="button" onclick="rem_list($(this))"><i class="fa fa-times"></i></button></td>')
		$('#list tbody').append(tr)
		calc()
		$('[name="weight[]"]').on('keyup change', function(){ calc() })
		$('[name="tendered"]').trigger('keyup')
		$('#laundry_category_id').val('')
		$('#weight').val('')
		$('#unit_price').val('')
	})

	function rem_list(_this){
		_this.closest('tr').remove()
		calc()
		$('[name="tendered"]').trigger('keyup')
	}

	function calc(){
		var total = 0
		$('#list tbody tr').each(function(){
			var t = $(this)
			var qty = parseFloat(t.find('[name="weight[]"]').val()) || 0
			var price = parseFloat(t.find('[name="unit_price[]"]').val()) || 0
			var amount = qty * price
			t.find('[name="amount[]"]').val(amount)
			t.find('td:eq(3)').html(parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits:2}))
			total += amount
		})
		$('[name="tamount"]').val(total)
		$('#tamount').html(parseFloat(total).toLocaleString('en-US', {minimumFractionDigits:2}))
	}

	// Initialize calc if editing
	<?php if(isset($_GET['id'])): ?>
	calc()
	<?php endif; ?>

	// Submit form
	$('#manage-laundry').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_laundry',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp){
				if(resp == 1){
					alert_toast("Order saved successfully", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				} else if(resp == 2){
					alert_toast("Order updated successfully", 'success')
					setTimeout(function(){ location.reload() }, 1500)
				}
			}
		})
	})
</script>
