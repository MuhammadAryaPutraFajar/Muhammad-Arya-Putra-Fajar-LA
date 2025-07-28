<?php
include "../../config/db.php";

if (isset($_POST['id_alat'])) {
    $alat_id = mysqli_real_escape_string($conn, $_POST['id_alat']);
    
    $query = "SELECT * FROM nomor_seri WHERE id_alat = '$alat_id' AND status = 'aktif' ORDER BY nomor_seri ASC";
    $result = mysqli_query($conn, $query);
    
    echo '<option value="">Pilih No. Seri</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['id_nomor'] . '">' . $row['nomor_seri'] . '</option>';
    }
}
?>
