<?php
function sendTelegramMessage($chat_id, $message) {
    $bot_token = '7466979760:AAEYrljm7m2tY9aXtc69Yfjv-Qg_GDf_B9s'; // Ganti dengan token bot Telegram Anda
    
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $data = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    );
    
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        )
    );
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}

function notifyUser($user_id, $message) {
    global $conn;
    
    $query = "SELECT chat_id_telegram FROM users WHERE id_user = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (!empty($user['chat_id_telegram'])) {
            return sendTelegramMessage($user['chat_id_telegram'], $message);
        }
    }
    
    return false;
}
?>
