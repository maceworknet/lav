<?php

namespace App\Filament\Resources\MailTemplates;

use App\Filament\Resources\MailTemplates\Pages\EditMailTemplate;
use App\Filament\Resources\MailTemplates\Pages\ListMailTemplates;
use App\Models\MailTemplate;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Mail Şablonları';

    protected static ?string $modelLabel = 'Mail Şablonu';

    protected static ?string $pluralModelLabel = 'Mail Şablonları';

    protected static \UnitEnum|string|null $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Şablon Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Şablon Adı')
                            ->required(),
                        TextInput::make('key')
                            ->label('Şablon Anahtarı (Sistem)')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Sistemin şablonu tanıması için kullanılır, değiştirilemez.'),
                        TextInput::make('subject')
                            ->label('Mail Konusu')
                            ->required()
                            ->columnSpanFull(),
                        Toggle::make('is_html')
                            ->label('HTML Şablon')
                            ->helperText('Açıksa içerik HTML kodu olarak gönderilir (tasarımlı mailler için). Kapalıysa düz metin gönderilir, satır sonları otomatik korunur.')
                            ->live()
                            ->default(false)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label(fn ($get) => $get('is_html') ? 'Mail İçeriği (HTML Kodu)' : 'Mail İçeriği')
                            ->rows(18)
                            ->required()
                            ->columnSpanFull()
                            ->extraInputAttributes(fn ($get) => $get('is_html') ? ['style' => 'font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; line-height: 1.55;'] : [])
                            ->helperText('Kullanılabilir değişkenler: {site_name}, {site_phone}, {site_email}, {order_number}, {sender_name}, {recipient_name}, {total}, {delivery_date}, {delivery_slot}, {tracking_url}'),
                        \Filament\Forms\Components\Placeholder::make('html_preview')
                            ->label('Önizleme')
                            ->columnSpanFull()
                            ->visible(fn ($get) => (bool) $get('is_html'))
                            ->content(fn ($get) => new \Illuminate\Support\HtmlString(
                                '<div style="border: 1px solid rgb(228 228 231); border-radius: .6rem; padding: 1rem; background: #fff; max-height: 420px; overflow: auto;">'
                                . ($get('body') ?: '<em style="color:#999">İçerik girildikçe önizleme burada görünür.</em>')
                                . '</div>'
                            )),
                        Toggle::make('is_active')
                            ->label('Aktif (Pasifse bu mail gönderilmez)')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Şablon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Konu')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('recipient_type')
                    ->label('Alıcı')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'admin' ? 'Site Sahibi' : 'Müşteri')
                    ->color(fn (string $state): string => $state === 'admin' ? 'warning' : 'info'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Son Güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailTemplates::route('/'),
            'edit' => EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
