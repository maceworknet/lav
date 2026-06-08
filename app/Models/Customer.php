<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'is_guest',
        'google_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'is_guest' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Helper attribute for full name
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Send password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('customer.password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        $siteName = \App\Models\Setting::where('key', 'site_name')->value('value') ?? 'Lav Çiçekçilik';

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Merhaba,\n\nHesabınızın şifresini sıfırlamak için aşağıdaki bağlantıyı kullanabilirsiniz:\n\n{$url}\n\nEğer bu talebi siz yapmadıysanız bu e-postayı dikkate almayınız.\n\nSaygılarımızla,\n{$siteName}",
                function ($message) use ($siteName) {
                    $message->to($this->email)
                        ->subject('Şifre Sıfırlama Talebi')
                        ->from(config('mail.from.address', 'hello@example.com'), $siteName);
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send password reset email to {$this->email}: " . $e->getMessage());
        }
    }
}
