<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('integration_settings')]
#[Fillable(['bitrix24_enabled', 'bitrix24_webhook_url'])]
#[Hidden(['bitrix24_webhook_url'])]
class IntegrationSettings extends Model
{
    protected function casts(): array
    {
        return [
            'bitrix24_enabled' => 'boolean',
            'bitrix24_webhook_url' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    /**
     * The webhook base URL without a trailing slash, or null when leads
     * should not be sent.
     */
    public function bitrix24WebhookUrl(): ?string
    {
        if (! $this->bitrix24_enabled || blank($this->bitrix24_webhook_url)) {
            return null;
        }

        return rtrim($this->bitrix24_webhook_url, '/');
    }
}
