<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi — Mensekak Carwash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-lg p-8">

        <div class="text-center mb-6">
            <div class="text-4xl mb-2">👋</div>
            <h1 class="text-xl font-bold text-gray-800">Absensi Masuk</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ now()->format('l, d F Y') }} &bull; {{ now()->format('H:i') }}
            </p>
        </div>

        <form method="POST" action="{{ route('absensi.submit', $tokenStr) }}">
            @csrf

            <label class="block text-sm font-medium text-gray-700 mb-1">
                Pilih nama kamu
            </label>
            <select name="employee_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="" disabled selected>-- Pilih nama --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>

            @error('employee_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <button type="submit"
                class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                Absensi Sekarang
            </button>
        </form>

    </div>

    <p class="mt-4 text-xs text-gray-400">Mensekak Carwash &bull; Pontianak</p>

</body>

</html>
