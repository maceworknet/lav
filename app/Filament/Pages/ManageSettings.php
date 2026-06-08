<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $title = 'Sistem Ayarları';
    protected static ?string $navigationLabel = 'Genel Ayarlar';
    protected static \UnitEnum|string|null $navigationGroup = 'Site Yönetimi';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Tabs::make('Ayarlar')
                    ->tabs([
                        Tab::make('Genel Ayarlar')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Site Adı')
                                            ->required(),
                                        TextInput::make('site_email')
                                            ->label('E-posta Adresi')
                                            ->email()
                                            ->required(),
                                        TextInput::make('site_phone')
                                            ->label('Telefon Numarası')
                                            ->required(),
                                        TextInput::make('site_whatsapp')
                                            ->label('WhatsApp Numarası (Uluslararası format, örn: 90532...)')
                                            ->required(),
                                        TextInput::make('working_hours')
                                            ->label('Çalışma Saatleri')
                                            ->required(),
                                        Textarea::make('site_address')
                                            ->label('Fiziksel Adres')
                                            ->columnSpanFull()
                                            ->required(),
                                    ]),
                            ]),
                        
                        Tab::make('SEO Ayarları')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Varsayılan SEO Başlığı')
                                    ->required(),
                                Textarea::make('meta_description')
                                    ->label('Varsayılan SEO Açıklaması')
                                    ->required(),
                            ]),
                        
                        Tab::make('E-Ticaret Kuralları')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('min_order_amount')
                                            ->label('Minimum Sipariş Tutarı (TL)')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->required(),
                                        TextInput::make('free_delivery_threshold')
                                            ->label('Ücretsiz Teslimat Limiti (TL)')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->required(),
                                        Toggle::make('same_day_delivery_active')
                                            ->label('Aynı Gün Teslimat Aktif')
                                            ->inline(false),
                                    ]),
                            ]),
                        
                        Tab::make('iyzico Ödeme Entegrasyonu')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('iyzico_test_mode')
                                            ->label('Test Modu (Sandbox) Aktif')
                                            ->columnSpanFull(),
                                        TextInput::make('iyzico_api_key')
                                            ->label('iyzico API Key')
                                            ->password()
                                            ->required(),
                                        TextInput::make('iyzico_secret_key')
                                            ->label('iyzico Secret Key')
                                            ->password()
                                            ->required(),
                                        TextInput::make('iyzico_base_url')
                                            ->label('iyzico Base URL')
                                            ->required(),
                                    ]),
                            ]),

                        Tab::make('Header Ayarları')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                FileUpload::make('site_logo')
                                    ->label('Site Logosu')
                                    ->directory('logos')
                                    ->image()
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                                Grid::make(4)
                                    ->schema([
                                        Toggle::make('header_search_active')
                                            ->label('Arama İkonu Aktif')
                                            ->default(true),
                                        Toggle::make('header_account_active')
                                            ->label('Hesabım İkonu Aktif')
                                            ->default(true),
                                        Toggle::make('header_favorites_active')
                                            ->label('Favoriler İkonu Aktif')
                                            ->default(true),
                                        Toggle::make('header_cart_active')
                                            ->label('Sepetim İkonu Aktif')
                                            ->default(true),
                                    ]),
                            ]),

                        Tab::make('Google Giriş Entegrasyonu')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('google_auth_active')
                                            ->label('Google ile Giriş/Kayıt Aktif')
                                            ->columnSpanFull(),
                                        TextInput::make('google_client_id')
                                            ->label('Google Client ID')
                                            ->placeholder('Google Console\'dan alınan OAuth Client ID'),
                                        TextInput::make('google_client_secret')
                                            ->label('Google Client Secret')
                                            ->password()
                                            ->placeholder('Google Console\'dan alınan OAuth Client Secret'),
                                        TextInput::make('google_redirect_url')
                                            ->label('Yönlendirme URI (Redirect URI)')
                                            ->default(fn () => url('/auth/google/callback'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull()
                                            ->helperText('Google Developer Console\'da "Authorized redirect URIs" alanına bu adresi eklemelisiniz.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            // Determine the group
            $group = 'general';
            if (in_array($key, ['meta_title', 'meta_description'])) {
                $group = 'seo';
            } elseif (in_array($key, ['min_order_amount', 'free_delivery_threshold', 'same_day_delivery_active'])) {
                $group = 'ecommerce';
            } elseif (in_array($key, ['iyzico_test_mode', 'iyzico_api_key', 'iyzico_secret_key', 'iyzico_base_url'])) {
                $group = 'iyzico';
            } elseif (in_array($key, ['site_logo', 'header_search_active', 'header_account_active', 'header_favorites_active', 'header_cart_active'])) {
                $group = 'header';
            } elseif (in_array($key, ['google_auth_active', 'google_client_id', 'google_client_secret'])) {
                $group = 'google_auth';
            }

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $group
                ]
            );
        }

        Notification::make()
            ->title('Ayarlar başarıyla kaydedildi.')
            ->success()
            ->send();
    }
}
