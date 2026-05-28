<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih — Mensekak Carwash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-lg p-8 text-center">
        <div class="text-6xl mb-4">
            @if ($score == 5)
                🤩
            @elseif($score >= 4)
                😊
            @elseif($score >= 3)
                🙂
            @else
                🙏
            @endif
        </div>
        <h1 class="text-xl font-bold text-gray-800">Rating Diterima!</h1>
        <div class="flex justify-center gap-1 my-3">
            @for ($i = 1; $i <= 5; $i++)
                <span class="text-2xl {{ $i <= $score ? 'text-yellow-400' : 'text-gray-200' }}">★</span>
            @endfor
        </div>
        <p class="text-gray-500 text-sm">Terima kasih atas penilaiannya.<br>Sampai jumpa lagi!</p>
        <p class="mt-6 text-xs text-gray-400">Mensekak Carwash &bull; Pontianak</p>
    </div>
</body>

</html>
