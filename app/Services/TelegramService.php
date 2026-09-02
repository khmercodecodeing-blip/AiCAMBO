<?php

namespace App\Services;

/**
 * Telegram Service — Creates temporary invite links via the Telegram Bot API
 */
class TelegramService
{
    private string $botToken;
    private string $apiBase = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->botToken = TELEGRAM_BOT_TOKEN;
    }

    /**
     * Create a temporary, single-use invite link for a Telegram group
     *
     * @param string $chatId     Telegram group/chat ID (e.g., "-1001234567890")
     * @param int    $expireMinutes How many minutes before link expires (default: 10)
     * @param int    $memberLimit  Max users that can join (default: 1)
     * @return array{success: bool, invite_link: string|null, error: string|null}
     */
    public function createInviteLink(string $chatId, int $expireMinutes = 10, int $memberLimit = 1): array
    {
        $url = $this->apiBase . $this->botToken . '/createChatInviteLink';

        $expireDate = time() + ($expireMinutes * 60);

        $params = [
            'chat_id'      => $chatId,
            'expire_date'  => $expireDate,
            'member_limit' => $memberLimit,
            'name'         => 'CourseHub-' . date('YmdHis'),
        ];

        $response = $this->makeRequest($url, $params);

        if ($response && isset($response['ok']) && $response['ok'] === true) {
            return [
                'success'     => true,
                'invite_link' => $response['result']['invite_link'] ?? null,
                'expires_at'  => date('Y-m-d H:i:s', $expireDate),
                'error'       => null,
            ];
        }

        return [
            'success'     => false,
            'invite_link' => null,
            'expires_at'  => null,
            'error'       => $response['description'] ?? 'Failed to create invite link',
        ];
    }

    /**
     * Send a message to a chat (for admin notifications)
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        $url = $this->apiBase . $this->botToken . '/sendMessage';
        $response = $this->makeRequest($url, [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
        return $response && ($response['ok'] ?? false);
    }

    /**
     * Make a POST request to the Telegram Bot API
     */
    private function makeRequest(string $url, array $params): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('Telegram API cURL error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            error_log('Telegram API HTTP ' . $httpCode . ': ' . $response);
        }

        return $decoded;
    }
}
