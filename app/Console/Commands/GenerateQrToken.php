<?php

namespace App\Console\Commands;

use App\Models\QrToken;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateQrToken extends Command
{
    protected $signature = 'qr:generate';

    protected $description = 'Generate QR token baru untuk absensi (rotate tiap 1 menit)';

    public function handle(): void
    {
        // Hapus token yang sudah expired
        QrToken::where('expired_at', '<', now())->delete();

        // Buat token baru, valid 65 detik (sedikit overlap untuk antisipasi delay)
        QrToken::create([
            'token'      => Str::random(48),
            'expired_at' => now()->addSeconds(65),
        ]);

        $this->info('QR token berhasil di-generate: ' . now()->format('H:i:s'));
    }
}
