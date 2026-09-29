<?php include 'db_connect.php' ?>
<div class="container-fluid">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header"><b>ESTATE SUBSCRIPTIONS</b> <button class="btn btn-primary btn-sm float-right" id="new_sub"><i class="fa fa-plus"></i> New Subscription</button></div>
      <div class="card-body">
        <table class="table table-bordered" id="sub-list">
          <thead><tr><th>Customer</th><th>Estate</th><th>Plan</th><th>Fee</th><th>Washes</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php
            $subs = db_query("SELECT s.*, c.name as cname, e.name as ename FROM estate_subscriptions s LEFT JOIN customers c ON s.customer_id=c.id LEFT JOIN estates e ON s.estate_id=e.id ORDER BY s.date_created DESC");
            foreach($subs as $r):
            ?>
            <tr><td><?php echo e($r['cname']) ?></td><td><?php echo e($r['ename']) ?></td><td><?php echo e($r['plan_name']) ?></td><td>KES <?php echo number_format($r['monthly_fee'],2) ?></td><td><?php echo $r['washes_used'] ?>/<?php echo $r['washes_per_month'] ?></td><td><span class="badge badge-<?php echo $r['status']=='active'?'success':'secondary' ?>"><?php echo ucfirst($r['status']) ?></span></td><td><button class="btn btn-sm btn-danger" onclick="delete_sub(<?php echo $r['id'] ?>)"><i class="fa fa-trash"></i></button></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
$('#sub-list').dataTable();
$('#new_sub').click(function(){ uni_modal('New Subscription','manage_subscription.php','mid-large') });
function delete_sub(id){ _conf("Delete subscription?", "do_delete_sub", [id]) }
function do_delete_sub(id){ start_load(); $.post('ajax.php?action=delete_subscription',{id:id}, function(r){ if(r==1){ alert_toast('Deleted','success'); setTimeout(()=>location.reload(),1000);} }) }
</script>
