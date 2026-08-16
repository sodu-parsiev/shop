<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;

class ApplicationControllerService
{
    /**
     * @var array<string, string>
     */
    private const VOLUME_LABELS = [
        '5000_10000' => '5 000–10 000 шт.',
        '10000_25000' => '10 000–25 000 шт.',
        '25000_plus' => 'Более 25 000 шт.',
    ];

    public function createFromRequest(StoreApplicationRequest $request): Application
    {
        $volumeLabel = self::VOLUME_LABELS[$request->validated('volume')];
        $comment = $request->validated('message');

        $message = $comment ? "{$volumeLabel}\n\n{$comment}" : $volumeLabel;

        return Application::create([
            'customer_name' => $request->validated('customer_name'),
            'company' => $request->validated('company'),
            'phone' => $request->validated('phone'),
            'message' => $message,
            'status' => ApplicationStatus::New,
        ]);
    }
}
