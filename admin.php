<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'TelegramBot.php';
require_once 'models/Question.php';

// اینجا منطق افزودن سوال، تعیین اولویت، تنظیم پیام‌ها و مدیریت سوالات قرار می‌گیرد
// این فایل به صورت تابعی یا کلاسی قابل توسعه است