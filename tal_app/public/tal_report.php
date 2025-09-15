<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /tal_app/public/login.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$tasks = [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';

    if (!$start || !$end) {
        $error = "Tanggal mulai dan selesai wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE status = 'completed' AND due_date BETWEEN ? AND ? ORDER BY due_date ASC");
        $stmt->execute([$start, $end]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Report Laporan TAL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Report Laporan TAL</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" name="start_date" id="start_date" required value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" name="end_date" id="end_date" required value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <h5>Hasil Laporan:</h5>
        <?php if (count($tasks) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Tugas</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Tugas</th>
                        <th>Tanggal Selesai</th>
                        <th>Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['task_name']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td>
                                <?php
                                    // Tanggal Tugas + Jam
                                    if (!empty($task['created_at'])) {
                                        echo date('d-m-Y H:i', strtotime($task['created_at']));
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    // Tanggal Selesai + Jam
                                    if (!empty($task['due_date'])) {
                                        echo date('d-m-Y H:i', strtotime($task['due_date']));
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($task['priority']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada tugas selesai pada rentang tanggal tersebut.</div>
        <?php endif; ?>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
</div>
</body>
</html>