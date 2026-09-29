<?php
// ============================================================
// Database Connection - LaundryPro Kenya
// Provides both mysqli (backward compat) and PDO (new code)
// Timezone Africa/Nairobi, Secure defaults
// ============================================================
date_default_timezone_set('Africa/Nairobi');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'laundry_db';

// --- mysqli connection (legacy, used by existing pages) ---
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Could not connect to mysql: " . $conn->connect_error);
}

// --- PDO connection (used by new modules) ---
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// --- Helper: Get PDO instance ---
if (!function_exists('db')) {
function db() {
    global $pdo;
    return $pdo;
}
}

// --- Helper: Fetch all rows ---
if (!function_exists('db_query')) {
function db_query($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
}
// --- Helper: Fetch single row ---
if (!function_exists('db_fetch')) {
function db_fetch($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}
}
// --- Helper: Insert & return last insert ID ---
if (!function_exists('db_insert')) {
function db_insert($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return db()->lastInsertId();
}
}
// --- Helper: Execute (update/delete) & return row count ---
if (!function_exists('db_execute')) {
function db_execute($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
}
