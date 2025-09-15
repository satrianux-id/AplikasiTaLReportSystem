<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?: null;
    $image_path = $task['image_path'];
    
    // Handle upload gambar baru
    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['task_image']['tmp_name'];
        $file_type = $_FILES['task_image']['type'];
        $file_size = $_FILES['task_image']['size'];
        
        // Validasi tipe file
        if (in_array($file_type, $allowed_types)) {
            // Validasi ukuran file
            if ($file_size <= $max_size) {
                // Hapus gambar lama jika ada
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                
                // Generate nama unik untuk file baru
                $file_extension = pathinfo($_FILES['task_image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('task_', true) . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                // Pindahkan file ke folder uploads
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $image_path = $file_name; // hanya nama file
                    logHistory($pdo, $id, 'image_changed', $task['image_path'], $image_path);
                }
            }
        }
    }
    
    // Log perubahan untuk setiap field
    if ($task['task_name'] !== $task_name) {
        logHistory($pdo, $id, 'task_name', $task['task_name'], $task_name);
    }
    
    if ($task['description'] !== $description) {
        logHistory($pdo, $id, 'description', $task['description'], $description);
    }
    
    if ($task['status'] !== $status) {
        logHistory($pdo, $id, 'status', $task['status'], $status);
    }
    
    if ($task['priority'] !== $priority) {
        logHistory($pdo, $id, 'priority', $task['priority'], $priority);
    }
    
    if ($task['due_date'] != $due_date) {
        logHistory($pdo, $id, 'due_date', $task['due_date'], $due_date);
    }
    
    $stmt = $pdo->prepare("UPDATE tasks SET task_name = ?, description = ?, status = ?, 
                           priority = ?, due_date = ?, image_path = ? WHERE id = ?");
    $stmt->execute([$task_name, $description, $status, $priority, $due_date, $image_path, $id]);
    
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - TAL Report</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center">Edit Tugas</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="task_name" class="form-label">Nama Tugas</label>
                                <input type="text" class="form-control" id="task_name" name="task_name" 
                                       value="<?= htmlspecialchars($task['task_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($task['description']) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" onchange="togglePhotoUpload()">
                                        <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Prioritas</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                        <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Tanggal Jatuh Tempo (opsional)</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       value="<?= $task['due_date'] ?>">
                            </div>
                            <div class="mb-3" id="photo-upload-section" style="display: <?= $task['status'] === 'completed' ? 'block' : 'none' ?>;">
                                <label for="task_image" class="form-label">Upload Foto Penyelesaian</label>
                                <?php if ($task['image_path']): ?>
                                    <div class="mb-2">
                                        <img src="<?= htmlspecialchars($task['image_path']) ?>" alt="Task Image" width="120">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="task_image" name="task_image" accept="image/*">
                                <small class="text-muted">Hanya bisa upload saat tugas selesai.</small>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Tugas</button>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function togglePhotoUpload() {
        var status = document.getElementById('status').value;
        var section = document.getElementById('photo-upload-section');
        section.style.display = (status === 'completed') ? 'block' : 'none';
    }
    </script>
</body>
</html>