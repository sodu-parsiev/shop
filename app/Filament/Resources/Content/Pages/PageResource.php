<?php

namespace App\Filament\Resources\Content\Pages;

use App\Filament\Resources\Content\Pages\Pages\ManagePages;
use App\Filament\Support\NavigationGroups;
use App\Models\Content\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::content();
    }

    public static function getModelLabel(): string
    {
        return __('Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('title')
                    ->label(__('Title'))
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->label(__('Body'))
                    ->rows(12)
                    ->columnSpanFull(),
                Select::make('page_type')
                    ->label(__('Page type'))
                    ->options([
                        'content' => __('Content'),
                        'legal' => __('Legal'),
                    ])
                    ->default('content')
                    ->required(),
                TextInput::make('sort_order')
                    ->label(__('Sort order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->label(__('Published'))
                    ->default(true),
                TextInput::make('meta_title')
                    ->label(__('Meta title'))
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->label(__('Meta description'))
                    ->columnSpanFull(),
                TextInput::make('canonical_url')
                    ->label(__('Canonical URL')),
                TextInput::make('og_image')
                    ->label(__('OG image')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable(),
                TextColumn::make('page_type')
                    ->label(__('Type'))
                    ->badge(),
                TextColumn::make('is_published')
                    ->label(__('Published'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePages::route('/'),
        ];
    }
}
