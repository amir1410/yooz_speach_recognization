<?php
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'telegram_bot',
    ],
    'bot_token' => 'YOUR_BOT_TOKEN',
    'admin_ids' => [12345678, 87654321], // آرایه آیدی مدیران
    'support_id' => 11223344, // آیدی پشتیبان
    'welcome_message' => 'خوش آمدید {user_name}!',
    'welcome_voice' => 'welcome.ogg', // نام فایل وویس خوش‌آمد
    'level_messages' => [
        'A2' => 'سطح شما {level} است، تبریک!',
        'B1' => 'سطح شما {level} است، عالی!',
        'B2' => 'سطح شما {level} است، فوق‌العاده!',
    ],
    'level_ranges' => [
        'A2' => [0, 30],
        'B1' => [31, 60],
        'B2' => [61, 100],
    ],
    'user_info_questions' => [
        'full_name' => 'لطفا نام و نام خانوادگی خود را وارد کنید:',
        'goal' => 'هدف شما از یادگیری چیست؟',
        'city' => 'شهر محل زندگی خود را وارد کنید:',
        'phone' => 'شماره تماس خود را وارد کنید:',
    ],
    'goals' => [
        'مهاجرت',
        'ارتقا شغلی',
        'ارتقا تحصیلی',
        'علاقه شخصی'
    ]
];