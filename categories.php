<?php include('db_connect.php');?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-md-5">
			<form action="" id="manage-category">
				<div class="card">
					<div class="card-header">
						    Service / Pricing Form
				  	</div>
					<div class="card-body">
							<input type="hidden" name="id">
							<div class="form-group">
								<label class="control-label">Service Name</label>
								<input type="text" class="form-control" name="name" placeholder="e.g. Shirt, Bed Sheet, Per Kg Wash & Fold">
							</div>
							<div class="form-group">
								<label class="control-label">Pricing Type</label>
								<select name="pricing_type" id="pricing_type" class="custom-select browser-default">
									<option value="per_kg">Per Kilogram (kg)</option>
									<option value="per_piece">Per Piece</option>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Unit Price (KES)</label>
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">KES</span></div>
									<input type="number" class="form-control text-right" min="1" step="any" name="price" placeholder="e.g. 30">
									<div class="input-group-append"><span class="input-group-text" id="unit_label">/kg</span></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label">Express Surcharge (KES)</label>
								<input type="number" class="form-control text-right" min="0" step="any" name="express_surcharge" value="0" placeholder="Extra charge for express">
								<small class="text-muted">Additional amount for same/next day service</small> 
							</div>
					</div>
					<div class="card-footer">
						<div class="row">
							<div class="col-md-12">
								<button class="btn btn-sm btn-primary col-sm-3 offset-md-3"> Save</button>
								<button class="btn btn-sm btn-default col-sm-3" type="button" onclick="$('#manage-category').get(0).reset()"> Cancel</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			</div>

			<div class="col-md-7">
				<div class="card">
					<div class="card-body">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Name</th>
									<th class="text-center">Type</th>
									<th class="text-center">Price</th>
									<th class="text-center">Express</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$cats = db_query("SELECT * FROM laundry_categories ORDER BY name ASC");
								foreach($cats as $row):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td><b><?php echo htmlspecialchars($row['name']) ?></b></td>
									<td class="text-center">
										<span class="badge badge-<?php echo ($row['pricing_type'] ?? 'per_kg') == 'per_kg' ? 'info' : 'success' ?>">
											<?php echo ($row['pricing_type'] ?? 'per_kg') == 'per_kg' ? 'Per Kg' : 'Per Piece' ?>
										</span>
									</td>
									<td class="text-right"><b>KES <?php echo number_format($row['price'], 2) ?></b></td>
									<td class="text-right"><?php echo $row['express_surcharge'] ? 'KES '.number_format($row['express_surcharge'], 2) : '—' ?></td>
									<td class="text-center">
										<button class="btn btn-sm btn-primary edit_cat" type="button" data-id="<?php echo $row['id'] ?>" data-name="<?php echo htmlspecialchars($row['name']) ?>" data-price="<?php echo $row['price'] ?>" data-pricing="<?php echo $row['pricing_type'] ?? 'per_kg' ?>" data-express="<?php echo $row['express_surcharge'] ?? 0 ?>">Edit</button>
										<button class="btn btn-sm btn-danger delete_cat" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	td { vertical-align: middle !important; }
	td p { margin: unset }
</style>
<script>
	$('#pricing_type').change(function(){
		if($(this).val() == 'per_piece'){
			$('#unit_label').text('/piece')
		} else {
			$('#unit_label').text('/kg')
		}
	})
	$('#manage-category').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_category',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success')
					setTimeout(function(){ location.reload() },1500)
				}
				else if(resp==2){
					alert_toast("Data successfully updated",'success')
					setTimeout(function(){ location.reload() },1500)
				}
			}
		})
	})
	$('.edit_cat').click(function(){
		start_load()
		var cat = $('#manage-category')
		cat.get(0).reset()
		cat.find("[name='id']").val($(this).attr('data-id'))
		cat.find("[name='name']").val($(this).attr('data-name'))
		cat.find("[name='price']").val($(this).attr('data-price'))
		cat.find("[name='pricing_type']").val($(this).attr('data-pricing'))
		cat.find("[name='express_surcharge']").val($(this).attr('data-express'))
		cat.find('#pricing_type').trigger('change')
		end_load()
	})
	$('.delete_cat').click(function(){
		_conf("Are you sure to delete this service?","delete_cat",[$(this).attr('data-id')])
	})
	function delete_cat($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_category',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){ location.reload() },1500)
				}
			}
		})
	}
</script>
