<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'layanan';

    public function getFormattedOptionAttribute()
    {
        return "{$this->nama_layanan} - Rp. " . number_format($this->harga, 0, ',', '.');
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class);
    }
}
