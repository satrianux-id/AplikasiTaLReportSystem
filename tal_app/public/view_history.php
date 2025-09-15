<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Ambil data tugas
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: index.php');
    exit;
}

// Ambil history
$history = getTaskHistory($pdo, $id);

// Cari waktu dibuat dan waktu selesai
$created_at = null;
$completed_at = null;
foreach ($history as $record) {
    if ($record['changed_field'] === 'task_created' && !$created_at) {
        $created_at = $record['changed_at'];
    }
    if ($record['changed_field'] === 'status' && $record['new_value'] === 'completed') {
        $completed_at = $record['changed_at'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Tugas - TAL Report</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>History Perubahan: <?= htmlspecialchars($task['task_name']) ?></h2>
                        <a href="index.php" class="btn btn-secondary">Kembali</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($history) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Field</th>
                                            <th>Nilai Lama</th>
                                            <th>Nilai Baru</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $i => $record): ?>
                                            <tr>
                                                <td><?= date('d M Y H:i', strtotime($record['changed_at'])) ?></td>
                                                <td>
                                                    <?php 
                                                    $field_names = [
                                                        'task_name' => 'Nama Tugas',
                                                        'description' => 'Deskripsi',
                                                        'status' => 'Status',
                                                        'priority' => 'Prioritas',
                                                        'due_date' => 'Tanggal Jatuh Tempo',
                                                        'image_changed' => 'Gambar',
                                                        'task_created' => 'Tugas Dibuat'
                                                    ];
                                                    echo $field_names[$record['changed_field']] ?? $record['changed_field'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($record['changed_field'] === 'status') {
                                                        $statuses = [
                                                            'pending' => 'Pending',
                                                            'in_progress' => 'Sedang Dikerjakan',
                                                            'completed' => 'Selesai'
                                                        ];
                                                        echo $statuses[$record['old_value']] ?? $record['old_value'];
                                                    } elseif ($record['changed_field'] === 'priority') {
                                                        $priorities = [
                                                            'low' => 'Rendah',
                                                            'medium' => 'Sedang',
                                                            'high' => 'Tinggi'
                                                        ];
                                                        echo $priorities[$record['old_value']] ?? $record['old_value'];
                                                    } elseif ($record['changed_field'] === 'image_changed') {
                                                        if ($record['old_value']) {
                                                            echo '<img src="/tal_app/public/uploads/' . htmlspecialchars($record['old_value']) . '" alt="Gambar Sebelumnya" width="80">';
                                                        } else {
                                                            echo 'Tidak Ada Gambar';
                                                        }
                                                    } else {
                                                        echo htmlspecialchars($record['old_value'] ?? '-');
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($record['changed_field'] === 'status') {
                                                        $statuses = [
                                                            'pending' => 'Pending',
                                                            'in_progress' => 'Sedang Dikerjakan',
                                                            'completed' => 'Selesai'
                                                        ];
                                                        echo $statuses[$record['new_value']] ?? $record['new_value'];
                                                    } elseif ($record['changed_field'] === 'priority') {
                                                        $priorities = [
                                                            'low' => 'Rendah',
                                                            'medium' => 'Sedang',
                                                            'high' => 'Tinggi'
                                                        ];
                                                        echo $priorities[$record['new_value']] ?? $record['new_value'];
                                                    } elseif ($record['changed_field'] === 'image_changed') {
                                                        if ($record['new_value']) {
                                                            echo '<img src="/tal_app/public/uploads/' . htmlspecialchars($record['new_value']) . '" alt="Gambar Baru" width="80">';
                                                        } else {
                                                            echo 'Tidak Ada Gambar';
                                                        }
                                                    } else {
                                                        echo htmlspecialchars($record['new_value'] ?? '-');
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button 
                                                        class="btn btn-info btn-sm view-detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal"
                                                        data-field="<?= htmlspecialchars($field_names[$record['changed_field']] ?? $record['changed_field']) ?>"
                                                        data-old="<?= htmlspecialchars($record['old_value']) ?>"
                                                        data-new="<?= htmlspecialchars($record['new_value']) ?>"
                                                        data-changed="<?= htmlspecialchars($record['changed_field']) ?>"
                                                        data-oldimg="<?= $record['changed_field'] === 'image_changed' && $record['old_value'] ? htmlspecialchars($record['old_value']) : '' ?>"
                                                        data-newimg="<?= $record['changed_field'] === 'image_changed' && $record['new_value'] ? htmlspecialchars($record['new_value']) : '' ?>"
                                                        data-time="<?= date('d M Y H:i', strtotime($record['changed_at'])) ?>"
                                                        data-created="<?= $created_at ? date('Y-m-d\TH:i:s', strtotime($created_at)) : '' ?>"
                                                        data-completed="<?= $completed_at ? date('Y-m-d\TH:i:s', strtotime($completed_at)) : '' ?>"
                                                    >
                                                        <i class="fa fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada history perubahan untuk tugas ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Detail Perubahan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <ul class="list-group">
              <li class="list-group-item"><strong>Waktu:</strong> <span id="modal-time"></span></li>
              <li class="list-group-item"><strong>Field:</strong> <span id="modal-field"></span></li>
              <li class="list-group-item"><strong>Nilai Lama:</strong> <span id="modal-old"></span></li>
              <li class="list-group-item"><strong>Nilai Baru:</strong> <span id="modal-new"></span></li>
              <li class="list-group-item" id="modal-durasi-li" style="display:none;">
                <strong>Total Durasi:</strong> <span id="modal-durasi"></span>
              </li>
            </ul>
            <div id="modal-imgs" class="mt-3" style="display:none;">
              <div><strong>Gambar Sebelumnya:</strong><br><img id="modal-oldimg" src="" alt="Gambar Sebelumnya" width="120"></div>
              <div class="mt-2"><strong>Gambar Sesudah:</strong><br><img id="modal-newimg" src="" alt="Gambar Sesudah" width="120"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.view-detail-btn').on('click', function() {
            var button = $(this);
            var field = button.data('field');
            var oldValue = button.data('old');
            var newValue = button.data('new');
            var changedField = button.data('changed');
            var oldImg = button.data('oldimg');
            var newImg = button.data('newimg');
            var time = button.data('time');
            var created = button.data('created');
            var completed = button.data('completed');

            $('#modal-time').text(time);
            $('#modal-field').text(field);
            $('#modal-old').text(oldValue);
            $('#modal-new').text(newValue);

            // Durasi hanya tampil jika status sudah completed
            if (created && completed) {
                var start = new Date(created);
                var end = new Date(completed);
                var diffMs = end - start;
                var diffMins = Math.floor(diffMs / 60000);
                var jam = Math.floor(diffMins / 60);
                var menit = diffMins % 60;
                var durasiStr = '';
                if (jam > 0) durasiStr += jam + ' Jam ';
                durasiStr += menit + ' Menit';
                document.getElementById('modal-durasi').textContent = durasiStr;
                document.getElementById('modal-durasi-li').style.display = 'block';
            } else {
                document.getElementById('modal-durasi-li').style.display = 'none';
            }

            if (changedField === 'image_changed') {
                if (oldImg) {
                    $('#modal-oldimg').attr('src', oldImg);
                    $('#modal-oldimg').parent().show();
                } else {
                    $('#modal-oldimg').parent().hide();
                }

                if (newImg) {
                    $('#modal-newimg').attr('src', newImg);
                    $('#modal-newimg').parent().show();
                } else {
                    $('#modal-newimg').parent().hide();
                }

                $('#modal-imgs').show();
            } else {
                $('#modal-imgs').hide();
            }
        });
    });

    document.querySelectorAll('.view-detail-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('modal-time').textContent = btn.getAttribute('data-time');
            document.getElementById('modal-field').textContent = btn.getAttribute('data-field');
            let changed = btn.getAttribute('data-changed');
            let oldVal = btn.getAttribute('data-old');
            let newVal = btn.getAttribute('data-new');
            // Untuk status dan prioritas, tampilkan label
            if (changed === 'status') {
                const statuses = {'pending':'Pending','in_progress':'Sedang Dikerjakan','completed':'Selesai'};
                oldVal = statuses[oldVal] ?? oldVal;
                newVal = statuses[newVal] ?? newVal;
            }
            if (changed === 'priority') {
                const priorities = {'low':'Rendah','medium':'Sedang','high':'Tinggi'};
                oldVal = priorities[oldVal] ?? oldVal;
                newVal = priorities[newVal] ?? newVal;
            }
            document.getElementById('modal-old').textContent = oldVal || '-';
            document.getElementById('modal-new').textContent = newVal || '-';

            // Gambar
            if (changed === 'image_changed') {
                document.getElementById('modal-imgs').style.display = 'block';
                let oldimg = btn.getAttribute('data-oldimg');
                let newimg = btn.getAttribute('data-newimg');
                document.getElementById('modal-oldimg').src = oldimg ? '/tal_app/public/uploads/' + oldimg : '';
                document.getElementById('modal-newimg').src = newimg ? '/tal_app/public/uploads/' + newimg : '';
                document.getElementById('modal-oldimg').style.display = oldimg ? 'block' : 'none';
                document.getElementById('modal-newimg').style.display = newimg ? 'block' : 'none';
            } else {
                document.getElementById('modal-imgs').style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>