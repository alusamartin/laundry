<?php
ob_start();
$action = $_GET['action'] ?? '';
// Basic allowlist for actions
$allowed = ['login','logout','save_user','delete_user','save_settings','save_category','delete_category','save_supply','delete_supply','save_laundry','delete_laundry','save_inv','delete_inv','save_estate','delete_estate','save_block','delete_block','save_unit','delete_unit','get_blocks','get_units','save_customer','delete_customer','save_expense','delete_expense','save_driver','delete_driver','save_schedule','delete_schedule','mpesa_stk','verify_mpesa_code','send_sms','update_order_status','save_subscription','delete_subscription'];
if (!in_array($action, $allowed)) { http_response_code(400); exit('Invalid action'); }
include 'admin_class.php';
$crud = new Action();

if($action == 'login'){
	$login = $crud->login();
	echo $login;
}
if($action == 'logout'){
	$crud->logout();
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save) echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save) echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save) echo $save;
}
if($action == "save_category"){
	$save = $crud->save_category();
	if($save) echo $save;
}
if($action == "delete_category"){
	$save = $crud->delete_category();
	if($save) echo $save;
}
if($action == "save_supply"){
	$save = $crud->save_supply();
	if($save) echo $save;
}
if($action == "delete_supply"){
	$save = $crud->delete_supply();
	if($save) echo $save;
}
if($action == "save_laundry"){
	$save = $crud->save_laundry();
	if($save) echo $save;
}
if($action == "delete_laundry"){
	$save = $crud->delete_laundry();
	if($save) echo $save;
}
if($action == "save_inv"){
	$save = $crud->save_inv();
	if($save) echo $save;
}
if($action == "delete_inv"){
	$save = $crud->delete_inv();
	if($save) echo $save;
}

// ============ NEW MODULES ============
if($action == "save_estate"){
	$save = $crud->save_estate();
	if($save) echo $save;
}
if($action == "delete_estate"){
	$save = $crud->delete_estate();
	if($save) echo $save;
}
if($action == "save_block"){
	$save = $crud->save_block();
	if($save) echo $save;
}
if($action == "delete_block"){
	$save = $crud->delete_block();
	if($save) echo $save;
}
if($action == "save_unit"){
	$save = $crud->save_unit();
	if($save) echo $save;
}
if($action == "delete_unit"){
	$save = $crud->delete_unit();
	if($save) echo $save;
}
if($action == "get_blocks"){
	$crud->get_blocks();
}
if($action == "get_units"){
	$crud->get_units();
}
if($action == "save_customer"){
	$save = $crud->save_customer();
	if($save) echo $save;
}
if($action == "delete_customer"){
	$save = $crud->delete_customer();
	if($save) echo $save;
}
if($action == "save_expense"){
	$save = $crud->save_expense();
	if($save) echo $save;
}
if($action == "delete_expense"){
	$save = $crud->delete_expense();
	if($save) echo $save;
}
if($action == "save_driver"){
	$save = $crud->save_driver();
	if($save) echo $save;
}
if($action == "delete_driver"){
	$save = $crud->delete_driver();
	if($save) echo $save;
}
if($action == "save_schedule"){
	$save = $crud->save_schedule();
	if($save) echo $save;
}
if($action == "delete_schedule"){
	$save = $crud->delete_schedule();
	if($save) echo $save;
}
if($action == "mpesa_stk"){
	require_once 'includes/mpesa.php';
	$phone = $_POST['phone'] ?? '';
	$amount = $_POST['amount'] ?? 0;
	$order_id = $_POST['order_id'] ?? 0;
	$res = mpesa_stk_push($phone, $amount, $order_id);
	echo json_encode($res);
}
if($action == "verify_mpesa_code"){
	require_once 'includes/mpesa.php';
	$code = $_POST['code'] ?? '';
	echo mpesa_verify_transaction_code($code) ? 1 : 0;
}
if($action == "send_sms"){
	require_once 'includes/sms.php';
	$to = $_POST['to'] ?? '';
	$msg = $_POST['message'] ?? '';
	$order_id = $_POST['order_id'] ?? null;
	$res = send_sms($to, $msg, $order_id);
	echo json_encode($res);
}
if($action == "update_order_status"){
	$id = intval($_POST['id'] ?? 0);
	$status = intval($_POST['status'] ?? 0);
	if($id>0){
		db_execute("UPDATE laundry_list SET status=? WHERE id=?", [$status, $id]);
		db_insert("INSERT INTO order_status_history (order_id,status,changed_by) VALUES (?,?,?)", [$id,$status,$_SESSION['login_id'] ?? null]);
		// SMS notify
		if(file_exists('includes/sms.php')){
			require_once 'includes/sms.php';
			$o = db_query("SELECT customer_id FROM laundry_list WHERE id=?", [$id]);
			if(!empty($o) && $o[0]['customer_id']){
				$c = db_query("SELECT phone,name FROM customers WHERE id=?", [$o[0]['customer_id']]);
				if(!empty($c)) send_status_sms($c[0]['phone'], $id, $status, $c[0]['name']);
			}
		}
		echo 1;
	} else echo 0;
}
if($action == "save_subscription"){
	$save = $crud->save_subscription();
	if($save) echo $save;
}
if($action == "delete_subscription"){
	$save = $crud->delete_subscription();
	if($save) echo $save;
}
