<x-filament-panels::page>

    {{-- Filter Bulan & Tahun --}}
    <div class="flex gap-4 mb-6">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
            <select wire:model.live="bulan"
                class="mt-1 block w-36 rounded-lg border-gray-300 text-sm shadow-sm focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $n => $nama)
                    <option value="{{ $n }}" @selected($bulan == $n)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
            <select wire:model.live="tahun"
                class="mt-1 block w-28 rounded-lg border-gray-300 text-sm shadow-sm focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @foreach (range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @php $d = $this->getData(); @endphp

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Pendapatan</p>
            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($d['totalPendapatan'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">{{ $d['jumlahTransaksi'] }} transaksi</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-orange-400">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Bagi Karyawan</p>
            <p class="text-lg font-bold text-orange-500">Rp {{ number_format($d['totalBagiKaryawan'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-red-400">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Pengeluaran</p>
            <p class="text-lg font-bold text-red-500">Rp {{ number_format($d['totalPengeluaran'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Laba Bersih Owner</p>
            <p class="text-lg font-bold {{ $d['labaOwner'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Rp {{ number_format($d['labaOwner'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Tabel Rincian --}}
    <style>
        .dark .laporan-table { background-color: rgb(31 41 55); }
        .dark .laporan-table thead { background-color: rgb(55 65 81); }
        .dark .laporan-table thead th { color: rgb(209 213 219); }
        .dark .laporan-table tbody tr { border-color: rgb(55 65 81); }
        .dark .laporan-table tbody tr td { color: rgb(209 213 219); }
        .dark .laporan-table .row-subtotal { background-color: rgb(55 65 81); }
        .dark .laporan-table .row-subtotal td { color: rgb(243 244 246); }
        .dark .laporan-table .row-total { background-color: rgb(20 83 45 / 0.3); }
        .dark .laporan-table .row-total td.label { color: rgb(243 244 246); }
    </style>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <table class="laporan-table w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300 font-semibold">Komponen</th>
                    <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300 font-semibold">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">Total Pendapatan Kotor</td>
                    <td class="px-4 py-3 text-right font-medium text-blue-600 dark:text-blue-400">+ Rp
                        {{ number_format($d['totalPendapatan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">Bagi Hasil Karyawan</td>
                    <td class="px-4 py-3 text-right text-orange-500 dark:text-orange-400">− Rp
                        {{ number_format($d['totalBagiKaryawan'], 0, ',', '.') }}</td>
                </tr>
                <tr class="row-subtotal bg-gray-50 font-semibold">
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-100">Bagian Owner (sebelum pengeluaran)</td>
                    <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-100">Rp
                        {{ number_format($d['bagianOwner'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">Pengeluaran Operasional</td>
                    <td class="px-4 py-3 text-right text-red-500 dark:text-red-400">− Rp
                        {{ number_format($d['totalPengeluaran'], 0, ',', '.') }}</td>
                </tr>
                <tr class="row-total bg-green-50 font-bold text-base">
                    <td class="label px-4 py-3 text-gray-800 dark:text-gray-100">Laba Bersih Owner</td>
                    <td class="px-4 py-3 text-right {{ $d['labaOwner'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        Rp {{ number_format($d['labaOwner'], 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</x-filament-panels::page>
