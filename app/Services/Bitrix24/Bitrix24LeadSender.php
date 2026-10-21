<?php

namespace App\Services\Bitrix24;

use App\Enums\ContactMethod;
use App\Models\Order;
use App\Models\Settings\IntegrationSettings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Bitrix24LeadSender
{
    public function send(Order $order): void
    {
        $webhookUrl = IntegrationSettings::current()->bitrix24WebhookUrl();

        if ($webhookUrl === null || $order->bitrix_lead_id) {
            return;
        }

        try {
            // Only retry when the request never reached Bitrix24 — retrying a
            // rejected or slow POST could create the same lead twice.
            $response = Http::timeout(10)
                ->retry(2, 300, fn (Throwable $e): bool => $e instanceof ConnectionException, throw: false)
                ->asJson()
                ->post("{$webhookUrl}/crm.lead.add.json", [
                    'fields' => $this->leadFields($order),
                    'params' => ['REGISTER_SONET_EVENT' => 'Y'],
                ]);

            $leadId = filter_var($response->json('result'), FILTER_VALIDATE_INT);

            if ($response->failed() || $leadId === false) {
                Log::warning('Bitrix24 lead creation failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'error' => $response->json('error'),
                    'error_description' => $response->json('error_description'),
                ]);

                return;
            }

            $order->forceFill(['bitrix_lead_id' => $leadId])->save();
        } catch (Throwable $e) {
            // The webhook URL embeds its secret token, so keep it out of the log.
            Log::warning('Bitrix24 lead creation threw', [
                'order_id' => $order->id,
                'message' => str_replace($webhookUrl, '[bitrix24-webhook]', $e->getMessage()),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function leadFields(Order $order): array
    {
        return array_filter([
            'TITLE' => "Заявка {$order->request_number} с сайта",
            'NAME' => $order->customer_name,
            'COMPANY_TITLE' => $order->company,
            'PHONE' => [['VALUE' => $order->phone, 'VALUE_TYPE' => 'WORK']],
            'EMAIL' => [['VALUE' => $order->email, 'VALUE_TYPE' => 'WORK']],
            'SOURCE_ID' => 'WEB',
            'SOURCE_DESCRIPTION' => $order->source_url ?? $order->landing_url,
            'COMMENTS' => $this->comments($order),
            'UTM_SOURCE' => $order->utm_source,
            'UTM_MEDIUM' => $order->utm_medium,
            'UTM_CAMPAIGN' => $order->utm_campaign,
            'UTM_CONTENT' => $order->utm_content,
            'UTM_TERM' => $order->utm_term,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function comments(Order $order): string
    {
        $contactMethod = ContactMethod::tryFrom((string) $order->preferred_contact_method)?->label()
            ?? $order->preferred_contact_method;

        return collect([
            "Номер заявки: {$order->request_number}",
            "Связь: {$contactMethod}",
            $order->line_summary ? "Позиции:\n{$order->line_summary}" : null,
            $order->message ? "Сообщение:\n{$order->message}" : null,
            $order->yclid ? "yclid: {$order->yclid}" : null,
        ])->filter()->implode("\n\n");
    }
}
