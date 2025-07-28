<?php
require_once('../../../config/db.php');

$since = $_GET['since'] ?? 0;
$sinceDate = date('Y-m-d H:i:s', $since / 1000);

$query = "
    SELECT 'request' as type, rp.created_at, u.name, a.nama_alat, 0 as amount
    FROM request_pengisian rp 
    JOIN users u ON rp.id_user = u.id_user 
    JOIN alat a ON rp.id_alat = a.id_alat 
    WHERE rp.created_at >= '$sinceDate'
    UNION ALL
    SELECT 'pengisian' as type, pb.created_at, u.name, a.nama_alat, pb.jumlah_liter_diisi as amount
    FROM pengisian_bbm pb 
    JOIN request_pengisian rp ON pb.id_request = rp.id_request 
    JOIN users u ON pb.id_user = u.id_user 
    JOIN alat a ON rp.id_alat = a.id_alat 
    WHERE pb.created_at >= '$sinceDate'
    ORDER BY created_at DESC 
    LIMIT 10
";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0):
    while ($activity = mysqli_fetch_assoc($result)):
        $timeAgo = time_elapsed_string($activity['created_at']);
        $iconClass = $activity['type'] == 'request' ? 'request' : 'completed';
        $icon = $activity['type'] == 'request' ? 'fa-plus-circle' : 'fa-check-circle';
        $title = $activity['type'] == 'request' ? 'Request Pengisian BBM' : 'Pengisian BBM Selesai';
        $amount = $activity['amount'];
?>
        <div class="activity-item fade-in-up">
            <div class="activity-icon <?php echo $iconClass; ?>">
                <i class="fas <?php echo $icon; ?>"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title"><?php echo $title; ?></div>
                <div class="activity-description">
                    <?php echo htmlspecialchars($activity['name']); ?> - <?php echo htmlspecialchars($activity['nama_alat']); ?>
                    <?php if ($amount > 0): ?>
                        <strong>(<?php echo number_format($amount, 1); ?> L)</strong>
                    <?php endif; ?>
                </div>
                <div class="activity-time"><?php echo $timeAgo; ?></div>
            </div>
        </div>
<?php 
    endwhile;
else:
?>
    <div class="text-center py-5" style="color: white;">
        <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
        <p style="opacity: 0.7;">Tidak ada aktivitas terbaru.</p>
    </div>
<?php endif; ?>

<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Hitung minggu manual, tanpa mengubah objek DateInterval
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );

    $result = array();

    if ($diff->y) {
        $result[] = $diff->y . ' ' . $string['y'];
    }
    if ($diff->m) {
        $result[] = $diff->m . ' ' . $string['m'];
    }
    if ($weeks) {
        $result[] = $weeks . ' ' . $string['w'];
    }
    if ($days) {
        $result[] = $days . ' ' . $string['d'];
    }
    if ($diff->h) {
        $result[] = $diff->h . ' ' . $string['h'];
    }
    if ($diff->i) {
        $result[] = $diff->i . ' ' . $string['i'];
    }
    if ($diff->s && empty($result)) {
        $result[] = $diff->s . ' ' . $string['s'];
    }

    if (!$full) $result = array_slice($result, 0, 1);
    return $result ? implode(', ', $result) . ' yang lalu' : 'baru saja';
}

?>
