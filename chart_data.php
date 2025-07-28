<?php
require_once('../../../config/db.php');

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'harian':
        getDataHarian($conn);
        break;
    case 'bulanan':
        getDataBulanan($conn);
        break;
    case 'alat':
        getDataAlat($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid type']);
}

function getDataHarian($conn) {
    $labels = [];
    $data = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d/m', strtotime($date));
        
        $query = "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total 
                  FROM pengisian_bbm 
                  WHERE DATE(created_at) = '$date'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $data[] = floatval($row['total']);
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
}

function getDataBulanan($conn) {
    $tahun = $_GET['tahun'] ?? date('Y');
    $data = [];
    
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $query = "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total 
                  FROM pengisian_bbm 
                  WHERE YEAR(created_at) = '$tahun' AND MONTH(created_at) = '$bulan'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $data[] = floatval($row['total']);
    }
    
    echo json_encode([
        'data' => $data
    ]);
}

function getDataAlat($conn) {
    $bulan = $_GET['bulan'] ?? '';
    $tahun = $_GET['tahun'] ?? date('Y');
    
    $where_clause = "WHERE YEAR(pb.created_at) = '$tahun'";
    if (!empty($bulan)) {
        $where_clause .= " AND MONTH(pb.created_at) = '$bulan'";
    }
    
    $query = "SELECT a.nama_alat, COALESCE(SUM(pb.jumlah_liter_diisi), 0) as total_liter
              FROM alat a
              LEFT JOIN request_pengisian rp ON a.id_alat = rp.id_alat
              LEFT JOIN pengisian_bbm pb ON rp.id_request = pb.id_request
              $where_clause
              GROUP BY a.id_alat, a.nama_alat
              HAVING total_liter > 0
              ORDER BY total_liter DESC";
    
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
}
?>
