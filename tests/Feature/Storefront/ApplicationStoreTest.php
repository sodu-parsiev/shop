<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;

test('a valid submission creates an application with a composed message and no email', function () {
    $response = $this->post(route('applications.store'), [
        'company' => 'ООО Ромашка',
        'customer_name' => 'Иван Иванов',
        'phone' => '+7 999 123-45-67',
        'volume' => '10000_25000',
        'message' => 'Нужна консультация по плотности ткани.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('applicationSubmitted', true);

    $application = Application::query()->latest('id')->first();

    expect($application)->not->toBeNull();
    expect($application->company)->toBe('ООО Ромашка');
    expect($application->customer_name)->toBe('Иван Иванов');
    expect($application->phone)->toBe('+7 999 123-45-67');
    expect($application->status)->toBe(ApplicationStatus::New);
    expect($application->email)->toBeNull();
    expect($application->message)->toContain('10 000–25 000 шт.');
    expect($application->message)->toContain('Нужна консультация по плотности ткани.');
});

test('volume without a comment composes a message with just the volume label', function () {
    $this->post(route('applications.store'), [
        'customer_name' => 'Пётр Петров',
        'phone' => '+7 999 000-00-00',
        'volume' => '5000_10000',
    ]);

    $application = Application::query()->latest('id')->first();

    expect($application->message)->toBe('5 000–10 000 шт.');
});

test('missing required fields fail validation and create no application', function () {
    $response = $this->post(route('applications.store'), [
        'customer_name' => '',
        'phone' => '',
        'volume' => '',
    ]);

    $response->assertSessionHasErrors(['customer_name', 'phone', 'volume']);
    expect(Application::query()->count())->toBe(0);
});

test('an invalid volume choice fails validation', function () {
    $response = $this->post(route('applications.store'), [
        'customer_name' => 'Иван Иванов',
        'phone' => '+7 999 123-45-67',
        'volume' => 'not_a_real_option',
    ]);

    $response->assertSessionHasErrors(['volume']);
});
