<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sipariş Genel Durumu')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('order_number')
                                    ->label('Sipariş Numarası')
                                    ->disabled(),
                                Select::make('status')
                                    ->label('Sipariş Durumu')
                                    ->options([
                                        'pending_payment' => 'Ödeme Bekliyor',
                                        'payment_failed' => 'Ödeme Başarısız',
                                        'paid' => 'Ödendi / Yeni Sipariş',
                                        'preparing' => 'Hazırlanıyor',
                                        'assigned_to_courier' => 'Kuryeye Atandı',
                                        'on_delivery' => 'Dağıtımda',
                                        'delivered' => 'Teslim Edildi',
                                        'cancelled' => 'İptal Edildi',
                                        'refunded' => 'İade Edildi',
                                    ])
                                    ->required(),
                                DatePicker::make('created_at')
                                    ->label('Sipariş Tarihi')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Gönderici ve Alıcı Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Section::make('Gönderici')
                                    ->schema([
                                        TextInput::make('sender_name')
                                            ->label('Gönderici Adı')
                                            ->required(),
                                        TextInput::make('sender_phone')
                                            ->label('Gönderici Telefonu')
                                            ->required(),
                                        TextInput::make('sender_email')
                                            ->label('Gönderici E-postası')
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                Section::make('Alıcı')
                                    ->schema([
                                        TextInput::make('recipient_name')
                                            ->label('Alıcı Adı')
                                            ->required(),
                                        TextInput::make('recipient_phone')
                                            ->label('Alıcı Telefonu')
                                            ->required(),
                                        Textarea::make('recipient_address')
                                            ->label('Teslimat Adresi')
                                            ->rows(2)
                                            ->required(),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('recipient_district')
                                                    ->label('İlçe')
                                                    ->required(),
                                                TextInput::make('recipient_neighborhood')
                                                    ->label('Mahalle')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Teslimat Zamanı ve Kart Notu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('delivery_date')
                                    ->label('Teslimat Tarihi')
                                    ->required(),
                                TextInput::make('delivery_slot')
                                    ->label('Teslimat Saat Aralığı')
                                    ->required(),
                                Textarea::make('card_note')
                                    ->label('Kart Notu İçeriği')
                                    ->rows(3),
                                TextInput::make('card_note_signature')
                                    ->label('Kart Notu İmzası (Gönderen/İsimsiz)')
                                    ->placeholder('İsimsiz'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Sipariş Edilen Çiçekler')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Ürün Adı')
                                    ->disabled()
                                    ->columnSpan(2),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->disabled(),
                                TextInput::make('price')
                                    ->label('Birim Fiyat')
                                    ->disabled()
                                    ->prefix('₺'),
                                TextInput::make('quantity')
                                    ->label('Adet')
                                    ->disabled(),
                                TextInput::make('total')
                                    ->label('Toplam Tutar')
                                    ->disabled()
                                    ->prefix('₺'),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->columnSpanFull(),

                Section::make('Finansal Toplamlar ve Notlar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->label('Ara Toplam')
                                            ->disabled()
                                            ->prefix('₺'),
                                        TextInput::make('delivery_fee')
                                            ->label('Teslimat Ücreti')
                                            ->disabled()
                                            ->prefix('₺'),
                                        TextInput::make('discount_amount')
                                            ->label('İndirim Tutarı')
                                            ->disabled()
                                            ->prefix('₺'),
                                        TextInput::make('total')
                                            ->label('Genel Toplam')
                                            ->disabled()
                                            ->prefix('₺'),
                                    ])
                                    ->columnSpan(1),
                                
                                Textarea::make('admin_note')
                                    ->label('Yönetici Notu (Sadece Admin Görür)')
                                    ->rows(4)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
