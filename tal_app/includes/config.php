<?php
$host = 'localhost';
$dbname = 'tal_report';
$username = 'root';
$password = '';

// Konfigurasi upload gambar
$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk log history
function logHistory($pdo, $task_id, $field, $old_value, $new_value) {
    $stmt = $pdo->prepare("INSERT INTO task_history (task_id, changed_field, old_value, new_value) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$task_id, $field, $old_value, $new_value]);
}

// Fungsi untuk mendapatkan history
function getTaskHistory($pdo, $task_id) {
    $stmt = $pdo->prepare("SELECT * FROM task_history WHERE task_id = ? ORDER BY changed_at DESC");
    $stmt->execute([$task_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>