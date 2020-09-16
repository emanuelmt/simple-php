<?php

namespace SimplePHP\Notification;

class TelegramNotify {

    private $message;
    private $key;
    private $channel;

    public function __construct($key, $channel, $messageHtml) {
        $this->key = $key;
        $this->channel = $channel;
        $this->message = $this->formatter($messageHtml);
        $this->send();
    }

    private function formatter($messageHtml) {
        return strip_tags(str_replace(["<br>", "</br>"], ["\n", "\n"], $messageHtml), "<b><strong><i><em><u><ins><s><strike><del><a><code><pre>");
    }

    private function send() {
        $ch = curl_init();
        $url = 'https://api.telegram.org/bot' . $this->key . '/SendMessage';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'text' => $this->message,
            'chat_id' => $this->channel,
            'parse_mode' => "HTML",
            'disable_web_page_preview' => null,
            'disable_notification' => true,
        ]));

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if ($result['ok'] === false) {
            throw new \SimplePHP\Exception\TelegramNotifyException('Telegram API error. Description: ' . $result['description']);
        }
    }

}
