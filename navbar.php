<nav id="sidebar" class='mx-lt-5 bg-dark'>
	<div class="sidebar-list">
		<a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
		<a href="index.php?page=laundry" class="nav-item nav-laundry"><span class='icon-field'><i class="fa fa-water"></i></span> Orders</a>
		<a href="index.php?page=categories" class="nav-item nav-categories"><span class='icon-field'><i class="fa fa-list"></i></span> Services & Pricing</a>
		<a href="index.php?page=estates" class="nav-item nav-estates"><span class='icon-field'><i class="fa fa-building"></i></span> Estates</a>
		<a href="index.php?page=customers" class="nav-item nav-customers"><span class='icon-field'><i class="fa fa-users"></i></span> Customers</a>
		<a href="index.php?page=drivers" class="nav-item nav-drivers"><span class='icon-field'><i class="fa fa-motorcycle"></i></span> Drivers / Riders</a>
		<a href="index.php?page=pickup_delivery" class="nav-item nav-pickup_delivery"><span class='icon-field'><i class="fa fa-truck"></i></span> Pickup & Delivery</a>
		<a href="index.php?page=supply" class="nav-item nav-supply"><span class='icon-field'><i class="fa fa-boxes"></i></span> Supply List</a>
		<a href="index.php?page=inventory" class="nav-item nav-inventory"><span class='icon-field'><i class="fa fa-list-alt"></i></span> Inventory</a>
		<a href="index.php?page=expenses" class="nav-item nav-expenses"><span class='icon-field'><i class="fa fa-money-bill"></i></span> Expenses</a>
		<a href="index.php?page=reports" class="nav-item nav-reports"><span class='icon-field'><i class="fa fa-th-list"></i></span> Reports</a>
		<a href="index.php?page=manifest" class="nav-item nav-manifest"><span class='icon-field'><i class="fa fa-clipboard-list"></i></span> Pickup Manifest</a>
		<a href="index.php?page=subscriptions" class="nav-item nav-subscriptions"><span class='icon-field'><i class="fa fa-sync"></i></span> Subscriptions</a>
		<a href="index.php?page=settings_mpesa" class="nav-item nav-settings_mpesa"><span class='icon-field'><i class="fa fa-cogs"></i></span> Settings (M-Pesa/SMS)</a>
		<?php if($_SESSION['login_type'] == 1): ?>
		<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-user-cog"></i></span> Users</a>
		<?php endif; ?>
	</div>
</nav>
<script>
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>
<?php if($_SESSION['login_type'] == 2): ?>
<style>
	.nav-sales ,.nav-users, .nav-drivers, .nav-expenses, .nav-pickup_delivery, .nav-estates, .nav-customers {
		display: none!important;
	}
</style>
<?php endif; ?>
