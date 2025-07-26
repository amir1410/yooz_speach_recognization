<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'TelegramBot.php';
require_once 'models/User.php';
require_once 'models/Question.php';
require_once 'models/Answer.php';

// اینجا منطق دریافت اطلاعات کاربر، شروع آزمون، ذخیره پاسخ و تعیین سطح قرار می‌گیرد
// این فایل به صورت تابعی یا کلاسی قابل توسعه است