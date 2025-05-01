<?php

// ===================================================
// = Ushbu bot @roobotmee tomonidan tarqatildi       =
// = Dasturchi @roobotmee iltemos manbaga tegmang    =
// = Tegadigan bosaiz iloyim logika qilomi qoling :) = 
//====================================================


$token = "tokkkkkeeeeeeennnnnnn"; 

$update = json_decode(file_get_contents("php://input"), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $message_id = $update['message']['message_id'];
    $text = $update['message']['text'];

    if ($text == "/start") {
        $start_text = "👋 *Salom! Men guruh adminlarini ogohlantiruvchi botman!*  
        
Adminlarni ogohlantirish uchun savdo guruhimizda `@admins` deb yozing.  
Guruhga qo‘shilish uchun quyidagi tugmani bosing.👇";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => "➕ Guruhga qo‘shilish", 'url' => "https://t.me/gruuppalinki"]]
            ]
        ];

        sendMessage($chat_id, $start_text, $keyboard);
    }

    if (strpos($text, "@admins") !== false) {
        $admins = getChatAdmins($chat_id);

        if (!empty($admins)) {
            $mentions = [];
            foreach ($admins as $admin) {
                if (isset($admin['username'])) {
                    $mentions[] = "[@{$admin['username']}](tg://user?id={$admin['id']})";
                } else {
                    $mentions[] = "[Admin](tg://user?id={$admin['id']})";
                }
            }
            $mention_text = implode(", ", $mentions);
            sendMessage($chat_id, "🔔 *Adminlar ogohlantirildi:* $mention_text", null, $message_id);
        } else {
            sendMessage($chat_id, "❌ *Adminlar yo'q!*", null, $message_id);
        }
    }
}

if (isset($update['message']['new_chat_member'])) {
    $new_member = $update['message']['new_chat_member'];
    $user_id = $new_member['id'];
    $username = isset($new_member['username']) ? "@".$new_member['username'] : "none";
    $date = date("d/m/Y");

    $welcome_text = "👋 *Salom $username! Guruxga hush kelibsiz!*\n\n"
                  . "📌 *Guruh qoidalari:* Guruhda faqat *ADMIN* bilan savdo qiling ✅\n"
                  . "❌ *ADMINSIZ* qilingan savdoga biz javobgar emasmiz!\n\n"
                  . "🆔 *ID:* $user_id\n"
                  . "👤 *USER:* $username\n"
                  . "📅 *Qo‘shilgan sana:* $date";

    sendMessage($chat_id, $welcome_text);
}

function sendMessage($chat_id, $text, $keyboard = null, $reply_to_message_id = null) {
    global $token;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => "Markdown",
        'disable_web_page_preview' => true
    ];
    if ($reply_to_message_id) {
        $data['reply_to_message_id'] = $reply_to_message_id;
    }
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    file_get_contents($url . "?" . http_build_query($data));
}

function getChatAdmins($chat_id) {
    global $token;
    $url = "https://api.telegram.org/bot$token/getChatAdministrators?chat_id=" . $chat_id;
    $response = json_decode(file_get_contents($url), true);
    $admins = [];

    if ($response['ok']) {
        foreach ($response['result'] as $admin) {
            if (!$admin['user']['is_bot']) { 
                $admins[] = [
                    'id' => $admin['user']['id'],
                    'username' => $admin['user']['username'] ?? null
                ];
            }
        }
    }
    return $admins;
}

?>
