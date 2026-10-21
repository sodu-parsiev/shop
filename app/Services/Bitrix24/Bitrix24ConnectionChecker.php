<?php

namespace App\Services\Bitrix24;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Bitrix24ConnectionChecker
{
    /**
     * Confirms the webhook is accepted and can read CRM leads, which is the
     * same scope crm.lead.add needs.
     *
     * @throws Bitrix24ConnectionException
     */
    public function check(string $webhookUrl): void
    {
        $webhookUrl = rtrim($webhookUrl, '/');

        try {
            $response = Http::timeout(10)->asJson()->post("{$webhookUrl}/crm.lead.fields.json");
        } catch (ConnectionException) {
            throw new Bitrix24ConnectionException('Не удалось подключиться к Битрикс24. Проверьте адрес портала.');
        }

        if ($response->successful() && is_array($response->json('result'))) {
            return;
        }

        throw new Bitrix24ConnectionException(match ($response->json('error')) {
            'insufficient_scope' => 'У вебхука нет доступа к CRM. Включите право «CRM (crm)» в настройках вебхука.',
            'INVALID_CREDENTIALS', 'NO_AUTH_FOUND' => 'Битрикс24 отклонил вебхук: неверный токен или ID пользователя.',
            default => 'Битрикс24 ответил ошибкой: '.($response->json('error_description') ?? "HTTP {$response->status()}"),
        });
    }
}
