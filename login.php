<?php
session_start();
include "config/db.php";

$email = $_POST['email'];
$password = $_POST['password'];
$password_md5 = md5($password);

$query = "SELECT * FROM users WHERE email='$email' AND (password='$password' OR password='$password_md5')";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] == 'admin') {
        header("Location: dashboard/admin/index.php");
    } elseif ($user['role'] == 'supervisor') {
        header("Location: dashboard/supervisor/index.php");
    } elseif ($user['role'] == 'operator') {
        header("Location: dashboard/operator/index.php");
    } elseif ($user['role'] == 'operator_pengisian') {
        header("Location: dashboard/operator_pengisian/index.php");
    }
} else {
    echo "Login gagal. Email atau password salah.";
}
?>
