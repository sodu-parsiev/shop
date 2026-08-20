<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Order'))
                    ->schema([
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
                        Textarea::make('message')
                            ->label(__('Message'))
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('line_summary')
                            ->label(__('Order lines'))
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
