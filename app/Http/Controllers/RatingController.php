<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function show(Transaksi $transaksi)
    {
        // Sudah diberi rating sebelumnya
        if ($transaksi->rating) {
            return view('rating.already');
        }

        return view('rating.form', compact('transaksi'));
    }

    public function store(Request $request, Transaksi $transaksi)
    {
        if ($transaksi->rating) {
            return view('rating.already');
        }

        $request->validate([
            'score'    => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);

        Rating::create([
            'transaksi_id' => $transaksi->id,
            'score'        => $request->score,
            'komentar'     => $request->komentar,
        ]);

        return view('rating.thanks', ['score' => $request->score]);
    }
}
