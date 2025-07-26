<?php
require_once 'config.php';
require_once 'TelegramBot.php';

$config = require 'config.php';
$bot = new TelegramBot($config['bot_token']);

// دریافت پیام از تلگرام
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? $update['callback_query']['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';

// بررسی اینکه آیا کاربر مدیر است
function isAdmin($chat_id, $config) {
    return in_array($chat_id, $config['admin_ids']);
}

// بررسی اینکه آیا کاربر پشتیبان است
function isSupport($chat_id, $config) {
    return $chat_id == $config['support_id'];
}

// هدایت پیام به فایل مناسب
if ($chat_id) {
    if (isAdmin($chat_id, $config)) {
        // اگر کاربر مدیر است، به admin.php هدایت کن
        include 'admin.php';
    } else {
        // اگر کاربر عادی است، به user.php هدایت کن
        include 'user.php';
    }
}