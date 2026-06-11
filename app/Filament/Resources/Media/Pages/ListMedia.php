<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Resources\Pages\Page;

class ListMedia extends Page
{
    protected static string $resource = MediaResource::class;

    protected string $view = 'filament.pages.media-library';

    protected static ?string $title = 'Medya Kütüphanesi';
}
