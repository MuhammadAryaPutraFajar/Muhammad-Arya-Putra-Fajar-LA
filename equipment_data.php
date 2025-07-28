<?php
require_once('../../../config/db.php');
header('Content-Type: application/json');

$query = "SELECT a.nama_alat, COALESCE(SUM(pb.jumlah_liter_diisi), 0) as total_liter
          FROM alat a
          LEFT JOIN request_pengisian rp ON a.id_alat = rp.id_alat
          LEFT JOIN pengisian_bbm pb ON rp.id_request = pb.id_request
          WHERE a.status = 'aktif'
          GROUP BY a.id_alat, a.nama_alat
          HAVING total_liter > 0
          ORDER BY total_liter DESC
          LIMIT 8";

$result = mysqli_query($conn, $query);
$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['nama_alat'];
    $data[] = floatval($row['total_liter']);
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);
?>
