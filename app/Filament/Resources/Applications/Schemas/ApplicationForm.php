<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request')
                    ->schema([
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('company'),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('phone'),
                        Textarea::make('message')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Handling')
                    ->schema([
                        Select::make('status')
                            ->options(collect(ApplicationStatus::cases())->mapWithKeys(
                                fn (ApplicationStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Assigned manager')
                            ->relationship('assignedManager', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('internal_notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
