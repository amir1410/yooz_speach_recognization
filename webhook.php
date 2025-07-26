<?php
require_once 'config.php';
require_once 'TelegramBot.php';

$update = json_decode(file_get_contents('php://input'), true);

// اینجا باید بر اساس پیام دریافتی، درخواست به user.php یا admin.php هدایت شود
// مثلا اگر کاربر مدیر بود، به admin.php وگرنه به user.php