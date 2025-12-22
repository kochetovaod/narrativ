<?php

namespace App\Filament\Pages;

use App\Settings\AboutSettings;
use App\Settings\BrandSettings;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelSettings\Settings;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $navigationGroup = 'Настройки';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormDefaults());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Контакты')
                    ->schema([
                        Forms\Components\TagsInput::make('contacts.phones')
                            ->label('Телефоны')
                            ->placeholder('+1 234 567 89')
                            ->helperText('Список телефонов компании')
                            ->separator(';'),
                        Forms\Components\TextInput::make('contacts.email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('contacts.address')
                            ->label('Адрес')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contacts.map_coordinates')
                            ->label('Координаты/карта')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('contacts.working_hours')
                            ->label('График работы')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('contacts.social_links')
                            ->label('Соцсети')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label('Ссылка')
                                    ->url()
                                    ->required()
                                    ->maxLength(2048),
                            ])
                            ->addActionLabel('Добавить ссылку')
                            ->reorderable()
                            ->default([])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('О нас')
                    ->schema([
                        Forms\Components\RichEditor::make('about.text')
                            ->label('Текст О нас')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Брендинг')
                    ->schema([
                        Forms\Components\TextInput::make('brand.company_name')
                            ->label('Название компании')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('brand.logo.path')
                            ->label('Логотип')
                            ->image()
                            ->imageEditor()
                            ->directory('branding/logo')
                            ->imagePreviewHeight('150'),
                        Forms\Components\TextInput::make('brand.logo.alt')
                            ->label('Alt логотипа')
                            ->required(fn (Get $get) => filled($get('brand.logo.path')))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('brand.logo.title')
                            ->label('Title логотипа')
                            ->required(fn (Get $get) => filled($get('brand.logo.path')))
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('brand.favicon.path')
                            ->label('Favicon')
                            ->image()
                            ->directory('branding/favicon')
                            ->imagePreviewHeight('80'),
                        Forms\Components\TextInput::make('brand.favicon.alt')
                            ->label('Alt фавиконки')
                            ->required(fn (Get $get) => filled($get('brand.favicon.path')))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('brand.favicon.title')
                            ->label('Title фавиконки')
                            ->required(fn (Get $get) => filled($get('brand.favicon.path')))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Базовые SEO')
                    ->schema([
                        Forms\Components\TextInput::make('seo.default_title')
                            ->label('Default title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('seo.default_description')
                            ->label('Default description')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        Validator::make($state, $this->getValidationRules())->validate();

        $this->persistSettings($state);

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }

    protected function getFormDefaults(): array
    {
        return [
            'contacts' => app(ContactSettings::class)->toArray(),
            'about' => app(AboutSettings::class)->toArray(),
            'brand' => app(BrandSettings::class)->toArray(),
            'seo' => app(SeoSettings::class)->toArray(),
        ];
    }

    protected function getValidationRules(): array
    {
        return array_merge(
            $this->prefixedRules('contacts', ContactSettings::rules()),
            $this->prefixedRules('about', AboutSettings::rules()),
            $this->prefixedRules('brand', BrandSettings::rules()),
            $this->prefixedRules('seo', SeoSettings::rules()),
        );
    }

    protected function prefixedRules(string $prefix, array $rules): array
    {
        return collect($rules)
            ->mapWithKeys(fn ($rules, $key) => ["{$prefix}.{$key}" => $rules])
            ->all();
    }

    protected function persistSettings(array $state): void
    {
        $this->applyState(app(ContactSettings::class), $state['contacts'] ?? []);
        $this->applyState(app(AboutSettings::class), $state['about'] ?? []);
        $this->applyState(app(BrandSettings::class), $state['brand'] ?? []);
        $this->applyState(app(SeoSettings::class), $state['seo'] ?? []);
    }

    protected function applyState(Settings $settings, array $values): void
    {
        foreach ($values as $key => $value) {
            $settings->{$key} = $value;
        }

        $settings->save();
    }
}
