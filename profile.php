<?php
// Profile.php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: /login.php');
  exit();
}
$user_name = $_SESSION['username'];
$user_role = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }
    .profile-container {
      max-width: 600px;
      margin: 20px auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) {
      .profile-container {
        padding: 10px;
        margin: 10px;
      }
    }
  </style>
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div style="margin-left: 250px; padding: 20px;">
    <?php include 'includes/topbar.php'; ?>
    <div class="profile-container">
      <h1>Profile of <?php echo htmlspecialchars($user_name); ?></h1>
      <p>Welcome to your profile page.</p>
      <p>Your role: <?php echo htmlspecialchars($user_role); ?></p>
    </div>
  </div>
</body>
</html>
