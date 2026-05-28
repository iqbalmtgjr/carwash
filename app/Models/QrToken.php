<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrToken extends Model
{
    protected $fillable = ['token', 'expired_at'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expired_at->isPast();
    }

    public static function current(): ?static
    {
        return static::where('expired_at', '>', now())->latest()->first();
    }
}
