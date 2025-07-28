<?php
require_once('../../../config/db.php');
header('Content-Type: application/json');

$since = $_GET['since'] ?? 0;
$sinceDate = date('Y-m-d H:i:s', $since / 1000);

// Cek apakah ada data baru sejak timestamp terakhir
$query = "
    SELECT COUNT(*) as new_count FROM (
        SELECT created_at FROM request_pengisian WHERE created_at > '$sinceDate'
        UNION ALL
        SELECT created_at FROM pengisian_bbm WHERE created_at > '$sinceDate'
    ) as new_data
";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$hasNewData = $row['new_count'] > 0;

echo json_encode([
    'hasNewData' => $hasNewData,
    'newCount' => intval($row['new_count'])
]);
?>
