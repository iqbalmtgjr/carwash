<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating Layanan — Mensekak Carwash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-lg p-8">

        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🚗✨</div>
            <h1 class="text-xl font-bold text-gray-800">Terima kasih sudah cuci di sini!</h1>
            <p class="text-sm text-gray-500 mt-1">Berikan rating untuk layanan kami</p>
        </div>

        @if ($transaksi->kendaraan)
            <div class="bg-blue-50 rounded-lg p-3 mb-5 text-sm text-blue-700 text-center">
                {{ $transaksi->kendaraan->merk }} — {{ $transaksi->kendaraan->plat }}
            </div>
        @endif

        <form method="POST" action="{{ route('rating.store', $transaksi->id) }}" id="ratingForm">
            @csrf

            <!-- Star Rating -->
            <div class="flex justify-center gap-3 mb-6" id="stars">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" class="star text-4xl transition-transform hover:scale-110 text-gray-300"
                        data-value="{{ $i }}">★</button>
                @endfor
            </div>
            <input type="hidden" name="score" id="scoreInput" required>

            @error('score')
                <p class="text-xs text-red-500 text-center mb-3">{{ $message }}</p>
            @enderror

            <textarea name="komentar" placeholder="Komentar (opsional)..." rows="3"
                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('komentar') }}</textarea>

            <button type="submit" id="submitBtn"
                class="mt-5 w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white font-semibold py-3 rounded-lg transition"
                disabled>
                Kirim Rating
            </button>
        </form>
    </div>

    <p class="mt-4 text-xs text-gray-400">Mensekak Carwash &bull; Pontianak</p>

    <script>
        const stars = document.querySelectorAll('.star');
        const input = document.getElementById('scoreInput');
        const btn = document.getElementById('submitBtn');

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const val = parseInt(star.dataset.value);
                input.value = val;
                btn.disabled = false;
                stars.forEach((s, i) => {
                    s.classList.toggle('text-yellow-400', i < val);
                    s.classList.toggle('text-gray-300', i >= val);
                });
            });
        });
    </script>
</body>

</html>
