<?php
require_once __DIR__ . '/../includes/config.php';

// Ambil data tasks
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY 
    FIELD(priority, 'high', 'medium', 'low'), 
    FIELD(status, 'pending', 'in_progress', 'completed'),
    due_date ASC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total_tasks = count($tasks);
$completed_tasks = count(array_filter($tasks, function($task) {
    return $task['status'] === 'completed';
}));
$in_progress_tasks = count(array_filter($tasks, function($task) {
    return $task['status'] === 'in_progress';
}));
$pending_tasks = count(array_filter($tasks, function($task) {
    return $task['status'] === 'pending';
}));

// Fungsi untuk mendapatkan nama status yang lebih baik
function getStatusText($status) {
    $statuses = [
        'pending' => 'Pending',
        'in_progress' => 'Sedang Dikerjakan',
        'completed' => 'Selesai'
    ];
    return $statuses[$status] ?? $status;
}

// Fungsi untuk mendapatkan nama prioritas yang lebih baik
function getPriorityText($priority) {
    $priorities = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi'
    ];
    return $priorities[$priority] ?? $priority;
}

// Fungsi untuk mendapatkan class badge berdasarkan status
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-warning',
        'in_progress' => 'bg-info',
        'completed' => 'bg-success'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

// Fungsi untuk mendapatkan class badge berdasarkan prioritas
function getPriorityBadgeClass($priority) {
    $classes = [
        'low' => 'bg-success',
        'medium' => 'bg-primary',
        'high' => 'bg-danger'
    ];
    return $classes[$priority] ?? 'bg-secondary';
}

// Pisahkan tasks berdasarkan status untuk tampilan tabel
$pending_tasks_list = array_filter($tasks, function($task) {
    return $task['status'] === 'pending';
});

$in_progress_tasks_list = array_filter($tasks, function($task) {
    return $task['status'] === 'in_progress';
});

