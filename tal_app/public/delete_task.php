<?php
require_once __DIR__ . '/../includes/config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;