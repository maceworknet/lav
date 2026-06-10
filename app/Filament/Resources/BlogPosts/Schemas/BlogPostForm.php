<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Blog Yazı Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Yazı Başlığı')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label('Kalıcı Bağlantı (Slug)')
                                    ->required()
                                    ->unique('blog_posts', 'slug', ignoreRecord: true),
                                Select::make('blog_category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->placeholder('Kategori Seçin')
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->default(true),
                            ]),
                        Textarea::make('summary')
                            ->label('Özet (Giriş Metni)')
                            ->rows(2)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Yazı İçeriği')
                            ->columnSpanFull()
                            ->required(),
                        \App\Forms\Components\MediaPicker::make('image')
                            ->label('Kapak Görseli')
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO Ayarları')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('SEO Başlığı')
                            ->maxLength(60),
                        Textarea::make('meta_description')
                            ->label('SEO Açıklaması')
                            ->maxLength(160)
                            ->rows(3),
                    ]),
            ]);
    }
}
