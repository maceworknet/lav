<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'recipient_type',
        'is_html',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_html' => 'boolean',
        ];
    }

    /**
     * Şablon içindeki {degisken} yer tutucularını verilen verilerle doldurur.
     */
    public function render(array $data): array
    {
        $replacements = [];
        foreach ($data as $key => $value) {
            $replacements['{' . $key . '}'] = (string) $value;
        }

        return [
            'subject' => strtr($this->subject, $replacements),
            'body' => strtr($this->body, $replacements),
        ];
    }
}
