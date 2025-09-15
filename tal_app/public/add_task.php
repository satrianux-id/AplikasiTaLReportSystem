<?php
require_once __DIR__ . '/../includes/config.php';

// Inisialisasi variabel
$error = '';
$success = '';

// Konfigurasi upload gambar
$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Buat folder uploads jika belum ada
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $gambar = null;

    // Proses upload gambar
    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['task_image']['tmp_name'];
        $file_name = $_FILES['task_image']['name'];
        $file_type = $_FILES['task_image']['type'];
        $file_size = $_FILES['task_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed_ext) && in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $unique_name = uniqid('task_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $unique_name;
            if (move_uploaded_file($file_tmp, $file_path)) {
                $gambar = $unique_name; // Simpan nama file saja ke kolom gambar
            } else {
                $error = 'Gagal upload gambar.';
            }
        } else {
            $error = 'Format atau ukuran gambar tidak valid.';
        }
    }

    // Simpan ke database
    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (task_name, description, status, priority, due_date, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$task_name, $description, $status, $priority, $due_date, $gambar]);
        $success = 'Tugas berhasil ditambahkan!';
    }
}

// Fungsi untuk mendapatkan pesan error upload
function getUploadError($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi ukuran yang diizinkan server)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi ukuran yang diizinkan form)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
    ];
    
    return $errors[$error_code] ?? 'Unknown error';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas Baru - TAL Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #4895ef;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.6rem 1rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }
        
        .upload-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center mb-0">Tambah Tugas Baru</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="task_name" class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="task_name" name="task_name" 
                                       value="<?= htmlspecialchars($task_name ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending" <?= ($status ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= ($status ?? 'pending') === 'in_progress' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                                        <option value="completed" <?= ($status ?? 'pending') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Prioritas</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="low" <?= ($priority ?? 'medium') === 'low' ? 'selected' : '' ?>>Rendah</option>
                                        <option value="medium" <?= ($priority ?? 'medium') === 'medium' ? 'selected' : '' ?>>Sedang</option>
                                        <option value="high" <?= ($priority ?? 'medium') === 'high' ? 'selected' : '' ?>>Tinggi</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Tanggal Jatuh Tempo (opsional)</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       value="<?= htmlspecialchars($due_date ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="task_image" class="form-label">Lampiran Gambar (opsional)</label>
                                <input type="file" class="form-control" id="task_image" name="task_image" accept="image/*">
                                <div class="upload-info">
                                    Format yang didukung: JPEG, PNG, GIF, WebP. Maksimal 5MB.
                                </div>
                                <img id="imagePreview" class="preview-image" src="#" alt="Preview gambar">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Tugas
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tugas
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview gambar sebelum upload
        document.getElementById('task_image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const taskName = document.getElementById('task_name').value.trim();
            const fileInput = document.getElementById('task_image');
            const file = fileInput.files[0];
            
            if (!taskName) {
                e.preventDefault();
                alert('Nama tugas harus diisi!');
                document.getElementById('task_name').focus();
                return;
            }
            
            if (file) {
                // Validasi ukuran file (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran gambar terlalu besar. Maksimal 5MB.');
                    fileInput.value = '';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Tipe file tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.');
                    fileInput.value = '';
                    return;
                }
            }
        });
    </script>
</body>
</html>