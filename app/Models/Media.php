<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $appends = ['url'];

    protected static function booted()
    {
        static::creating(function ($media) {
            if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
                if (empty($media->name)) {
                    $media->name = basename($media->file_path);
                }
                try {
                    $media->file_size = Storage::disk('public')->size($media->file_path);
                    $media->file_type = Storage::disk('public')->mimeType($media->file_path);
                } catch (\Exception $e) {
                    // Fallback if size/mime retrieval fails
                }
            }
        });
    }

    /**
     * Get the public URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
