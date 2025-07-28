<?php
require_once('../../../config/db.php');

header('Content-Type: application/json');

// Ambil statistik terbaru
$total_stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
$total_stok_result = mysqli_query($conn, $total_stok_query);
$total_stok = mysqli_fetch_assoc($total_stok_result)['total_liter'] ?? 0;

$total_pengisian_today = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm WHERE DATE(created_at) = CURDATE()");
$today_count = mysqli_fetch_assoc($total_pengisian_today)['total'] ?? 0;

$total_liter_today = mysqli_query($conn, "SELECT SUM(jumlah_liter_diisi) as total FROM pengisian_bbm WHERE DATE(created_at) = CURDATE()");
$today_liter = mysqli_fetch_assoc($total_liter_today)['total'] ?? 0;

$pending_requests = mysqli_query($conn, "SELECT COUNT(*) as total FROM request_pengisian WHERE status = 'pending'");
$pending_count = mysqli_fetch_assoc($pending_requests)['total'] ?? 0;

echo json_encode([
    'total_stok' => $total_stok,
    'today_count' => $today_count,
    'today_liter' => $today_liter,
    'pending_count' => $pending_count
]);
?>
