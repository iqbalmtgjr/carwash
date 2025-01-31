<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'transaksi';

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function kendaraan()
    {
        return $this->hasOne(Kendaraan::class);
    }

    public function bagipendapatan()
    {
        return $this->hasMany(Bagipendapatan::class);
    }

    public function transaksiuser()
    {
        return $this->hasMany(Transaksiuser::class);
    }
}
