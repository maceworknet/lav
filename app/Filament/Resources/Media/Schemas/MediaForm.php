<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('Medya Dosyası')
                    ->directory('media')
                    ->image()
                    ->required(),
                TextInput::make('name')
                    ->label('Dosya Adı (İsteğe Bağlı)')
                    ->placeholder('Boş bırakılırsa orijinal dosya adı kullanılır')
                    ->nullable(),
            ]);
    }
}
