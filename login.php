<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>LaundryPro Kenya | Login</title>
	<?php include('./header.php'); ?>
	<?php include('./db_connect.php'); ?>
	<?php
	if (session_status() === PHP_SESSION_NONE) session_start();
	if (isset($_SESSION['login_id']))
		header("location:index.php?page=home");
	?>
</head>
<style>
	body { width: 100%; height: calc(100%); }
	main#main { width:100%; height: calc(100%); background:white; }
	#login-right { position: absolute; right:0; width:40%; height: calc(100%); background:white; display: flex; align-items: center; }
	#login-left { position: absolute; left:0; width:60%; height: calc(100%); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; flex-direction: column; }
	#login-right .card { margin: auto; }
	.logo { margin: auto; font-size: 8rem; background: white; padding: .5em 0.7em; border-radius: 50% 50%; color: #000000b3; }
	.login-title { color: white; font-size: 2.5rem; font-weight: bold; margin-top: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
	.login-subtitle { color: rgba(255,255,255,0.8); font-size: 1.2rem; }
	.feature-list { color: white; margin-top: 30px; text-align: left; }
	.feature-list li { list-style: none; padding: 5px 0; }
	.feature-list li i { margin-right: 10px; }
</style>
<body>
	<main id="main" class="bg-dark">
		<div id="login-left">
			<div>
				<div class="logo"><i class="fa fa-tshirt"></i></div>
				<div class="login-title">LaundryPro Kenya</div>
				<div class="login-subtitle">Estate-Friendly Laundry Management</div>
				<div class="feature-list">
					<li><i class="fa fa-check-circle"></i> Estate & Apartment Complex Management</li>
					<li><i class="fa fa-check-circle"></i> M-Pesa Ready Payment System</li>
					<li><i class="fa fa-check-circle"></i> Driver / Rider Assignment</li>
					<li><i class="fa fa-check-circle"></i> Order Tracking from Wash to Delivery</li>
				</div>
			</div>
		</div>
		<div id="login-right">
			<div class="card col-md-8">
				<div class="card-body">
					<h4 class="text-center mb-4"><b>Staff Login</b></h4>
					<form id="login-form">
						<div class="form-group">
							<label for="username" class="control-label">Username</label>
							<input type="text" id="username" name="username" class="form-control">
						</div>
						<div class="form-group">
							<label for="password" class="control-label">Password</label>
							<input type="password" id="password" name="password" class="form-control">
						</div>
						<center><button type="submit" id="loginBtn" class="btn-sm btn-block btn-wave col-md-4 btn-primary">Login</button></center>
					</form>
				</div>
			</div>
		</div>
	</main>
	<a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>
</body>
<script>
	$('#login-form').submit(function(e){
		e.preventDefault()
		$('#loginBtn').attr('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Logging in...');
		$(this).find('.alert-danger').remove();
		$.ajax({
			url:'ajax.php?action=login',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
				$('#login-form').prepend('<div class="alert alert-danger">Unable to reach server, please try again.</div>')
				$('#loginBtn').removeAttr('disabled').html('Login');
			},
			success:function(resp){
				if(resp == 1){
					location.href ='index.php?page=home';
				}else{
					$('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>')
					$('#loginBtn').removeAttr('disabled').html('Login');
				}
			}
		})
	})
</script>
</html>