$completed_tasks_list = array_filter($tasks, function($task) {
    return $task['status'] === 'completed';
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi TAL Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            min-height: 100vh;
            padding: 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar h1 {
            font-weight: 700;
            font-size: 1.8rem;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stats {
            padding: 1.5rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.8rem;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .stat-value {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .stat-value.completed {
            color: #4ade80;
        }

        .stat-value.progress {
            color: #60a5fa;
        }

        .stat-value.pending {
            color: #f87171;
        }

        .btn-add-task {
            width: 100%;
            padding: 0.8rem;
            margin-top: 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .main-content {
            padding: 2rem;
        }

        .tasks-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .task-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.2rem;
            border-bottom: 1px solid #eee;
        }

        .task-title {
            font-weight: 600;
            margin: 0;
            flex: 1;
            margin-right: 1rem;
        }

        .priority-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .priority-high {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .priority-medium {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        .priority-low {
            background-color: #dcfce7;
            color: #22c55e;
        }

        .task-body {
            padding: 1.2rem;
        }

        .task-description {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .task-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-status {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .status-pending {
            background-color: #f59e0b;
        }

        .status-in_progress {
            background-color: #3b82f6;
        }

        .status-completed {
            background-color: #10b981;
        }

        .task-due {
            font-size: 0.9rem;
            color: #64748b;
            display: flex;
            align-items: center;
        }

        .task-footer {
            padding: 1rem 1.2rem;
            background-color: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .task-image img {
            border: 1px solid #ddd;
            padding: 4px;
            background: white;
            max-height: 200px;
            width: auto;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        /* Tabel Monitoring */
        .monitoring-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .monitoring-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .monitoring-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .task-image-table {
            max-width: 80px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .description-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding-bottom: 2rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .tasks-container {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .description-cell {
                max-width: 150px;
            }
        }

        /* Filter styles */
        .form-select, .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.6rem 1rem;
        }

        /* Button styles */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .tab-content {
            padding: 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h1 class="text-center mt-3">TAL Report</h1>
                <div class="text-center mb-4">
                    <i class="fas fa-tasks fa-3x"></i>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Tugas</span>
                        <span class="stat-value"><?= $total_tasks ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Selesai</span>
                        <span class="stat-value completed"><?= $completed_tasks ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Progress</span>
                        <span class="stat-value progress"><?= $in_progress_tasks ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value pending"><?= $pending_tasks ?></span>
                    </div>
                </div>
<div class="text-center">
                    <a href="public.php" class="btn btn-primary btn-dahboard mb-3">
                        <i class="fas fa-plus"></i> Dashboard Monitoring
                    </a>
                </div>

                <div class="text-center">
                    <a href="add_task.php" class="btn btn-primary btn-add-task">
                        <i class="fas fa-plus"></i> Tambah Tugas Baru
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h2 class="mt-3">Daftar Tugas</h2>
                
                <!-- Filter Options -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="priorityFilter" class="form-select">
                            <option value="">Semua Prioritas</option>
                            <option value="high">Tinggi</option>
                            <option value="medium">Sedang</option>
                            <option value="low">Rendah</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari tugas...">
                    </div>
                </div>

                <!-- Tabs untuk Monitoring -->
                <ul class="nav nav-pills mb-3" id="viewTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="card-view-tab" data-bs-toggle="pill" data-bs-target="#card-view" type="button" role="tab">
                            <i class="fas fa-th-large"></i> Tampilan Kartu
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="table-view-tab" data-bs-toggle="pill" data-bs-target="#table-view" type="button" role="tab">
                            <i class="fas fa-table"></i> Tampilan Tabel (Monitoring)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="viewTabsContent">
                    <!-- Tampilan Kartu -->
                    <div class="tab-pane fade show active" id="card-view" role="tabpanel">
                        <div class="tasks-container">
                            <?php if (count($tasks) > 0): ?>
                                <?php foreach ($tasks as $task): ?>
                                    <div class="task-card" data-status="<?= $task['status'] ?>" data-priority="<?= $task['priority'] ?>">
                                        <div class="task-header">
                                            <h5 class="task-title"><?= htmlspecialchars($task['task_name']) ?></h5>
                                            <span class="priority-badge priority-<?= $task['priority'] ?>">
                                                <?= getPriorityText($task['priority']) ?>
                                            </span>
                                        </div>
                                        <div class="task-body">
                                            <?php if (!empty($task['gambar'])): ?>
                                                <div class="task-image">
                                                    <img src="/tal_app/public/uploads/<?= htmlspecialchars($task['gambar']) ?>" alt="Lampiran tugas" class="img-fluid">
                                                </div>
                                            <?php endif; ?>
                                            <p class="task-description"><?= htmlspecialchars($task['description']) ?></p>
                                            <div class="task-details">
                                                <div class="task-status">
                                                    <span class="status-indicator status-<?= $task['status'] ?>"></span>
                                                    <?= getStatusText($task['status']) ?>
                                                </div>
                                                <?php if ($task['due_date']): ?>
                                                    <div class="task-due">
                                                        <i class="far fa-calendar-alt"></i>
                                                        <?= date('d M Y', strtotime($task['due_date'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="task-footer">
                                            <a href="view_history.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-history"></i> History
                                            </a>
                                            <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada tugas. Silakan tambah tugas baru.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tampilan Tabel untuk Monitoring -->
                    <div class="tab-pane fade" id="table-view" role="tabpanel">
                        <!-- Tabel Pending -->
                        <div class="monitoring-section">
                            <div class="monitoring-header">
                                <h3 class="monitoring-title">Tugas Pending</h3>
                                <span class="badge bg-warning"><?= count($pending_tasks_list) ?> Tugas</span>
                            </div>
                            <?php if (count($pending_tasks_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Tugas</th>
                                                <th>Deskripsi</th>
                                                <th>Prioritas</th>
                                                <th>Tanggal Jatuh Tempo</th>
                                                <th>Gambar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_tasks_list as $task): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                                                    <td class="description-cell" title="<?= htmlspecialchars($task['description']) ?>">
                                                        <?= htmlspecialchars($task['description']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= getPriorityBadgeClass($task['priority']) ?>">
                                                            <?= getPriorityText($task['priority']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($task['gambar'])): ?>
                                                            <img src="/tal_app/public/uploads/<?= htmlspecialchars($task['gambar']) ?>" alt="Lampiran" class="task-image-table">
                                                        <?php else: ?>
                                                            <span class="text-muted">Tidak ada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="view_history.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-info" title="Lihat History">
                                                            <i class="fas fa-history"></i>
                                                        </a>
                                                        <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Tidak ada tugas pending.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tabel Sedang Dikerjakan -->
                        <div class="monitoring-section">
                            <div class="monitoring-header">
                                <h3 class="monitoring-title">Tugas Sedang Dikerjakan</h3>
                                <span class="badge bg-info"><?= count($in_progress_tasks_list) ?> Tugas</span>
                            </div>
                            <?php if (count($in_progress_tasks_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Tugas</th>
                                                <th>Deskripsi</th>
                                                <th>Prioritas</th>
                                                <th>Tanggal Jatuh Tempo</th>
                                                <th>Gambar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($in_progress_tasks_list as $task): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                                                    <td class="description-cell" title="<?= htmlspecialchars($task['description']) ?>">
                                                        <?= htmlspecialchars($task['description']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= getPriorityBadgeClass($task['priority']) ?>">
                                                            <?= getPriorityText($task['priority']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($task['gambar'])): ?>
                                                            <img src="/tal_app/public/uploads/<?= htmlspecialchars($task['gambar']) ?>" alt="Lampiran" class="task-image-table">
                                                        <?php else: ?>
                                                            <span class="text-muted">Tidak ada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="view_history.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-info" title="Lihat History">
                                                            <i class="fas fa-history"></i>
                                                        </a>
                                                        <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Tidak ada tugas yang sedang dikerjakan.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tabel Selesai -->
                        <div class="monitoring-section">
                            <div class="monitoring-header">
                                <h3 class="monitoring-title">Tugas Selesai</h3>
                                <span class="badge bg-success"><?= count($completed_tasks_list) ?> Tugas</span>
                            </div>
                            <?php if (count($completed_tasks_list) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Tugas</th>
                                                <th>Deskripsi</th>
                                                <th>Prioritas</th>
                                                <th>Tanggal Jatuh Tempo</th>
                                                <th>Gambar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($completed_tasks_list as $task): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                                                    <td class="description-cell" title="<?= htmlspecialchars($task['description']) ?>">
                                                        <?= htmlspecialchars($task['description']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= getPriorityBadgeClass($task['priority']) ?>">
                                                            <?= getPriorityText($task['priority']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?= $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($task['gambar'])): ?>
                                                            <img src="/tal_app/public/uploads/<?= htmlspecialchars($task['gambar']) ?>" alt="Lampiran" class="task-image-table">
                                                        <?php else: ?>
                                                            <span class="text-muted">Tidak ada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="view_history.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-info" title="Lihat History">
                                                            <i class="fas fa-history"></i>
                                                        </a>
                                                        <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Tidak ada tugas yang selesai.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality untuk tampilan kartu
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const priorityFilter = document.getElementById('priorityFilter');
            const searchInput = document.getElementById('searchInput');
            const taskCards = document.querySelectorAll('.task-card');
            
            function filterTasks() {
                const statusValue = statusFilter.value;
                const priorityValue = priorityFilter.value;
                const searchValue = searchInput.value.toLowerCase();
                
                taskCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    const priority = card.getAttribute('data-priority');
                    const title = card.querySelector('.task-title').textContent.toLowerCase();
                    const description = card.querySelector('.task-description').textContent.toLowerCase();
                    
                    const statusMatch = !statusValue || status === statusValue;
                    const priorityMatch = !priorityValue || priority === priorityValue;
                    const searchMatch = !searchValue || title.includes(searchValue) || description.includes(searchValue);
                    
                    if (statusMatch && priorityMatch && searchMatch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
            
            statusFilter.addEventListener('change', filterTasks);
            priorityFilter.addEventListener('change', filterTasks);
            searchInput.addEventListener('input', filterTasks);
        });
    </script>
</body>
</html>