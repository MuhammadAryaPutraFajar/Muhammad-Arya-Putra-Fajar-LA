<?php
require_once('../../../config/db.php');

$recent_query = "SELECT pb.created_at, u.name as operator_name, a.nama_alat, pb.jumlah_liter_diisi 
                 FROM pengisian_bbm pb 
                 JOIN request_pengisian rp ON pb.id_request = rp.id_request 
                 JOIN users u ON rp.id_operator = u.id_user 
                 JOIN alat a ON rp.id_alat = a.id_alat 
                 ORDER BY pb.created_at DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

if (mysqli_num_rows($recent_result) > 0): ?>
    <div class="list-group list-group-flush">
        <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><?php echo htmlspecialchars($recent['operator_name']); ?></h6>
                        <p class="mb-1"><?php echo htmlspecialchars($recent['nama_alat']); ?></p>
                        <small class="text-muted"><?php echo number_format($recent['jumlah_liter_diisi'], 1); ?> Liter</small>
                    </div>
                    <small class="text-muted"><?php echo date('H:i', strtotime($recent['created_at'])); ?></small>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="text-center py-4">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <p class="text-muted">Tidak ada aktivitas terbaru.</p>
    </div>
<?php endif; ?>
