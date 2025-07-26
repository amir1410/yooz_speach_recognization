<?php
class TelegramBot {
    private $token;
    private $apiUrl;

    public function __construct($token) {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$token}/";
    }

    private function request($method, $params = []) {
        $url = $this->apiUrl . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function sendMessage($chat_id, $text, $reply_markup = null) {
        $params = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if ($reply_markup) {
            $params['reply_markup'] = json_encode($reply_markup);
        }
        return $this->request('sendMessage', $params);
    }

    public function sendKeyboard($chat_id, $text, $keyboard) {
        $reply_markup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        return $this->sendMessage($chat_id, $text, $reply_markup);
    }

    public function sendVoice($chat_id, $voice, $caption = null) {
        $params = [
            'chat_id' => $chat_id,
            'voice' => new CURLFile($voice),
        ];
        if ($caption) {
            $params['caption'] = $caption;
        }
        return $this->request('sendVoice', $params);
    }

    public function sendPhoto($chat_id, $photo, $caption = null) {
        $params = [
            'chat_id' => $chat_id,
            'photo' => new CURLFile($photo),
        ];
        if ($caption) {
            $params['caption'] = $caption;
        }
        return $this->request('sendPhoto', $params);
    }

    public function sendVideo($chat_id, $video, $caption = null) {
        $params = [
            'chat_id' => $chat_id,
            'video' => new CURLFile($video),
        ];
        if ($caption) {
            $params['caption'] = $caption;
        }
        return $this->request('sendVideo', $params);
    }
}