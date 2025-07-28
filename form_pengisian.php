<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'operator_pengisian') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');
require_once('../../config/telegram.php');

function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

$message = '';
$message_type = '';

// Cek apakah request_id valid
if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    header("Location: request_disetujui.php");
    exit();
}

$request_id = clean_input($_GET['request_id']);

// Ambil data request
$request_query = "SELECT rp.*, u.name as operator_name, a.nama_alat, ns.nomor_seri 
                  FROM request_pengisian rp 
                  JOIN users u ON rp.id_user = u.id_user 
                  JOIN alat a ON rp.id_alat = a.id_alat 
                  JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                  WHERE rp.id_request = '$request_id' AND rp.status = 'disetujui'";
$request_result = mysqli_query($conn, $request_query);

if (mysqli_num_rows($request_result) == 0) {
    header("Location: request_disetujui.php");
    exit();
}

$request_data = mysqli_fetch_assoc($request_result);

// Cek apakah sudah diisi
$check_pengisian = "SELECT id_pengisian FROM pengisian_bbm WHERE id_request = '$request_id'";
$check_result = mysqli_query($conn, $check_pengisian);
if (mysqli_num_rows($check_result) > 0) {
    header("Location: request_disetujui.php");
    exit();
}

// Proses submit pengisian
if (isset($_POST['submit_pengisian'])) {
    $liter_sebelum = clean_input($_POST['liter_sebelum']);
    $liter_sesudah = clean_input($_POST['liter_sesudah']);
    $tanda_tangan = clean_input($_POST['tanda_tangan']);
    $operator_pengisian_id = $_SESSION['user_id'];
    
    // Validasi
    if (!is_numeric($liter_sebelum) || !is_numeric($liter_sesudah)) {
        $message = "Liter harus berupa angka!";
        $message_type = "danger";
    } else if ($liter_sesudah <= $liter_sebelum) {
        $message = "Liter sesudah harus lebih besar dari liter sebelum!";
        $message_type = "danger";
    } else {
        $jumlah_liter_diisi = $liter_sesudah - $liter_sebelum;
        
        // Cek stok tersedia
        $stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
        $stok_result = mysqli_query($conn, $stok_query);
        $stok_tersedia = mysqli_fetch_assoc($stok_result)['total_liter'];
        
        if ($jumlah_liter_diisi > $stok_tersedia) {
            $message = "Stok BBM tidak mencukupi! Tersedia: " . number_format($stok_tersedia, 2) . " liter";
            $message_type = "danger";
        } else {
            // Upload foto sebelum
            $foto_sebelum = '';
            if (isset($_FILES['foto_sebelum']) && $_FILES['foto_sebelum']['error'] == 0) {
                $target_dir = "../../uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['foto_sebelum']['name'], PATHINFO_EXTENSION);
                $foto_sebelum = 'sebelum_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $target_file = $target_dir . $foto_sebelum;
                
                if (!move_uploaded_file($_FILES['foto_sebelum']['tmp_name'], $target_file)) {
                    $message = "Error upload foto sebelum!";
                    $message_type = "danger";
                }
            }
            
            // Upload foto sesudah
            $foto_sesudah = '';
            if (isset($_FILES['foto_sesudah']) && $_FILES['foto_sesudah']['error'] == 0) {
                $target_dir = "../../uploads/";
                $file_extension = pathinfo($_FILES['foto_sesudah']['name'], PATHINFO_EXTENSION);
                $foto_sesudah = 'sesudah_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $target_file = $target_dir . $foto_sesudah;
                
                if (!move_uploaded_file($_FILES['foto_sesudah']['tmp_name'], $target_file)) {
                    $message = "Error upload foto sesudah!";
                    $message_type = "danger";
                }
            }
            
            if (empty($message) && !empty($foto_sebelum) && !empty($foto_sesudah)) {
                // Insert pengisian BBM
                $insert_query = "INSERT INTO pengisian_bbm (id_request, id_user, liter_sebelum, foto_sebelum, liter_sesudah, foto_sesudah, jumlah_liter_diisi, tanda_tangan) 
                                VALUES ('$request_id', '$operator_pengisian_id', '$liter_sebelum', '$foto_sebelum', '$liter_sesudah', '$foto_sesudah', '$jumlah_liter_diisi', '$tanda_tangan')";
                
                if (mysqli_query($conn, $insert_query)) {
                    // Update stok BBM
                    $update_stok = "UPDATE total_stok_bbm SET total_liter = total_liter - $jumlah_liter_diisi WHERE id_total = 1";
                    mysqli_query($conn, $update_stok);
                    
                    $message = "Pengisian BBM berhasil dicatat!";
                    $message_type = "success";
                    
                    // Kirim notifikasi ke supervisor
                    $supervisor_query = "SELECT id_user FROM users WHERE role = 'supervisor'";
                    $supervisor_result = mysqli_query($conn, $supervisor_query);
                    while ($supervisor = mysqli_fetch_assoc($supervisor_result)) {
                        $notif_message = "⛽ <b>Pengisian BBM Selesai</b>\n\n";
                        $notif_message .= "Operator Request: " . $request_data['operator_name'] . "\n";
                        $notif_message .= "Operator Pengisian: " . $_SESSION['name'] . "\n";
                        $notif_message .= "Alat: " . $request_data['nama_alat'] . "\n";
                        $notif_message .= "No. Seri: " . $request_data['nomor_seri'] . "\n";
                        $notif_message .= "Jumlah: " . number_format($jumlah_liter_diisi, 2) . " Liter\n";
                        $notif_message .= "Sisa Stok: " . number_format($stok_tersedia - $jumlah_liter_diisi, 2) . " Liter";
                        
                        notifyUser($supervisor['id_user'], $notif_message);
                    }
                    
                    // Notifikasi ke operator yang request
                    $notif_message = "✅ <b>Pengisian BBM Selesai</b>\n\n";
                    $notif_message .= "Alat: " . $request_data['nama_alat'] . "\n";
                    $notif_message .= "No. Seri: " . $request_data['nomor_seri'] . "\n";
                    $notif_message .= "Jumlah: " . number_format($jumlah_liter_diisi, 2) . " Liter\n";
                    $notif_message .= "Operator Pengisian: " . $_SESSION['name'];
                    
                    notifyUser($request_data['id_operator'], $notif_message);
                    
                    // Redirect setelah 2 detik
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'request_disetujui.php';
                        }, 2000);
                    </script>";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $message_type = "danger";
                }
            } else if (empty($foto_sebelum) || empty($foto_sesudah)) {
                $message = "Semua foto harus diupload!";
                $message_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengisian BBM - Operator Pengisian</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        :root {
            --ipc-primary: #005baa;
            --ipc-secondary: #0074d9;
            --ipc-accent: #003b6f;
            --ipc-light: #e6f0f9;
            --ipc-text-light: #ffffff;
            --ipc-text-dark: #333333;
            --ipc-gray-light: #f5f5f5;
            --ipc-gray: #e0e0e0;
            --ipc-success: #28a745;
            --ipc-warning: #ffc107;
            --ipc-danger: #dc3545;
            --ipc-info: #17a2b8;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 70px;
            --transition-speed: 0.3s;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
            transition: all var(--transition-speed) ease;
            background-color: #f8f9fc;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
        }
        
        .form-control-file {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 8px 12px;
            background-color: white;
        }
        
        .signature-pad {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            cursor: crosshair;
            background-color: white;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--ipc-success), #1cc88a);
            border: none;
            color: white;
        }
        
        .btn-success:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            border: none;
            color: white;
        }
        
        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.875rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
            color: var(--ipc-success);
            border-left: 4px solid var(--ipc-success);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
            color: var(--ipc-danger);
            border-left: 4px solid var(--ipc-danger);
        }
        
        .text-danger {
            color: var(--ipc-danger) !important;
        }
        
        .signature-container {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e3e6f0;
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                padding: 20px;
            }
            
            .sidebar.expanded ~ .main-content {
                margin-left: var(--sidebar-width);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .page-header {
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header,
            .card-body {
                padding: 20px 15px;
            }
            
            .signature-pad {
                width: 100% !important;
                height: 150px !important;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <?php 
    include('../../includes/sidebar.php');
    include('../../includes/topbar.php');
    showSidebar('operator_pengisian');
    showTopbar('operator_pengisian');
    ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-gas-pump mr-2"></i>Form Pengisian BBM</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> mr-2"></i>
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Info Request -->
        <div class="card info-card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle mr-2"></i>Informasi Request #<?php echo $request_data['id_request']; ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Operator Request:</strong> <?php echo htmlspecialchars($request_data['operator_name']); ?></p>
                        <p><strong>Alat:</strong> <?php echo htmlspecialchars($request_data['nama_alat']); ?></p>
                        <p><strong>No. Seri:</strong> <?php echo htmlspecialchars($request_data['nomor_seri']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Hourmeter:</strong> <?php echo htmlspecialchars($request_data['hourmeter']); ?></p>
                        <p><strong>Tanggal Request:</strong> <?php echo date('d/m/Y H:i', strtotime($request_data['created_at'])); ?></p>
                        <p><strong>Tanggal Disetujui:</strong> <?php echo date('d/m/Y H:i', strtotime($request_data['tanggal_verifikasi'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Pengisian -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-edit mr-2"></i>Form Pengisian BBM</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="pengisianForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="operator_pengisian">Nama Operator Pengisian BBM</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="liter_sebelum">Liter Dispenser Sebelum Pengisian <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="liter_sebelum" name="liter_sebelum" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label for="foto_sebelum">Foto Dispenser Sebelum <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" id="foto_sebelum" name="foto_sebelum" accept="image/*" required>
                                <img id="preview_sebelum" class="preview-image" style="display: none;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="liter_sesudah">Liter Dispenser Sesudah Pengisian <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="liter_sesudah" name="liter_sesudah" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label for="foto_sesudah">Foto Dispenser Sesudah <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" id="foto_sesudah" name="foto_sesudah" accept="image/*" required>
                                <img id="preview_sesudah" class="preview-image" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jumlah Liter yang Diisi</label>
                                <input type="text" class="form-control" id="jumlah_liter" readonly placeholder="Akan dihitung otomatis">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tanda Tangan <span class="text-danger">*</span></label>
                        <div class="signature-container">
                            <canvas id="signature-pad" class="signature-pad" width="400" height="200"></canvas>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">
                                    <i class="fas fa-eraser mr-2"></i>Hapus Tanda Tangan
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="tanda_tangan" name="tanda_tangan">
                    </div>

                    <hr>

                    <div class="form-group text-center">
                        <button type="submit" name="submit_pengisian" class="btn btn-success btn-lg mr-3">
                            <i class="fas fa-save mr-2"></i>Simpan Pengisian
                        </button>
                        <a href="request_disetujui.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Signature Pad
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 0)'
        });

        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        // Hitung jumlah liter otomatis
        function hitungJumlahLiter() {
            var sebelum = parseFloat($('#liter_sebelum').val()) || 0;
            var sesudah = parseFloat($('#liter_sesudah').val()) || 0;
            var jumlah = sesudah - sebelum;
            
            if (jumlah > 0) {
                $('#jumlah_liter').val(jumlah.toFixed(2) + ' Liter');
            } else {
                $('#jumlah_liter').val('');
            }
        }

        $('#liter_sebelum, #liter_sesudah').on('input', hitungJumlahLiter);

        // Preview gambar
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(previewId).attr('src', e.target.result).show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('#foto_sebelum').change(function() {
            previewImage(this, '#preview_sebelum');
        });

        $('#foto_sesudah').change(function() {
            previewImage(this, '#preview_sesudah');
        });

        // Submit form
        $('#pengisianForm').submit(function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Tanda tangan harus diisi!');
                return false;
            }
            
            var sebelum = parseFloat($('#liter_sebelum').val()) || 0;
            var sesudah = parseFloat($('#liter_sesudah').val()) || 0;
            
            if (sesudah <= sebelum) {
                e.preventDefault();
                alert('Liter sesudah harus lebih besar dari liter sebelum!');
                return false;
            }
            
            var dataURL = signaturePad.toDataURL();
            $('#tanda_tangan').val(dataURL);
        });
    </script>
</body>
</html>
