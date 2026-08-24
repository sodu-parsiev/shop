<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\ContactMethod;
use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Order'))
                    ->schema([
                        TextInput::make('request_number')
                            ->label(__('Request number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('customer_name')
                            ->label(__('Customer name'))
                            ->required(),
                        TextInput::make('company')
                            ->label(__('Company')),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email(),
                        TextInput::make('phone')
                            ->label(__('Phone')),
                        Select::make('preferred_contact_method')
                            ->label(__('Preferred contact method'))
                            ->options(collect(ContactMethod::cases())->mapWithKeys(
                                fn (ContactMethod $method) => [$method->value => $method->label()]
                            )),
                        Textarea::make('message')
                            ->label(__('Message'))
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('Order lines'))
                    ->visible(fn (?Order $record): bool => $record !== null)
                    ->schema([
                        View::make('filament.resources.orders.order-lines'),
                    ]),
                Section::make(__('Attribution'))
                    ->schema([
                        TextInput::make('landing_url')
                            ->label(__('Landing URL'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('source_url')
                            ->label(__('Source URL'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('referrer_url')
                            ->label(__('Referrer URL'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('utm_source')
                            ->label(__('UTM source'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('utm_medium')
                            ->label(__('UTM medium'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('utm_campaign')
                            ->label(__('UTM campaign'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('utm_content')
                            ->label(__('UTM content'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('utm_term')
                            ->label(__('UTM term'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('consent_accepted_at')
                            ->label(__('Consent accepted at'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('consent_ip')
                            ->label(__('Consent IP'))
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsed(),
                Section::make(__('Handling'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(collect(OrderStatus::cases())->mapWithKeys(
                                fn (OrderStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required(),
                        Select::make('assigned_to')
                            ->label(__('Assigned manager'))
                            ->relationship('assignedManager', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('internal_notes')
                            ->label(__('Internal notes'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
