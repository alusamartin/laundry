<?php
// Hardened Action class - Estate Edition
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;
	private $pdo;

	public function __construct() {
		ob_start();
		include 'db_connect.php';
		$this->db = $conn;
		$this->pdo = $pdo ?? null;
		// Make the PDO available globally so db()/db_query() helpers work
		if ($this->pdo) $GLOBALS['pdo'] = $this->pdo;
		// Load security helpers if available
		if (file_exists(__DIR__.'/includes/security.php')) require_once __DIR__.'/includes/security.php';
	}
	function __destruct() {
		if ($this->db) $this->db->close();
		ob_end_flush();
	}

	// ============ AUTH ============
	function login(){
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		if ($username === '' || $password === '') return 3;
		$stmt = db()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
		$stmt->execute([$username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$user) return 3;
		$hash = $user['password'] ?? '';
		$valid = false;
		if (password_get_info($hash)['algo'] !== 0) {
			$valid = password_verify($password, $hash);
			if ($valid && password_needs_rehash($hash, PASSWORD_BCRYPT)) {
				$newHash = password_hash($password, PASSWORD_BCRYPT);
				db_execute("UPDATE users SET password=? WHERE id=?", [$newHash, $user['id']]);
			}
		} elseif ($hash === md5($password) || $hash === $password) {
			$valid = true;
			$newHash = password_hash($password, PASSWORD_BCRYPT);
			db_execute("UPDATE users SET password=? WHERE id=?", [$newHash, $user['id']]);
		}
		if ($valid) {
			session_regenerate_id(true);
			foreach ($user as $key => $value) {
				if ($key !== 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			$_SESSION['login_role'] = $user['role'] ?? $user['type'] ?? 2;
			return 1;
		}
		return 3;
	}
	function logout(){
		$_SESSION = [];
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		session_destroy();
		header("location:login.php");
		exit;
	}

	// ============ USERS (SECURE) ============
	function save_user(){
		$id = $_POST['id'] ?? '';
		$name = trim($_POST['name'] ?? '');
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		$type = intval($_POST['type'] ?? 2);
		$role = intval($_POST['role'] ?? 2);
		if ($name === '' || $username === '') return 0;
		try {
			if (empty($id)) {
				$hash = password_hash($password, PASSWORD_BCRYPT);
				db_insert("INSERT INTO users (name, username, password, type, role) VALUES (?,?,?,?,?)", [$name, $username, $hash, $type, $role]);
			} else {
				if ($password !== '') {
					$hash = password_hash($password, PASSWORD_BCRYPT);
					db_execute("UPDATE users SET name=?, username=?, password=?, type=?, role=? WHERE id=?", [$name, $username, $hash, $type, $role, $id]);
				} else {
					db_execute("UPDATE users SET name=?, username=?, type=?, role=? WHERE id=?", [$name, $username, $type, $role, $id]);
				}
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_user(){
		$id = intval($_POST['id'] ?? 0);
		if ($id === 1) return 0; // Prevent deleting super admin
		try { db_execute("DELETE FROM users WHERE id = ?", [$id]); return 1; } catch(Exception $e){ return 0; }
	}

	function save_settings(){
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$contact = trim($_POST['contact'] ?? '');
		$about = $_POST['about'] ?? '';
		$about_content = htmlentities(str_replace("'","&#x2019;",$about));
		$mpesa_consumer_key = trim($_POST['mpesa_consumer_key'] ?? '');
		$mpesa_consumer_secret = trim($_POST['mpesa_consumer_secret'] ?? '');
		$mpesa_passkey = trim($_POST['mpesa_passkey'] ?? '');
		$mpesa_shortcode = trim($_POST['mpesa_shortcode'] ?? '');
		$mpesa_environment = trim($_POST['mpesa_environment'] ?? 'sandbox');
		$sms_api_key = trim($_POST['sms_api_key'] ?? '');
		$sms_username = trim($_POST['sms_username'] ?? '');
		$currency = trim($_POST['currency'] ?? 'KES');
		$timezone = trim($_POST['timezone'] ?? 'Africa/Nairobi');
		$fname = null;
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
			$fname = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
			move_uploaded_file($_FILES['img']['tmp_name'],'../assets/img/'.$fname);
		}
		try {
			$chk = db_query("SELECT id FROM system_settings LIMIT 1");
			if(count($chk) > 0){
				$id = $chk[0]['id'];
				if($fname) db_execute("UPDATE system_settings SET name=?, email=?, contact=?, about_content=?, cover_img=?, mpesa_consumer_key=?, mpesa_consumer_secret=?, mpesa_passkey=?, mpesa_shortcode=?, mpesa_environment=?, sms_api_key=?, sms_username=?, currency=?, timezone=? WHERE id=?",
					[$name,$email,$contact,$about_content,$fname,$mpesa_consumer_key,$mpesa_consumer_secret,$mpesa_passkey,$mpesa_shortcode,$mpesa_environment,$sms_api_key,$sms_username,$currency,$timezone,$id]);
				else db_execute("UPDATE system_settings SET name=?, email=?, contact=?, about_content=?, mpesa_consumer_key=?, mpesa_consumer_secret=?, mpesa_passkey=?, mpesa_shortcode=?, mpesa_environment=?, sms_api_key=?, sms_username=?, currency=?, timezone=? WHERE id=?",
					[$name,$email,$contact,$about_content,$mpesa_consumer_key,$mpesa_consumer_secret,$mpesa_passkey,$mpesa_shortcode,$mpesa_environment,$sms_api_key,$sms_username,$currency,$timezone,$id]);
			}else{
				db_insert("INSERT INTO system_settings (name,email,contact,about_content,cover_img,mpesa_consumer_key,mpesa_consumer_secret,mpesa_passkey,mpesa_shortcode,mpesa_environment,sms_api_key,sms_username,currency,timezone) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
					[$name,$email,$contact,$about_content,$fname,$mpesa_consumer_key,$mpesa_consumer_secret,$mpesa_passkey,$mpesa_shortcode,$mpesa_environment,$sms_api_key,$sms_username,$currency,$timezone]);
			}
			$q = db_query("SELECT * FROM system_settings LIMIT 1");
			if(count($q)>0) foreach($q[0] as $k=>$v) if(!is_numeric($k)) $_SESSION['setting_'.$k] = $v;
			return 1;
		} catch(Exception $e){ return 0; }
	}

	// ============ CATEGORIES (SECURE) ============
	function save_category(){
		$id = $_POST['id'] ?? '';
		$name = trim($_POST['name'] ?? '');
		$price = floatval($_POST['price'] ?? 0);
		$pricing_type = trim($_POST['pricing_type'] ?? 'per_kg');
		$express_surcharge = floatval($_POST['express_surcharge'] ?? 0);
		if($name==='') return 0;
		try {
			if(empty($id)) db_insert("INSERT INTO laundry_categories (name,price,pricing_type,express_surcharge) VALUES (?,?,?,?)", [$name,$price,$pricing_type,$express_surcharge]);
			else db_execute("UPDATE laundry_categories SET name=?, price=?, pricing_type=?, express_surcharge=? WHERE id=?", [$name,$price,$pricing_type,$express_surcharge,$id]);
			return 1;
		} catch(Exception $e){ return 0; }
	}
	function delete_category(){
		$id = intval($_POST['id'] ?? 0);
		try { db_execute("DELETE FROM laundry_categories WHERE id=?", [$id]); return 1; } catch(Exception $e){ return 0; }
	}

	// ============ SUPPLY (SECURE) ============
	function save_supply(){
		$id = $_POST['id'] ?? '';
		$name = trim($_POST['name'] ?? '');
		if($name==='') return 0;
		try {
			if(empty($id)) db_insert("INSERT INTO supply_list (name) VALUES (?)", [$name]);
			else db_execute("UPDATE supply_list SET name=? WHERE id=?", [$name,$id]);
			return 1;
		} catch(Exception $e){ return 0; }
	}
	function delete_supply(){
		$id = intval($_POST['id'] ?? 0);
		try { db_execute("DELETE FROM supply_list WHERE id=?", [$id]); return 1; } catch(Exception $e){ return 0; }
	}

	// ============ ORDERS (LAUNDRY) SECURE - WITH TRANSACTIONS & GATE PIN ============
	function save_laundry(){
		$id = $_POST['id'] ?? '';
		$customer_name = trim($_POST['customer_name'] ?? '');
		$customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
		$estate_id = !empty($_POST['estate_id']) ? intval($_POST['estate_id']) : null;
		$remarks = trim($_POST['remarks'] ?? '');
		$tamount = floatval($_POST['tamount'] ?? 0);
		$tendered = floatval($_POST['tendered'] ?? 0);
		$change = floatval($_POST['change'] ?? 0);
		$is_express = isset($_POST['is_express']) ? 1 : 0;
		$service_type = trim($_POST['service_type'] ?? 'wash_fold');
		$pickup_location = trim($_POST['pickup_location'] ?? '');
		$delivery_location = trim($_POST['delivery_location'] ?? '');
		$pickup_date = !empty($_POST['pickup_date']) ? $_POST['pickup_date'] : null;
		$pickup_time_slot = trim($_POST['pickup_time_slot'] ?? 'morning');
		$driver_id = !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : null;
		$gate_pass_notes = trim($_POST['gate_pass_notes'] ?? '');
		$payment_method = trim($_POST['payment_method'] ?? 'cash');
		$mpesa_code = trim($_POST['mpesa_transaction_code'] ?? '');
		$pay_status = isset($_POST['pay']) ? 1 : 0;
		$status = isset($_POST['status']) ? intval($_POST['status']) : null;
		$gate_pin = trim($_POST['gate_pin'] ?? '');
		if ($estate_id && empty($gate_pin) && empty($id)) $gate_pin = str_pad(random_int(0,9999),4,'0',STR_PAD_LEFT);
		$weight = $_POST['weight'] ?? [];
		$laundry_category_id = $_POST['laundry_category_id'] ?? [];
		$unit_price = $_POST['unit_price'] ?? [];
		$amount = $_POST['amount'] ?? [];
		$item_id = $_POST['item_id'] ?? [];
		if (empty($weight) || count($weight)==0) return 0;
		$pdo = db();
		try {
			$pdo->beginTransaction();
			if (empty($id)) {
				// Atomic queue - transaction-safe (InnoDB)
				$stmtQ = $pdo->prepare("SELECT COALESCE(MAX(queue),0)+1 as nq FROM laundry_list WHERE status != 7 FOR UPDATE");
				$stmtQ->execute();
				$q = $stmtQ->fetch();
				$queue = $q['nq'] ?? 1;
				$sql = "INSERT INTO laundry_list (customer_name, customer_id, estate_id, remarks, total_amount, amount_tendered, amount_change, is_express, service_type, pickup_location, delivery_location, pickup_date, pickup_time_slot, driver_id, gate_pass_notes, gate_pin, payment_method, mpesa_transaction_code, pay_status, status, queue) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([$customer_name,$customer_id,$estate_id,$remarks,$tamount,$tendered,$change,$is_express,$service_type,$pickup_location,$delivery_location,$pickup_date,$pickup_time_slot,$driver_id,$gate_pass_notes,$gate_pin,$payment_method,$mpesa_code,$pay_status, $status ?? 0, $queue]);
				$newId = $pdo->lastInsertId();
				foreach ($weight as $key=>$val) {
					$w = floatval($val);
					$cid = intval($laundry_category_id[$key] ?? 0);
					$up = floatval($unit_price[$key] ?? 0);
					$am = floatval($amount[$key] ?? ($w*$up));
					$qr = 'LP'.str_pad($newId,6,'0',STR_PAD_LEFT).'-'.str_pad($cid,3,'0',STR_PAD_LEFT).'-'.bin2hex(random_bytes(2));
					$pdo->prepare("INSERT INTO laundry_items (laundry_id, laundry_category_id, weight, unit_price, amount, qr_code) VALUES (?,?,?,?,?,?)")->execute([$newId,$cid,$w,$up,$am,$qr]);
				}
				$sv = $status ?? 0;
				$pdo->prepare("INSERT INTO order_status_history (order_id, status, changed_by) VALUES (?,?,?)")->execute([$newId,$sv,$_SESSION['login_id'] ?? null]);
				// Auto commission
				if ($estate_id && $tamount>0) {
					$est = db_query("SELECT contract_rate FROM estates WHERE id=?", [$estate_id]);
					if (count($est)>0 && $est[0]['contract_rate']>0) {
						$rate=$est[0]['contract_rate']; $comm=$tamount*$rate/100;
						db_insert("INSERT INTO estate_commissions (estate_id, order_id, gross_amount, commission_rate, commission_amount) VALUES (?,?,?,?,?)", [$estate_id,$newId,$tamount,$rate,$comm]);
					}
				}
				// Notify via SMS if configured
				if (file_exists(__DIR__.'/includes/sms.php') && $customer_id) {
					try { require_once __DIR__.'/includes/sms.php'; $c=db_query("SELECT phone,name FROM customers WHERE id=?",[$customer_id]); if(count($c)>0) send_status_sms($c[0]['phone'], $newId, 0, $c[0]['name']); } catch(Exception $e){}
				}
				$pdo->commit();
				return 1;
			} else {
				$id = intval($id);
				$sql = "UPDATE laundry_list SET customer_name=?, customer_id=?, estate_id=?, remarks=?, total_amount=?, amount_tendered=?, amount_change=?, is_express=?, service_type=?, pickup_location=?, delivery_location=?, pickup_date=?, pickup_time_slot=?, driver_id=?, gate_pass_notes=?, gate_pin=?, payment_method=?, mpesa_transaction_code=?, pay_status=? ".($status!==null?", status=?":"")." WHERE id=?";
				$params = [$customer_name,$customer_id,$estate_id,$remarks,$tamount,$tendered,$change,$is_express,$service_type,$pickup_location,$delivery_location,$pickup_date,$pickup_time_slot,$driver_id,$gate_pass_notes,$gate_pin,$payment_method,$mpesa_code,$pay_status];
				if ($status!==null) $params[]=$status;
				$params[]=$id;
				$pdo->prepare($sql)->execute($params);
				if ($status!==null) $pdo->prepare("INSERT INTO order_status_history (order_id, status, changed_by) VALUES (?,?,?)")->execute([$id,$status,$_SESSION['login_id'] ?? null]);
				$keep=[]; foreach ($weight as $k=>$v){
					$w=floatval($v); $cid=intval($laundry_category_id[$k]??0); $up=floatval($unit_price[$k]??0); $am=floatval($amount[$k]??($w*$up));
					if(empty($item_id[$k])){ $pdo->prepare("INSERT INTO laundry_items (laundry_id, laundry_category_id, weight, unit_price, amount, qr_code) VALUES (?,?,?,?,?,?)")->execute([$id,$cid,$w,$up,$am,'LP'.str_pad($id,6,'0',STR_PAD_LEFT).'-'.bin2hex(random_bytes(2))]); $keep[]=$pdo->lastInsertId(); }
					else { $iid=intval($item_id[$k]); $pdo->prepare("UPDATE laundry_items SET laundry_category_id=?, weight=?, unit_price=?, amount=? WHERE id=? AND laundry_id=?")->execute([$cid,$w,$up,$am,$iid,$id]); $keep[]=$iid; }
				}
				if(count($keep)>0){ $placeholders=implode(',',array_fill(0,count($keep),'?')); $params=array_merge($keep,[$id]); $pdo->prepare("DELETE FROM laundry_items WHERE laundry_id=? AND id NOT IN ($placeholders)")->execute($params); }
				if ($status!==null && file_exists(__DIR__.'/includes/sms.php') && $customer_id){
					try { require_once __DIR__.'/includes/sms.php'; $c=db_query("SELECT phone,name FROM customers WHERE id=?",[$customer_id]); if(count($c)>0) send_status_sms($c[0]['phone'],$id,$status,$c[0]['name']); } catch(Exception $e){}
				}
				$pdo->commit();
				return 2;
			}
		} catch(Exception $e){ if($pdo->inTransaction()) $pdo->rollBack(); error_log("save_laundry: ".$e->getMessage()); return 0; }
	}

	function delete_laundry(){
		$id = intval($_POST['id'] ?? 0);
		try { db()->beginTransaction(); db_execute("DELETE FROM laundry_items WHERE laundry_id=?",[$id]); db_execute("DELETE FROM laundry_list WHERE id=?",[$id]); db()->commit(); return 1; } catch(Exception $e){ if(db()->inTransaction()) db()->rollBack(); return 0; }
	}

	// ============ INVENTORY (SECURE) ============
	function save_inv(){
		$id = $_POST['id'] ?? '';
		$supply_id = intval($_POST['supply_id'] ?? 0);
		$qty = intval($_POST['qty'] ?? 0);
		$stock_type = intval($_POST['stock_type'] ?? 1);
		try {
			if(empty($id)) db_insert("INSERT INTO inventory (supply_id, qty, stock_type) VALUES (?,?,?)", [$supply_id,$qty,$stock_type]);
			else db_execute("UPDATE inventory SET supply_id=?, qty=?, stock_type=? WHERE id=?", [$supply_id,$qty,$stock_type,$id]);
			return 1;
		} catch(Exception $e){ return 0; }
	}
	function delete_inv(){
		$id = intval($_POST['id'] ?? 0);
		try { db_execute("DELETE FROM inventory WHERE id=?",[$id]); return 1; } catch(Exception $e){ return 0; }
	}

	// ============ ESTATES ============
	function save_estate(){
		extract($_POST);
		try {
			$id = isset($id) ? $id : '';
			if(empty($id)){
				db_insert("INSERT INTO estates (name, location, city, contact_person, phone, email, gate_access_notes, contract_rate, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
					[$name, $location, $city, $contact_person, $phone, $email, $gate_access_notes, $contract_rate, $is_active]);
			} else {
				db_execute("UPDATE estates SET name=?, location=?, city=?, contact_person=?, phone=?, email=?, gate_access_notes=?, contract_rate=?, is_active=? WHERE id=?",
					[$name, $location, $city, $contact_person, $phone, $email, $gate_access_notes, $contract_rate, $is_active, $id]);
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_estate(){
		extract($_POST);
		try {
			db_execute("DELETE FROM estates WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function save_block(){
		extract($_POST);
		try {
			db_insert("INSERT INTO estate_blocks (estate_id, name) VALUES (?, ?)", [$estate_id, $name]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_block(){
		extract($_POST);
		try {
			db_execute("DELETE FROM estate_blocks WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function save_unit(){
		extract($_POST);
		try {
			db_insert("INSERT INTO estate_units (block_id, unit_number, floor) VALUES (?, ?, ?)", [$block_id, $unit_number, $floor]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_unit(){
		extract($_POST);
		try {
			db_execute("DELETE FROM estate_units WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function get_blocks(){
		extract($_POST);
		$blocks = db_query("SELECT id, name FROM estate_blocks WHERE estate_id = ? ORDER BY name ASC", [$estate_id]);
		echo json_encode($blocks);
	}
	function get_units(){
		extract($_POST);
		$units = db_query("SELECT id, unit_number, floor FROM estate_units WHERE block_id = ? ORDER BY unit_number ASC", [$block_id]);
		echo json_encode($units);
	}

	// ============ CUSTOMERS ============
	function save_customer(){
		extract($_POST);
		try {
			if(empty($id)){
				db_insert("INSERT INTO customers (name, phone, email, national_id, customer_type, estate_id, block_id, unit_id, preferred_pickup_location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					[$name, $phone, $email, $national_id, $customer_type, $estate_id ?: null, $block_id ?: null, $unit_id ?: null, $preferred_pickup_location, $notes]);
			} else {
				db_execute("UPDATE customers SET name=?, phone=?, email=?, national_id=?, customer_type=?, estate_id=?, block_id=?, unit_id=?, preferred_pickup_location=?, notes=? WHERE id=?",
					[$name, $phone, $email, $national_id, $customer_type, $estate_id ?: null, $block_id ?: null, $unit_id ?: null, $preferred_pickup_location, $notes, $id]);
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_customer(){
		extract($_POST);
		try {
			db_execute("DELETE FROM customers WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}

	// ============ EXPENSES ============
	function save_expense(){
		extract($_POST);
		try {
			if(empty($id)){
				db_insert("INSERT INTO expenses (expense_type, amount, description, receipt_no, entered_by, date_incurred) VALUES (?, ?, ?, ?, ?, ?)",
					[$expense_type, $amount, $description, $receipt_no, $_SESSION['login_id'], $date_incurred]);
			} else {
				db_execute("UPDATE expenses SET expense_type=?, amount=?, description=?, receipt_no=?, date_incurred=? WHERE id=?",
					[$expense_type, $amount, $description, $receipt_no, $date_incurred, $id]);
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_expense(){
		extract($_POST);
		try {
			db_execute("DELETE FROM expenses WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}

	// ============ DRIVERS ============
	function save_driver(){
		extract($_POST);
		try {
			if(empty($id)){
				db_insert("INSERT INTO drivers (name, phone, email, national_id, vehicle_type, vehicle_reg, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)",
					[$name, $phone, $email, $national_id, $vehicle_type, $vehicle_reg, $is_active]);
			} else {
				db_execute("UPDATE drivers SET name=?, phone=?, email=?, national_id=?, vehicle_type=?, vehicle_reg=?, is_active=? WHERE id=?",
					[$name, $phone, $email, $national_id, $vehicle_type, $vehicle_reg, $is_active, $id]);
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_driver(){
		extract($_POST);
		try {
			db_execute("DELETE FROM drivers WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}

	// ============ PICKUP SCHEDULE ============
	function save_schedule(){
		extract($_POST);
		try {
			if(empty($id)){
				db_insert("INSERT INTO pickup_schedule (order_id, customer_id, estate_id, schedule_type, scheduled_date, time_slot, driver_id, status, notes, assigned_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					[$order_id ?: null, $customer_id ?: null, $estate_id ?: null, $schedule_type, $scheduled_date, $time_slot, $driver_id ?: null, $status ?: 0, $notes, $_SESSION['login_id']]);
			} else {
				db_execute("UPDATE pickup_schedule SET order_id=?, customer_id=?, estate_id=?, schedule_type=?, scheduled_date=?, time_slot=?, driver_id=?, status=?, notes=? WHERE id=?",
					[$order_id ?: null, $customer_id ?: null, $estate_id ?: null, $schedule_type, $scheduled_date, $time_slot, $driver_id ?: null, $status, $notes, $id]);
			}
			return 1;
		} catch(Exception $e) { return 0; }
	}
	function delete_schedule(){
		extract($_POST);
		try {
			db_execute("DELETE FROM pickup_schedule WHERE id = ?", [$id]);
			return 1;
		} catch(Exception $e) { return 0; }
	}
	// ============ SUBSCRIPTIONS ============
	function save_subscription(){
		$id = $_POST['id'] ?? '';
		$customer_id = intval($_POST['customer_id'] ?? 0);
		$estate_id = intval($_POST['estate_id'] ?? 0);
		$plan_name = trim($_POST['plan_name'] ?? '');
		$monthly_fee = floatval($_POST['monthly_fee'] ?? 0);
		$washes_per_month = intval($_POST['washes_per_month'] ?? 4);
		$billing_day = intval($_POST['billing_day'] ?? 1);
		$status = trim($_POST['status'] ?? 'active');
		if(!$customer_id || !$estate_id) return 0;
		try {
			if(empty($id)) db_insert("INSERT INTO estate_subscriptions (customer_id, estate_id, plan_name, monthly_fee, washes_per_month, washes_remaining, billing_day, status) VALUES (?,?,?,?,?,?,?,?)", [$customer_id,$estate_id,$plan_name,$monthly_fee,$washes_per_month,$washes_per_month,$billing_day,$status]);
			else db_execute("UPDATE estate_subscriptions SET customer_id=?, estate_id=?, plan_name=?, monthly_fee=?, washes_per_month=?, billing_day=?, status=? WHERE id=?", [$customer_id,$estate_id,$plan_name,$monthly_fee,$washes_per_month,$billing_day,$status,$id]);
			return 1;
		} catch(Exception $e){ return 0; }
	}
	function delete_subscription(){
		$id = intval($_POST['id'] ?? 0);
		try { db_execute("DELETE FROM estate_subscriptions WHERE id=?", [$id]); return 1; } catch(Exception $e){ return 0; }
	}
}
