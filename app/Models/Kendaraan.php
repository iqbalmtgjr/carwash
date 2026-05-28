<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'kendaraan';

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
