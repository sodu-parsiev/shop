<?php

namespace App\Filament\Pages;

use App\Filament\Support\NavigationGroups;
use App\Models\Content\HomePageContent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class ManageHomePageContent extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Главная страница';

    protected static ?string $title = 'Главная страница';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::content();
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->record()->contentWithDefaults());
    }

    protected function record(): HomePageContent
    {
        return HomePageContent::query()->firstOrCreate(['id' => 1], ['content' => []]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_site')
                ->label('Открыть сайт')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('gray')
                ->url(route('home'))
                ->openUrlInNewTab(),
        ];
    }

    protected function hint(string $text): Text
    {
        return Text::make($text)
            ->color('gray')
            ->size(TextSize::Small)
            ->columnSpanFull();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Разделы')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Навигация')
                            ->icon(Heroicon::OutlinedBars3)
                            ->schema([
                                $this->hint('Шапка сайта: логотип, пункты меню и кнопка заявки — видны на каждой странице.'),
                                TextInput::make('nav.tagline')->label('Подпись под логотипом'),
                                TextInput::make('nav.catalog')->label('Каталог'),
                                TextInput::make('nav.production')->label('Производство'),
                                TextInput::make('nav.customization')->label('Кастомизация'),
                                TextInput::make('nav.terms')->label('Условия'),
                                TextInput::make('nav.contacts')->label('Контакты'),
                                TextInput::make('nav.apply_button')->label('Кнопка «Заявка»'),
                            ])
                            ->columns(3),

                        Tab::make('Hero')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                $this->hint('Первый экран главной страницы — самое заметное место на сайте.'),
                                TextInput::make('hero.tag_production')->label('Тег: производство'),
                                TextInput::make('hero.tag_b2b')->label('Тег: B2B'),
                                TextInput::make('hero.headline_main')->label('Заголовок'),
                                TextInput::make('hero.headline_accent')->label('Заголовок (акцент, розовый)'),
                                Textarea::make('hero.subcopy')->label('Подзаголовок')->columnSpanFull(),
                                Textarea::make('hero.callout')->label('Плашка-уточнение')->columnSpanFull(),
                                TextInput::make('hero.cta_primary')->label('Кнопка 1'),
                                TextInput::make('hero.cta_secondary')->label('Кнопка 2'),
                                TextInput::make('hero.hero_badge_value')->label('Бейдж на фото: значение'),
                                TextInput::make('hero.hero_badge_label')->label('Бейдж на фото: подпись'),
                                TextInput::make('hero.top_ticker')->label('Бегущая строка (верхняя, над шапкой)')->columnSpanFull(),
                                TextInput::make('hero.bottom_ticker')->label('Бегущая строка (нижняя, под hero)')->columnSpanFull(),
                                Repeater::make('hero.stats')
                                    ->label('Статистика')
                                    ->schema([
                                        TextInput::make('value')->label('Значение'),
                                        TextInput::make('label')->label('Подпись'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['value'] ?? null),
                            ])
                            ->columns(2),

                        Tab::make('01 · Почему «Свой Ход»')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                TextInput::make('why.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('why.heading')->label('Заголовок'),
                                TextInput::make('why.subheading')->label('Подзаголовок'),
                                Repeater::make('why.cards')
                                    ->label('Карточки')
                                    ->schema([
                                        TextInput::make('number')->label('Номер'),
                                        TextInput::make('title')->label('Заголовок'),
                                        Textarea::make('description')->label('Описание'),
                                        Toggle::make('highlighted')->label('Выделена (тёмный фон)'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),

                        Tab::make('02 · Каталог и производство')
                            ->icon(Heroicon::OutlinedSquares2x2)
                            ->schema([
                                $this->hint('Тексты каталога, фильтров и карточек товара. Сами товары редактируются в разделе «Товары».'),
                                TextInput::make('catalog.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('catalog.heading')->label('Заголовок'),
                                TextInput::make('catalog.heading_accent')->label('Заголовок (акцент)'),
                                TextInput::make('catalog.count_label')->label('Подпись количества моделей'),
                                TextInput::make('catalog.availability_all_label')->label('Фильтр наличия: «Все модели»'),
                                TextInput::make('catalog.availability_stock_label')->label('Фильтр наличия: «На складе»'),
                                TextInput::make('catalog.availability_order_label')->label('Фильтр наличия: «Под заказ»'),
                                TextInput::make('catalog.filter_category_label')->label('Подпись поля «Категория»'),
                                TextInput::make('catalog.filter_all_label')->label('Категория: значение «Все»'),
                                TextInput::make('catalog.filter_size_grid_label')->label('Размерная сетка: placeholder'),
                                TextInput::make('catalog.filter_density_label')->label('Подпись поля «Желаемая плотность»'),
                                TextInput::make('catalog.filter_density_default')->label('Плотность: значение по умолчанию'),
                                Repeater::make('catalog.filter_density_options')
                                    ->label('Плотность: варианты выбора')
                                    ->schema([
                                        TextInput::make('value')->label('Текст'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['value'] ?? null),
                                TextInput::make('catalog.filter_size_label')->label('Подпись поля «Размерная сетка»'),
                                Repeater::make('catalog.filter_size_options')
                                    ->label('Размерная сетка: варианты выбора')
                                    ->schema([
                                        TextInput::make('value')->label('Текст'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['value'] ?? null),
                                TextInput::make('catalog.filter_qty_label')->label('Подпись поля «Объём партии»'),
                                Textarea::make('catalog.price_note')->label('Примечание о цене')->columnSpanFull(),
                                TextInput::make('catalog.badge_in_stock')->label('Бейдж: на складе'),
                                TextInput::make('catalog.badge_made_to_order')->label('Бейдж: под заказ'),
                                TextInput::make('catalog.kicker')->label('Надпись над карточкой (слева)'),
                                TextInput::make('catalog.moq_prefix')->label('Приставка к МОQ (напр. «От»)'),
                                TextInput::make('catalog.density_label')->label('Подпись: плотность'),
                                TextInput::make('catalog.sizes_label')->label('Подпись: размеры'),
                                TextInput::make('catalog.sizes_unit')->label('Единица измерения размеров (напр. «размеров»)'),
                                TextInput::make('catalog.qty_unit')->label('Единица измерения количества (напр. «шт.»)'),
                                TextInput::make('catalog.price_label')->label('Строка стоимости: подпись'),
                                TextInput::make('catalog.price_value')->label('Строка стоимости: значение'),
                                TextInput::make('catalog.price_note_small')->label('Строка стоимости: примечание'),
                                TextInput::make('catalog.cta_stock')->label('Кнопка (в наличии)'),
                                TextInput::make('catalog.cta_made_to_order')->label('Кнопка (под заказ)'),
                            ])
                            ->columns(2),

                        Tab::make('03 · Для продавцов маркетплейсов')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                TextInput::make('sellers.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('sellers.heading')->label('Заголовок'),
                                TextInput::make('sellers.subheading')->label('Подзаголовок'),
                                TextInput::make('sellers.cta')->label('Кнопка'),
                                TextInput::make('sellers.caption')->label('Подпись внизу'),
                                Repeater::make('sellers.items')
                                    ->label('Список')
                                    ->schema([
                                        TextInput::make('label')->label('Текст пункта'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                            ])
                            ->columns(2),

                        Tab::make('04 · О производстве')
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->schema([
                                TextInput::make('process.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('process.heading')->label('Заголовок'),
                                TextInput::make('process.subheading')->label('Подзаголовок'),
                                Repeater::make('process.stages')
                                    ->label('Этапы')
                                    ->schema([
                                        TextInput::make('title')->label('Название'),
                                        TextInput::make('subtitle')->label('Подпись'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),

                        Tab::make('05 · Печать и кастомизация')
                            ->icon(Heroicon::OutlinedPrinter)
                            ->schema([
                                TextInput::make('customization_section.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('customization_section.heading')->label('Заголовок'),
                                TextInput::make('customization_section.heading_accent')->label('Заголовок (акцент)'),
                                TextInput::make('customization_section.cta')->label('Кнопка на выделенной карточке'),
                            ])
                            ->columns(2),

                        Tab::make('06 · Условия сотрудничества')
                            ->icon(Heroicon::OutlinedCheckBadge)
                            ->schema([
                                TextInput::make('terms.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('terms.heading_main')->label('Заголовок'),
                                TextInput::make('terms.heading_accent')->label('Заголовок (акцент, розовый)'),
                                Repeater::make('terms.stats')
                                    ->label('Блоки условий')
                                    ->schema([
                                        TextInput::make('value')->label('Значение'),
                                        TextInput::make('label')->label('Подпись'),
                                        Textarea::make('description')->label('Описание'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                            ]),

                        Tab::make('FAQ')
                            ->icon(Heroicon::OutlinedQuestionMarkCircle)
                            ->schema([
                                $this->hint('Заголовок блока вопросов и ответов. Сами вопросы редактируются в разделе «Вопросы и ответы».'),
                                TextInput::make('faq.heading')->label('Надпись сверху (эйброу)'),
                                TextInput::make('faq.subheading_main')->label('Заголовок'),
                                TextInput::make('faq.subheading_accent')->label('Заголовок (акцент, розовый)'),
                            ])
                            ->columns(3),

                        Tab::make('Личный кабинет')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                $this->hint('Анонс личного кабинета покупателя на главной странице.'),
                                TextInput::make('portal_callout.eyebrow')->label('Надпись сверху'),
                                TextInput::make('portal_callout.heading')->label('Заголовок'),
                                Textarea::make('portal_callout.description')->label('Описание')->columnSpanFull(),
                                TextInput::make('portal_callout.cta')->label('Кнопка'),
                            ])
                            ->columns(2),

                        Tab::make('07 · Начать сотрудничество')
                            ->icon(Heroicon::OutlinedMegaphone)
                            ->schema([
                                TextInput::make('cta_section.eyebrow')->label('Надпись рядом с номером секции'),
                                TextInput::make('cta_section.heading_main')->label('Заголовок'),
                                TextInput::make('cta_section.heading_accent')->label('Заголовок (акцент, розовый)'),
                                Textarea::make('cta_section.subcopy')->label('Подзаголовок')->columnSpanFull(),
                                TextInput::make('cta_section.email_label')->label('Подпись поля Email'),
                                TextInput::make('cta_section.email')->label('Email'),
                                TextInput::make('cta_section.address_label')->label('Подпись поля Адрес'),
                                TextInput::make('cta_section.address')->label('Адрес'),
                            ])
                            ->columns(2),

                        Tab::make('Форма заявки')
                            ->icon(Heroicon::OutlinedClipboardDocumentList)
                            ->schema([
                                $this->hint('Подписи полей и сообщений формы заявки внизу главной страницы.'),
                                TextInput::make('form.company')->label('Поле: компания'),
                                TextInput::make('form.contact_person')->label('Поле: контактное лицо'),
                                TextInput::make('form.phone')->label('Поле: телефон'),
                                TextInput::make('form.email')->label('Поле: email'),
                                TextInput::make('form.preferred_contact_method')->label('Поле: способ связи'),
                                TextInput::make('form.contact_phone')->label('Вариант связи: телефон'),
                                TextInput::make('form.contact_email')->label('Вариант связи: email'),
                                TextInput::make('form.volume')->label('Поле: объём'),
                                TextInput::make('form.comment')->label('Поле: комментарий'),
                                TextInput::make('form.consent')->label('Текст согласия')->columnSpanFull(),
                                TextInput::make('form.privacy_link')->label('Ссылка: политика конфиденциальности'),
                                TextInput::make('form.consent_link')->label('Ссылка: согласие на обработку'),
                                TextInput::make('form.submit')->label('Кнопка отправки'),
                                Textarea::make('form.helper')->label('Пояснение под формой')->columnSpanFull(),
                                TextInput::make('form.success')->label('Сообщение об успехе')->columnSpanFull(),
                                TextInput::make('form.success_number_label')->label('Подпись номера заявки'),
                                Repeater::make('form.volume_options')
                                    ->label('Варианты объёма')
                                    ->schema([
                                        TextInput::make('key')->label('Ключ (10, 100, 500 и т.п.)'),
                                        TextInput::make('label')->label('Текст'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                            ])
                            ->columns(2),

                        Tab::make('Футер')
                            ->icon(Heroicon::OutlinedRectangleStack)
                            ->schema([
                                TextInput::make('footer.tagline')->label('Слоган'),
                                TextInput::make('footer.nav_heading')->label('Заголовок: навигация'),
                                TextInput::make('footer.order_heading')->label('Заголовок: заказ'),
                                TextInput::make('footer.legal_heading')->label('Заголовок: документы'),
                                TextInput::make('footer.privacy')->label('Документ: политика конфиденциальности'),
                                TextInput::make('footer.consent')->label('Документ: согласие на обработку'),
                                TextInput::make('footer.requisites')->label('Документ: реквизиты'),
                                TextInput::make('footer.size_guide')->label('Документ: как определить размер'),
                                TextInput::make('footer.contacts_heading')->label('Заголовок: контакты'),
                                TextInput::make('footer.copyright')->label('Копирайт'),
                                TextInput::make('footer.made_for')->label('Подпись внизу'),
                                Repeater::make('footer.order_items')
                                    ->label('Пункты «Заказ»')
                                    ->schema([
                                        TextInput::make('label')->label('Текст пункта'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                            ])
                            ->columns(2),

                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema([
                                $this->hint('Технические настройки для поисковиков и соцсетей. Обычно не требуют частых правок.'),
                                TextInput::make('seo.title')->label('Заголовок (Title)')->columnSpanFull(),
                                Textarea::make('seo.description')->label('Meta-описание')->columnSpanFull(),
                                Textarea::make('seo.keywords')->label('Ключевые слова')->columnSpanFull(),
                                TextInput::make('seo.canonical_url')->label('Канонический URL'),
                                TextInput::make('seo.og_title')->label('Заголовок OpenGraph'),
                                Textarea::make('seo.og_description')->label('Описание OpenGraph')->columnSpanFull(),
                                TextInput::make('seo.og_image')->label('Изображение OpenGraph'),
                                TextInput::make('seo.icon')->label('Иконка'),
                                TextInput::make('seo.organization_name')->label('Название организации'),
                                Textarea::make('seo.organization_description')->label('Описание организации')->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record()->update(['content' => $data]);

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
