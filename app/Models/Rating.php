<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = ['transaksi_id', 'score', 'komentar'];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }
}
