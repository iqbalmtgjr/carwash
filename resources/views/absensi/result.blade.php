<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi — Mensekak Carwash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-lg p-8 text-center">

        @if ($success && $icon === 'hadir')
            <div class="text-6xl mb-4">✅</div>
            <h1 class="text-xl font-bold text-green-600">Absensi Berhasil!</h1>
        @elseif($success && $icon === 'terlambat')
            <div class="text-6xl mb-4">⚠️</div>
            <h1 class="text-xl font-bold text-yellow-600">Absensi Tercatat</h1>
        @elseif($icon === 'already')
            <div class="text-6xl mb-4">ℹ️</div>
            <h1 class="text-xl font-bold text-blue-600">Sudah Absen</h1>
        @elseif($icon === 'expired')
            <div class="text-6xl mb-4">⏰</div>
            <h1 class="text-xl font-bold text-orange-500">QR Kadaluarsa</h1>
        @else
            <div class="text-6xl mb-4">🚫</div>
            <h1 class="text-xl font-bold text-red-600">Tidak Diizinkan</h1>
        @endif

        <p class="mt-3 text-gray-600 text-sm leading-relaxed">{{ $message }}</p>

        @if (isset($user) && $success)
            <div class="mt-4 bg-gray-50 rounded-lg p-3 text-left text-sm text-gray-700">
                <div><span class="font-medium">Nama:</span> {{ $user->name }}</div>
                <div><span class="font-medium">Waktu:</span> {{ now()->format('H:i:s') }}</div>
                <div><span class="font-medium">Status:</span>
                    @if ($icon === 'terlambat')
                        <span class="text-yellow-600 font-semibold">Terlambat</span>
                    @else
                        <span class="text-green-600 font-semibold">Hadir</span>
                    @endif
                </div>
            </div>
        @endif

        <p class="mt-6 text-xs text-gray-400">Mensekak Carwash &bull; Pontianak</p>

    </div>

</body>

</html>
