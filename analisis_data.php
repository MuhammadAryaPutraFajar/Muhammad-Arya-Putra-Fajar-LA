<?php
require_once('../../../config/db.php');
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$period = $_GET['period'] ?? 'all';

switch ($type) {
    case 'alat':
        getAlatData($conn, $period);
        break;
    case 'trend':
        getTrendData($conn);
        break;
    case 'table':
        getTableData($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid type']);
}

function getAlatData($conn, $period) {
    $whereClause = "";
    
    switch ($period) {
        case 'month':
            $whereClause = "WHERE YEAR(pb.created_at) = YEAR(CURDATE()) AND MONTH(pb.created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $whereClause = "WHERE YEAR(pb.created_at) = YEAR(CURDATE())";
            break;
        default:
            $whereClause = "";
    }
    
    $query = "SELECT a.nama_alat, COALESCE(SUM(pb.jumlah_liter_diisi), 0) as total_liter
              FROM alat a
              LEFT JOIN request_pengisian rp ON a.id_alat = rp.id_alat
              LEFT JOIN pengisian_bbm pb ON rp.id_request = pb.id_request
              $whereClause
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

function getTrendData($conn) {
    $labels = [];
    $data = [];
    
    // Ambil data 12 bulan terakhir
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime($month . '-01'));
        
        $query = "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total 
                  FROM pengisian_bbm 
                  WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $data[] = floatval($row['total']);
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
}

function getTableData($conn) {
    // Ambil total konsumsi untuk perhitungan persentase
    $total_query = "SELECT SUM(jumlah_liter_diisi) as grand_total FROM pengisian_bbm";
    $total_result = mysqli_query($conn, $total_query);
    $grand_total = mysqli_fetch_assoc($total_result)['grand_total'] ?? 1;
    
    $query = "SELECT 
                a.nama_alat,
                COALESCE(SUM(pb.jumlah_liter_diisi), 0) as total_konsumsi,
                COUNT(pb.id_pengisian) as frekuensi,
                COALESCE(AVG(pb.jumlah_liter_diisi), 0) as rata_rata,
                COALESCE((SUM(pb.jumlah_liter_diisi) / $grand_total * 100), 0) as persentase
              FROM alat a
              LEFT JOIN request_pengisian rp ON a.id_alat = rp.id_alat
              LEFT JOIN pengisian_bbm pb ON rp.id_request = pb.id_request
              GROUP BY a.id_alat, a.nama_alat
              HAVING total_konsumsi > 0
              ORDER BY total_konsumsi DESC";
    
    $result = mysqli_query($conn, $query);
    $data = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'nama_alat' => $row['nama_alat'],
            'total_konsumsi' => floatval($row['total_konsumsi']),
            'frekuensi' => intval($row['frekuensi']),
            'rata_rata' => floatval($row['rata_rata']),
            'persentase' => floatval($row['persentase'])
        ];
    }
    
    echo json_encode($data);
}
?>
