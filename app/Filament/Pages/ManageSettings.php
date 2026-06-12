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
use Filament\Forms\Components\Select;
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

        // JSON olarak saklanan çoklu seçim alanlarını diziye çevir
        $settings['closed_days'] = json_decode($settings['closed_days'] ?? '[]', true) ?: [];

        // Zil sesi: yalnızca public diskte duran (panelden yüklenmiş) dosyalar
        // FileUpload bileşeninde önizlenebilir; eski yol değerleri boş gösterilir.
        $bellSound = $settings['admin_notification_bell_sound'] ?? null;
        if ($bellSound && !\Illuminate\Support\Facades\Storage::disk('public')->exists($bellSound)) {
            unset($settings['admin_notification_bell_sound']);
        }

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
                                        TextInput::make('announcement_text')
                                            ->label('Üst Duyuru Çubuğu Metni')
                                            ->placeholder('Diyarbakır İçi Tüm Siparişlerde Aynı Gün Teslimat ve Canlı Kurye Takibi!')
                                            ->helperText('Sitenin en üstündeki renkli duyuru şeridinde gösterilir. Boş bırakılırsa şerit gizlenir.')
                                            ->columnSpanFull(),
                                        Toggle::make('order_tracking_widget_active')
                                            ->label('Sipariş Takip Sekmesi Aktif')
                                            ->helperText('Sitenin sağ kenarındaki "Sipariş Takip" sekmesi ve sorgulama kartı.')
                                            ->inline(false)
                                            ->default(true),
                                    ]),
                            ]),

                        Tab::make('Mobil Görünüm')
                            ->icon('heroicon-o-device-phone-mobile')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Toggle::make('mobile_marquee_active')
                                            ->label('Duyuru Kayan Yazı (Mobil)')
                                            ->helperText('Mobilde üst duyuru metni tek satır kayan yazı olur.')
                                            ->inline(false)
                                            ->default(true),
                                        Toggle::make('mobile_bottom_menu_active')
                                            ->label('Mobil Alt Menü Aktif')
                                            ->helperText('Ekranın altına yapışık ikonlu menü.')
                                            ->inline(false)
                                            ->default(true),
                                        Toggle::make('mobile_categories_first')
                                            ->label('Mobilde Kategoriler En Üstte')
                                            ->helperText('Ana sayfada kategori şeridi mobilde hero üstüne taşınır.')
                                            ->inline(false)
                                            ->default(true),
                                        Toggle::make('mobile_sidebar_quick_actions_active')
                                            ->label('Sidebar Hızlı İşlem Kartları')
                                            ->helperText('Sidebar üstündeki Hesabım/Sepet/Takip kartları veya Giriş/Kayıt butonları.')
                                            ->inline(false)
                                            ->default(true),
                                        Toggle::make('mobile_sidebar_contact_active')
                                            ->label('Sidebar İletişim Alanı')
                                            ->helperText('Sidebar altındaki telefon, WhatsApp ve e-posta bağlantıları.')
                                            ->inline(false)
                                            ->default(true),
                                        Toggle::make('whatsapp_float_active')
                                            ->label('Sabit WhatsApp Balonu')
                                            ->helperText('Sağ alttaki yüzen WhatsApp butonu. Mobil alt menüde WhatsApp olduğu için varsayılan kapalıdır.')
                                            ->inline(false)
                                            ->default(false),
                                    ]),
                                \Filament\Schemas\Components\Section::make('Mobil Menü İçerikleri')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('mobile_menus_link')
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString(
                                                'Mobil alt menü ve sidebar menü öğelerini (başlık, bağlantı, ikon, sıralama, silme) <a href="/admin/menus" style="text-decoration: underline; font-weight: 600;">Menüler</a> bölümünden yönetebilirsiniz: <strong>Mobil Alt Menü</strong> ve <strong>Mobil Sidebar Menü</strong>.'
                                            )),
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
                                Textarea::make('google_analytics_code')
                                    ->label('Google Analytics / Takip Kodu')
                                    ->rows(6)
                                    ->placeholder('<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX"></script>...')
                                    ->helperText('Google Analytics, Tag Manager veya benzeri takip kodunu script etiketleriyle birlikte yapıştırın. Sitenin <head> bölümüne eklenir.'),
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
                                Grid::make(4)
                                    ->schema([
                                        Select::make('timezone')
                                            ->label('Saat Dilimi')
                                            ->options([
                                                'Europe/Istanbul' => 'Türkiye (Europe/Istanbul)',
                                                'UTC' => 'UTC',
                                            ])
                                            ->default('Europe/Istanbul')
                                            ->required(),
                                        TextInput::make('prep_time_value')
                                            ->label('Hazırlık Süresi Değeri')
                                            ->numeric()
                                            ->default(120)
                                            ->required(),
                                        Select::make('prep_time_unit')
                                            ->label('Hazırlık Süresi Birimi')
                                            ->options([
                                                'minutes' => 'Dakika',
                                                'hours' => 'Saat',
                                                'days' => 'Gün',
                                            ])
                                            ->default('minutes')
                                            ->required(),
                                        TextInput::make('delivery_cutoff_time')
                                            ->label('Günlük Sipariş Kapanış Saati (Cutoff)')
                                            ->placeholder('Örn: 18:00')
                                            ->default('18:00')
                                            ->required(),
                                    ]),
                                \Filament\Forms\Components\CheckboxList::make('closed_days')
                                    ->label('Kapalı Günler (Bu günlerde teslimat yapılmaz)')
                                    ->options([
                                        '1' => 'Pazartesi',
                                        '2' => 'Salı',
                                        '3' => 'Çarşamba',
                                        '4' => 'Perşembe',
                                        '5' => 'Cuma',
                                        '6' => 'Cumartesi',
                                        '7' => 'Pazar',
                                    ])
                                    ->columns(4),
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
                                Grid::make(2)
                                    ->schema([
                                        \App\Forms\Components\MediaPicker::make('site_logo')
                                            ->label('Header Logosu'),
                                        \App\Forms\Components\MediaPicker::make('favicon')
                                            ->label('Favicon (Tarayıcı Sekme İkonu)')
                                            ->helperText('Kare oranlı PNG veya ICO önerilir (örn. 64x64).'),
                                    ]),
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

                        Tab::make('Footer Ayarları')
                            ->icon('heroicon-o-rectangle-group')
                            ->schema([
                                \App\Forms\Components\MediaPicker::make('footer_logo')
                                    ->label('Footer Logosu')
                                    ->helperText('Boş bırakılırsa footer\'da yazı tabanlı site adı gösterilir. Koyu zemin için açık renkli logo önerilir.')
                                    ->columnSpanFull(),
                                Textarea::make('footer_description')
                                    ->label('Footer Tanıtım Metni')
                                    ->rows(3)
                                    ->placeholder('Diyarbakır genelinde taze çiçek buketleri...')
                                    ->helperText('Footer\'ın sol sütununda logo altında gösterilen kısa tanıtım yazısı.')
                                    ->columnSpanFull(),
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

                        Tab::make('E-Posta (SMTP)')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('mail_smtp_active')
                                            ->label('SMTP ile Mail Gönderimi Aktif')
                                            ->helperText('Pasifken mailler sunucu varsayılanı ile (test ortamında log dosyasına) gönderilir.')
                                            ->columnSpanFull(),
                                        TextInput::make('mail_host')
                                            ->label('SMTP Sunucusu (Host)')
                                            ->placeholder('örn: smtp.gmail.com'),
                                        TextInput::make('mail_port')
                                            ->label('SMTP Port')
                                            ->numeric()
                                            ->placeholder('587'),
                                        TextInput::make('mail_username')
                                            ->label('SMTP Kullanıcı Adı')
                                            ->placeholder('mail@alanadiniz.com'),
                                        TextInput::make('mail_password')
                                            ->label('SMTP Şifre')
                                            ->password()
                                            ->revealable(),
                                        Select::make('mail_encryption')
                                            ->label('Şifreleme')
                                            ->options([
                                                'tls' => 'TLS (587)',
                                                'ssl' => 'SSL (465)',
                                                'none' => 'Yok',
                                            ])
                                            ->default('tls'),
                                        TextInput::make('mail_from_address')
                                            ->label('Gönderen E-Posta Adresi')
                                            ->email()
                                            ->placeholder('info@alanadiniz.com'),
                                        TextInput::make('mail_from_name')
                                            ->label('Gönderen Adı')
                                            ->placeholder('Lav Çiçekçilik'),
                                        TextInput::make('admin_notification_email')
                                            ->label('Site Sahibi Bildirim E-Postası')
                                            ->email()
                                            ->helperText('Yeni sipariş mailleri bu adrese gönderilir. Boşsa genel ayarlardaki site e-postası kullanılır.'),
                                    ]),
                                \Filament\Schemas\Components\Section::make('Mail Şablonları')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('mail_templates_link')
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString(
                                                'Müşteri ve site sahibine gönderilen maillerin içeriklerini <a href="/admin/mail-templates" style="text-decoration: underline; font-weight: 600;">Mail Şablonları</a> sayfasından düzenleyebilirsiniz.'
                                            )),
                                    ]),
                            ]),

                        Tab::make('Yönetici Bildirimleri')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('admin_audio_notification_active')
                                            ->label('Sesli Bildirim Aktif')
                                            ->default(true),
                                        Toggle::make('admin_desktop_notification_active')
                                            ->label('Masaüstü (Tarayıcı) Bildirimi Aktif')
                                            ->default(true),
                                        FileUpload::make('admin_notification_bell_sound')
                                            ->label('Zil Sesi Dosyası')
                                            ->disk('public')
                                            ->directory('audio')
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/mp4'])
                                            ->maxSize(5120)
                                            ->helperText('MP3, WAV veya OGG yükleyin. Boş bırakılırsa mevcut ses korunur; hiç ses yoksa varsayılan zil çalar.'),
                                        TextInput::make('admin_notification_volume')
                                            ->label('Ses Seviyesi (0.0 - 1.0)')
                                            ->numeric()
                                            ->default(1.0)
                                            ->required(),
                                        TextInput::make('admin_notification_polling_interval')
                                            ->label('Polleme Aralığı (Saniye)')
                                            ->numeric()
                                            ->default(15)
                                            ->required(),
                                        Select::make('admin_notification_condition')
                                            ->label('Hangi Siparişlerde Uyarı Verilsin?')
                                            ->options([
                                                'paid_only' => 'Sadece Ödenen / Yeni Siparişlerde (paid)',
                                                'all_orders' => 'Tüm Siparişlerde',
                                            ])
                                            ->default('paid_only')
                                            ->required(),
                                    ]),
                            ]),

                        Tab::make('Müşteri Bildirimleri (Push)')
                            ->icon('heroicon-o-paper-airplane')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('customer_push_active')
                                            ->label('Web Push Bildirimleri Aktif')
                                            ->default(true)
                                            ->columnSpanFull(),
                                        \Filament\Forms\Components\Placeholder::make('vapid_info')
                                            ->hiddenLabel()
                                            ->columnSpanFull()
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; border:1px solid rgb(228 228 231); border-radius:.6rem; padding:.8rem 1rem;">'
                                                . '<span style="font-size:.8125rem; color:rgb(113 113 122);">Müşteri bildirimlerinin çalışması için VAPID anahtarları zorunludur. Anahtarlarınız yoksa tek tıkla üretebilirsiniz.</span>'
                                                . '<button type="button" wire:click="generateVapidKeys" wire:loading.attr="disabled" style="background:rgb(24 24 27); color:#fff; font-size:.75rem; font-weight:700; padding:.5rem 1rem; border-radius:.5rem; border:none; cursor:pointer; white-space:nowrap;">VAPID Anahtarı Üret</button>'
                                                . '</div>'
                                            )),
                                        TextInput::make('customer_push_vapid_public_key')
                                            ->label('VAPID Public Key')
                                            ->placeholder('Tarayıcı push aboneliği için genel anahtar')
                                            ->columnSpanFull(),
                                        TextInput::make('customer_push_vapid_private_key')
                                            ->label('VAPID Private Key')
                                            ->placeholder('Bildirim göndermek için özel anahtar')
                                            ->columnSpanFull(),
                                        Textarea::make('push_msg_paid')
                                            ->label('Sipariş Alındı Bildirim Metni')
                                            ->default('Yeni siparişiniz başarıyla alındı!')
                                            ->rows(2),
                                        Textarea::make('push_msg_preparing')
                                            ->label('Hazırlanıyor Bildirim Metni')
                                            ->default('Siparişiniz hazırlanıyor.')
                                            ->rows(2),
                                        Textarea::make('push_msg_assigned_to_courier')
                                            ->label('Kuryeye Atandı Bildirim Metni')
                                            ->default('Siparişiniz kuryemize atandı.')
                                            ->rows(2),
                                        Textarea::make('push_msg_on_delivery')
                                            ->label('Dağıtımda Bildirim Metni')
                                            ->default('Siparişiniz teslim edilmek üzere yola çıktı!')
                                            ->rows(2),
                                        Textarea::make('push_msg_delivered')
                                            ->label('Teslim Edildi Bildirim Metni')
                                            ->default('Siparişiniz başarıyla teslim edildi!')
                                            ->rows(2),
                                        Textarea::make('push_msg_cancelled')
                                            ->label('İptal Edildi Bildirim Metni')
                                            ->default('Siparişiniz maalesef iptal edildi.')
                                            ->rows(2),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Müşteri web push bildirimleri için VAPID anahtar çifti üretir
     * ve form alanlarına doldurur (Kaydet ile kalıcı olur).
     */
    public function generateVapidKeys(): void
    {
        try {
            $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

            $this->data['customer_push_vapid_public_key'] = $keys['publicKey'];
            $this->data['customer_push_vapid_private_key'] = $keys['privateKey'];

            Notification::make()
                ->title('VAPID anahtarları üretildi.')
                ->body('Alanlara dolduruldu — kalıcı olması için Kaydet butonuna basın.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Anahtar üretilemedi: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            // Zil sesi boş bırakıldıysa mevcut ayar korunur (kayıt silinmez)
            if ($key === 'admin_notification_bell_sound' && blank($value)) {
                continue;
            }

            // Dizi değerleri (örn. closed_days) JSON olarak sakla
            if (is_array($value)) {
                $value = json_encode(array_values($value));
            }

            // Determine the group
            $group = 'general';
            if (in_array($key, ['meta_title', 'meta_description', 'google_analytics_code'])) {
                $group = 'seo';
            } elseif (in_array($key, ['min_order_amount', 'free_delivery_threshold', 'same_day_delivery_active', 'timezone', 'prep_time_value', 'prep_time_unit', 'delivery_cutoff_time', 'closed_days'])) {
                $group = 'ecommerce';
            } elseif (in_array($key, ['iyzico_test_mode', 'iyzico_api_key', 'iyzico_secret_key', 'iyzico_base_url'])) {
                $group = 'iyzico';
            } elseif (in_array($key, ['site_logo', 'favicon', 'header_search_active', 'header_account_active', 'header_favorites_active', 'header_cart_active'])) {
                $group = 'header';
            } elseif (in_array($key, ['footer_logo', 'footer_description'])) {
                $group = 'footer';
            } elseif (in_array($key, ['mobile_marquee_active', 'mobile_bottom_menu_active', 'mobile_categories_first', 'mobile_sidebar_quick_actions_active', 'mobile_sidebar_contact_active', 'whatsapp_float_active'])) {
                $group = 'mobile';
            } elseif (in_array($key, ['google_auth_active', 'google_client_id', 'google_client_secret'])) {
                $group = 'google_auth';
            } elseif (in_array($key, ['mail_smtp_active', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name', 'admin_notification_email'])) {
                $group = 'mail';
            } elseif (in_array($key, ['admin_audio_notification_active', 'admin_desktop_notification_active', 'admin_notification_bell_sound', 'admin_notification_volume', 'admin_notification_polling_interval', 'admin_notification_condition'])) {
                $group = 'admin_notification';
            } elseif (in_array($key, ['customer_push_active', 'customer_push_vapid_public_key', 'customer_push_vapid_private_key', 'push_msg_paid', 'push_msg_preparing', 'push_msg_assigned_to_courier', 'push_msg_on_delivery', 'push_msg_delivered', 'push_msg_cancelled'])) {
                $group = 'customer_notification';
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
