<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

#[Fillable(['content'])]
class HomePageContent extends Model
{
    private const FALLBACK_CONTENT = [
        'form' => [
            'email' => 'EMAIL',
            'preferred_contact_method' => 'КАК СВЯЗАТЬСЯ',
            'contact_phone' => 'Позвонить',
            'contact_email' => 'Написать на email',
            'consent' => 'Согласен на обработку персональных данных',
            'privacy_link' => 'Политика конфиденциальности',
            'consent_link' => 'Согласие на обработку',
            'success_number_label' => 'Номер заявки:',
        ],
        'footer' => [
            'legal_heading' => 'ДОКУМЕНТЫ',
            'privacy' => 'Политика конфиденциальности',
            'consent' => 'Согласие на обработку',
            'requisites' => 'Реквизиты',
        ],
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->contentWithDefaults(), $key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function contentWithDefaults(): array
    {
        return array_replace_recursive(self::FALLBACK_CONTENT, $this->content ?? []);
    }
}
