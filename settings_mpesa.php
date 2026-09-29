<?php include 'db_connect.php';
$settings = db_query("SELECT * FROM system_settings LIMIT 1");
$s = $settings[0] ?? [];
?>
<div class="container-fluid">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header"><b>System Settings - M-Pesa & SMS</b></div>
      <div class="card-body">
        <form id="settings-form">
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Business Name</label><input type="text" name="name" class="form-control" value="<?php echo e($s['name']??'') ?>"></div></div>
            <div class="col-md-3"><div class="form-group"><label>Currency</label><input type="text" name="currency" class="form-control" value="<?php echo e($s['currency']??'KES') ?>"></div></div>
            <div class="col-md-3"><div class="form-group"><label>Timezone</label><input type="text" name="timezone" class="form-control" value="<?php echo e($s['timezone']??'Africa/Nairobi') ?>"></div></div>
          </div>
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>Email</label><input type="text" name="email" class="form-control" value="<?php echo e($s['email']??'') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Contact</label><input type="text" name="contact" class="form-control" value="<?php echo e($s['contact']??'') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>About</label><input type="text" name="about" class="form-control" value="<?php echo e($s['about_content']??'') ?>"></div></div>
          </div>
          <hr><h5>M-Pesa Daraja (Safaricom)</h5>
          <div class="row">
            <div class="col-md-3"><div class="form-group"><label>Consumer Key</label><input type="text" name="mpesa_consumer_key" class="form-control" value="<?php echo e($s['mpesa_consumer_key']??'') ?>"></div></div>
            <div class="col-md-3"><div class="form-group"><label>Consumer Secret</label><input type="password" name="mpesa_consumer_secret" class="form-control" value="<?php echo e($s['mpesa_consumer_secret']??'') ?>"></div></div>
            <div class="col-md-2"><div class="form-group"><label>Shortcode</label><input type="text" name="mpesa_shortcode" class="form-control" value="<?php echo e($s['mpesa_shortcode']??'') ?>" placeholder="174379"></div></div>
            <div class="col-md-2"><div class="form-group"><label>Environment</label><select name="mpesa_environment" class="custom-select"><option value="sandbox" <?php echo ($s['mpesa_environment']??'')=='sandbox'?'selected':'' ?>>Sandbox</option><option value="production" <?php echo ($s['mpesa_environment']??'')=='production'?'selected':'' ?>>Production</option></select></div></div>
            <div class="col-md-2"><div class="form-group"><label>Passkey</label><input type="password" name="mpesa_passkey" class="form-control" value="<?php echo e($s['mpesa_passkey']??'') ?>"></div></div>
          </div>
          <div class="form-group"><label>Callback URL</label><input type="text" name="mpesa_callback_url" class="form-control" value="<?php echo e($s['mpesa_callback_url']??'') ?>" placeholder="https://yourdomain/laundry/callback/mpesa_callback.php"><small class="text-muted">Register this URL in Daraja portal</small></div>
          <hr><h5>Africa's Talking SMS</h5>
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>AT Username</label><input type="text" name="sms_username" class="form-control" value="<?php echo e($s['sms_username']??'') ?>" placeholder="sandbox"></div></div>
            <div class="col-md-4"><div class="form-group"><label>AT API Key</label><input type="password" name="sms_api_key" class="form-control" value="<?php echo e($s['sms_api_key']??'') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>WhatsApp Token (optional)</label><input type="text" name="whatsapp_token" class="form-control" value="<?php echo e($s['whatsapp_token']??'') ?>"></div></div>
          </div>
          <button class="btn btn-primary" type="submit">Save Settings</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
$('#settings-form').submit(function(e){
  e.preventDefault(); start_load();
  $.ajax({url:'ajax.php?action=save_settings', method:'POST', data: new FormData(this), contentType:false, processData:false,
    success:function(resp){ if(resp==1){ alert_toast('Settings saved','success'); setTimeout(()=>location.reload(),1000);} else alert_toast('Failed','danger'); end_load(); }
  })
})
</script>
