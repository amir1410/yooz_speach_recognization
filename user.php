<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'TelegramBot.php';
require_once 'models/User.php';
require_once 'models/Question.php';
require_once 'models/Answer.php';

$config = require 'config.php';
$bot = new TelegramBot($config['bot_token']);
$db = new Database($config['db']);

function getUserByChatId($db, $chat_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE chat_id = ?");
    $stmt->bind_param('s', $chat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return new User($row);
    }
    return null;
}

function createUser($db, $chat_id) {
    $stmt = $db->prepare("INSERT INTO users (chat_id) VALUES (?)");
    $stmt->bind_param('s', $chat_id);
    $stmt->execute();
    return getUserByChatId($db, $chat_id);
}

function updateUserField($db, $chat_id, $field, $value) {
    $stmt = $db->prepare("UPDATE users SET $field = ? WHERE chat_id = ?");
    $stmt->bind_param('ss', $value, $chat_id);
    $stmt->execute();
}

function getNextQuestion($db, $user_id) {
    $sql = "SELECT * FROM questions WHERE id NOT IN (SELECT question_id FROM answers WHERE user_id = ?) ORDER BY priority ASC, id ASC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return new Question($row);
    }
    return null;
}

function saveAnswer($db, $user_id, $question, $answer, $voice_path = null) {
    $is_correct = null;
    $score = 0;
    if ($question->type === 'choice' && $question->has_score) {
        $is_correct = ($answer == $question->correct_option) ? 1 : 0;
        $score = $is_correct ? $question->score : 0;
    } elseif ($question->type === 'text' && $question->has_score) {
        $score = $question->score;
    }
    $stmt = $db->prepare("INSERT INTO answers (user_id, question_id, answer, voice_path, is_correct, score) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissii', $user_id, $question->id, $answer, $voice_path, $is_correct, $score);
    $stmt->execute();
    // Update user score and answers_count
    $db->query("UPDATE users SET score = score + $score, answers_count = answers_count + 1 WHERE id = $user_id");
}

function determineLevel($score, $level_ranges) {
    foreach ($level_ranges as $level => $range) {
        if ($score >= $range[0] && $score <= $range[1]) {
            return $level;
        }
    }
    return null;
}

function replaceVars($text, $vars) {
    foreach ($vars as $k => $v) {
        $text = str_replace('{' . $k . '}', $v, $text);
    }
    return $text;
}

// --- پردازش پیام دریافتی ---
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? $update['callback_query']['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$user = getUserByChatId($db, $chat_id);
if (!$user) $user = createUser($db, $chat_id);

// مرحله دریافت اطلاعات اولیه
if (!$user->full_name) {
    $bot->sendMessage($chat_id, $config['user_info_questions']['full_name']);
    if (!empty($message['text'])) {
        updateUserField($db, $chat_id, 'full_name', $message['text']);
    }
    exit;
}
if (!$user->goal) {
    $keyboard = array_map(function($g){return [$g];}, $config['goals']);
    $bot->sendKeyboard($chat_id, $config['user_info_questions']['goal'], $keyboard);
    if (in_array($message['text'], $config['goals'])) {
        updateUserField($db, $chat_id, 'goal', $message['text']);
    }
    exit;
}
if (!$user->city) {
    $bot->sendMessage($chat_id, $config['user_info_questions']['city']);
    if (!empty($message['text'])) {
        updateUserField($db, $chat_id, 'city', $message['text']);
    }
    exit;
}
if (!$user->phone) {
    $bot->sendMessage($chat_id, $config['user_info_questions']['phone']);
    if (!empty($message['text'])) {
        updateUserField($db, $chat_id, 'phone', $message['text']);
    }
    exit;
}

// خوش‌آمدگویی و ارسال وویس
if ($message['text'] == '/start' || $message['text'] == 'شروع ربات') {
    $welcome = replaceVars($config['welcome_message'], ['user_name' => $user->full_name]);
    $bot->sendMessage($chat_id, $welcome);
    $bot->sendVoice($chat_id, $config['welcome_voice']);
    $bot->sendKeyboard($chat_id, 'برای شروع آزمون دکمه زیر را بزنید.', [['شروع آزمون']]);
    exit;
}

// شروع آزمون
if ($message['text'] == 'شروع آزمون') {
    $question = getNextQuestion($db, $user->id);
    if ($question) {
        if ($question->type == 'choice') {
            $keyboard = array_map(function($o, $i){return [($i+1).'. '.$o];}, $question->options, array_keys($question->options));
            $bot->sendKeyboard($chat_id, $question->text, $keyboard);
        } else {
            $bot->sendMessage($chat_id, $question->text);
        }
    } else {
        // آزمون تمام شد
        $level = determineLevel($user->score, $config['level_ranges']);
        updateUserField($db, $chat_id, 'level', $level);
        $msg = replaceVars($config['level_messages'][$level], ['level' => $level, 'user_name' => $user->full_name]);
        $bot->sendMessage($chat_id, $msg);
        // ارسال به پشتیبان
        $supportMsg = "کاربر: {$user->full_name}\nسطح: {$level}\nامتیاز: {$user->score}\nشماره: {$user->phone}\nهدف: {$user->goal}\nشهر: {$user->city}";
        $bot->sendMessage($config['support_id'], $supportMsg);
    }
    exit;
}

// دریافت پاسخ سوال و ذخیره آن
$question = getNextQuestion($db, $user->id);
if ($question) {
    if ($question->type == 'choice' && isset($message['text'])) {
        $selected = trim($message['text']);
        foreach ($question->options as $i => $opt) {
            if ($selected == ($i+1).'. '.$opt) {
                saveAnswer($db, $user->id, $question, $i);
                break;
            }
        }
    } elseif ($question->type == 'text' && isset($message['text'])) {
        saveAnswer($db, $user->id, $question, $message['text']);
    } elseif ($question->type == 'voice' && isset($message['voice'])) {
        // دانلود وویس
        $file_id = $message['voice']['file_id'];
        $file_info = file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/getFile?file_id=$file_id");
        $file_info = json_decode($file_info, true);
        $file_path = $file_info['result']['file_path'];
        $voice_url = "https://api.telegram.org/file/bot{$config['bot_token']}/$file_path";
        $voice_data = file_get_contents($voice_url);
        $local_path = 'uploads/'.uniqid('voice_').'.ogg';
        file_put_contents($local_path, $voice_data);
        saveAnswer($db, $user->id, $question, '', $local_path);
    }
    // ارسال سوال بعدی
    $nextQ = getNextQuestion($db, $user->id);
    if ($nextQ) {
        if ($nextQ->type == 'choice') {
            $keyboard = array_map(function($o, $i){return [($i+1).'. '.$o];}, $nextQ->options, array_keys($nextQ->options));
            $bot->sendKeyboard($chat_id, $nextQ->text, $keyboard);
        } else {
            $bot->sendMessage($chat_id, $nextQ->text);
        }
    } else {
        // آزمون تمام شد
        $user = getUserByChatId($db, $chat_id); // آپدیت اطلاعات کاربر
        $level = determineLevel($user->score, $config['level_ranges']);
        updateUserField($db, $chat_id, 'level', $level);
        $msg = replaceVars($config['level_messages'][$level], ['level' => $level, 'user_name' => $user->full_name]);
        $bot->sendMessage($chat_id, $msg);
        // ارسال به پشتیبان
        $supportMsg = "کاربر: {$user->full_name}\nسطح: {$level}\nامتیاز: {$user->score}\nشماره: {$user->phone}\nهدف: {$user->goal}\nشهر: {$user->city}";
        $bot->sendMessage($config['support_id'], $supportMsg);
    }
    exit;
}
