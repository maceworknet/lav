<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Kategori Adı')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label('Kalıcı Bağlantı (Slug)')
                                    ->required()
                                    ->unique('categories', 'slug', ignoreRecord: true),
                                Select::make('parent_id')
                                    ->label('Üst Kategori')
                                    ->relationship('parent', 'name')
                                    ->placeholder('Üst Kategori Yok')
                                    ->nullable(),
                                TextInput::make('order')
                                    ->label('Sıralama')
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->rows(3)
                            ->columnSpanFull(),
                        \App\Forms\Components\MediaPicker::make('image')
                            ->label('Kategori Öne Çıkan Görseli')
                            ->columnSpanFull(),
                    ]),

                Section::make('Durum ve Görünüm')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->inline(false)
                                    ->default(true),
                                Toggle::make('show_on_header')
                                    ->label('Header\'da Göster')
                                    ->inline(false)
                                    ->default(false),
                                Toggle::make('show_on_homepage')
                                    ->label('Ana Sayfada Göster')
                                    ->inline(false)
                                    ->default(false),
                            ]),
                    ]),

                Section::make('SEO Ayarları')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('SEO Başlığı')
                            ->maxLength(60),
                        Textarea::make('meta_description')
                            ->label('Kategori SEO Açıklaması')
                            ->maxLength(160)
                            ->rows(3),
                        RichEditor::make('seo_description')
                            ->label('Kategori Detaylı SEO Açıklaması (Sayfa Altı)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
