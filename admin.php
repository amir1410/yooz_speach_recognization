<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'TelegramBot.php';
require_once 'models/Question.php';

$config = require 'config.php';
$bot = new TelegramBot($config['bot_token']);
$db = new Database($config['db']);

function isAdmin($chat_id, $config) {
    return in_array($chat_id, $config['admin_ids']);
}

function addQuestion($db, $text, $type, $score, $options, $correct_option, $priority, $has_score) {
    $stmt = $db->prepare("INSERT INTO questions (text, type, score, options, correct_option, priority, has_score) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $opts = $options ? json_encode($options, JSON_UNESCAPED_UNICODE) : null;
    $stmt->bind_param('ssisiis', $text, $type, $score, $opts, $correct_option, $priority, $has_score);
    $stmt->execute();
}

function setQuestionPriority($db, $question_id, $priority) {
    $stmt = $db->prepare("UPDATE questions SET priority = ? WHERE id = ?");
    $stmt->bind_param('ii', $priority, $question_id);
    $stmt->execute();
}

function updateConfigMessage($key, $value) {
    $config = require 'config.php';
    $config[$key] = $value;
    file_put_contents('config.php', "<?php\nreturn " . var_export($config, true) . ";");
}

// --- پردازش پیام دریافتی ---
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? $update['callback_query']['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
if (!isAdmin($chat_id, $config)) exit;

$text = $message['text'] ?? '';

// منوی مدیریت
if ($text == '/admin' || $text == 'مدیریت') {
    $bot->sendKeyboard($chat_id, 'پنل مدیریت:', [
        ['افزودن سوال'],
        ['تعیین اولویت سوال'],
        ['تنظیم پیام خوش‌آمد'],
        ['تنظیم پیام سطح‌بندی']
    ]);
    exit;
}

// افزودن سوال
if ($text == 'افزودن سوال') {
    $bot->sendKeyboard($chat_id, "نوع سوال را انتخاب کنید:", [['متنی'], ['چهارگزینه‌ای'], ['وویس']]);
    // ادامه منطق افزودن سوال باید با وضعیت (state) پیاده‌سازی شود
    exit;
}

// تعیین اولویت سوال
if ($text == 'تعیین اولویت سوال') {
    $bot->sendMessage($chat_id, 'لطفا آیدی سوال و اولویت جدید را به صورت "id,priority" ارسال کنید.');
    exit;
}
if (preg_match('/^(\d+),(\d+)$/', $text, $m)) {
    setQuestionPriority($db, (int)$m[1], (int)$m[2]);
    $bot->sendMessage($chat_id, 'اولویت سوال بروزرسانی شد.');
    exit;
}

// تنظیم پیام خوش‌آمد
if ($text == 'تنظیم پیام خوش‌آمد') {
    $bot->sendMessage($chat_id, 'متن جدید پیام خوش‌آمد را ارسال کنید:');
    // ادامه منطق باید با وضعیت (state) پیاده‌سازی شود
    exit;
}
if (strpos($text, 'خوش‌آمد:') === 0) {
    $msg = trim(str_replace('خوش‌آمد:', '', $text));
    updateConfigMessage('welcome_message', $msg);
    $bot->sendMessage($chat_id, 'پیام خوش‌آمد بروزرسانی شد.');
    exit;
}

// تنظیم پیام سطح‌بندی
if ($text == 'تنظیم پیام سطح‌بندی') {
    $bot->sendMessage($chat_id, 'برای هر سطح پیام را به صورت "A2:متن" ارسال کنید.');
    exit;
}
if (preg_match('/^(A2|B1|B2):(.*)$/u', $text, $m)) {
    $level = $m[1];
    $msg = trim($m[2]);
    $config = require 'config.php';
    $config['level_messages'][$level] = $msg;
    file_put_contents('config.php', "<?php\nreturn " . var_export($config, true) . ";");
    $bot->sendMessage($chat_id, "پیام سطح $level بروزرسانی شد.");
    exit;
}