<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi — Mensekak Carwash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="60">
</head>

<body class="bg-gray-900 text-white min-h-screen flex flex-col items-center justify-center p-6">

    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-blue-400">Mensekak Carwash</h1>
        <p class="text-gray-400 mt-1">Scan QR Code untuk absensi</p>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-2xl">
        @if ($token)
            {!! QrCode::size(280)->generate(url('/absensi/scan/' . $token->token)) !!}
        @else
            <div class="w-72 h-72 flex items-center justify-center text-gray-500 text-sm">
                QR tidak tersedia
            </div>
        @endif
    </div>

    <div class="mt-6 text-center space-y-2">
        <p class="text-gray-300 text-sm">
            <span class="font-semibold text-white">Cara absensi:</span>
            Connect WiFi <span class="text-blue-400 font-mono">MensekakStaff</span>
            → Scan QR → Pilih nama → Submit
        </p>

        @if ($token)
            <p class="text-xs text-gray-500">
                QR berlaku hingga <span
                    class="text-yellow-400 font-mono">{{ $token->expired_at->format('H:i:s') }}</span>
                &bull; halaman refresh otomatis tiap 60 detik
            </p>
        @endif
    </div>

    <div class="mt-8 text-4xl font-mono text-white" id="clock"></div>

    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${h}:${m}:${s}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>

</html>
