<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // UPDATE existing records — sesuai daftar harga final
        // -------------------------------------------------------
        $updates = [
            1  => ['nama_layanan' => 'Cuci Mobil Besar',                       'harga' => '65000',  'bagi_karyawan' => '25000'],
            2  => ['nama_layanan' => 'Cuci Mobil Kecil',                       'harga' => '50000',  'bagi_karyawan' => '19000'],
            3  => ['nama_layanan' => 'Cuci Mobil Kecil - Kotor',               'harga' => '60000',  'bagi_karyawan' => '23000'],
            4  => ['nama_layanan' => 'Cuci Mobil Besar - Kotor',               'harga' => '80000',  'bagi_karyawan' => '30000'],
            5  => ['nama_layanan' => 'Cuci Motor Kecil',                       'harga' => '15000',  'bagi_karyawan' => '6000'],
            6  => ['nama_layanan' => 'Cuci Motor Kecil - Kotor',               'harga' => '20000',  'bagi_karyawan' => '8000'],
            7  => ['nama_layanan' => 'Cuci Dam Truck',                         'harga' => '110000', 'bagi_karyawan' => '41000'],
            11 => ['nama_layanan' => 'Cuci Pick Up - Kotor',                   'harga' => '80000',  'bagi_karyawan' => '30000'],
            15 => ['nama_layanan' => 'Cuci Ban & Velg Mobil',                  'harga' => '20000',  'bagi_karyawan' => '8000'],
            16 => ['nama_layanan' => 'Cuci Body Aja',                          'harga' => '40000',  'bagi_karyawan' => '16000'],
            18 => ['nama_layanan' => 'Semprot Motor',                          'harga' => '5000',   'bagi_karyawan' => '2000'],
            22 => ['nama_layanan' => 'Cuci Motor Kecil + Kit Full Body',       'harga' => '20000',  'bagi_karyawan' => '8000'],
            24 => ['nama_layanan' => 'Cuci Truck Besar / Tronton',             'harga' => '130000', 'bagi_karyawan' => '48000'],
            25 => ['nama_layanan' => 'Cuci Motor Besar',                       'harga' => '20000',  'bagi_karyawan' => '8000'],
            26 => ['nama_layanan' => 'Cuci Dalam Aja (Vacuum)',                'harga' => '25000',  'bagi_karyawan' => '10000'],
            27 => ['nama_layanan' => 'Cuci Bus',                               'harga' => '150000', 'bagi_karyawan' => '55500'],
            28 => ['nama_layanan' => 'Cuci Bus Besar',                         'harga' => '160000', 'bagi_karyawan' => '59000'],
        ];

        foreach ($updates as $id => $data) {
            DB::table('layanan')->where('id', $id)->update(array_merge($data, ['status' => 'aktif']));
        }

        // -------------------------------------------------------
        // NONAKTIFKAN records lama yang tidak sesuai daftar harga
        // -------------------------------------------------------
        $inactiveIds = [8, 9, 10, 12, 13, 14, 17, 19, 20, 21, 23];
        DB::table('layanan')->whereIn('id', $inactiveIds)->update(['status' => 'tidak_aktif']);

        // -------------------------------------------------------
        // INSERT layanan baru yang belum ada
        // -------------------------------------------------------
        $now = now();
        DB::table('layanan')->insert([
            // Motor Kecil
            ['nama_layanan' => 'Cuci Motor Kecil + Kit Full Body - Kotor', 'harga' => '25000', 'bagi_karyawan' => '10000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            // Motor Besar
            ['nama_layanan' => 'Cuci Motor Besar - Kotor',                 'harga' => '25000', 'bagi_karyawan' => '10000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Motor Besar + Kit Full Body',         'harga' => '25000', 'bagi_karyawan' => '10000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Motor Besar + Kit Full Body - Kotor', 'harga' => '30000', 'bagi_karyawan' => '12000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            // Pick Up & Minibus
            ['nama_layanan' => 'Cuci Pick Up',                             'harga' => '65000', 'bagi_karyawan' => '24000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Minibus / Elf',                       'harga' => '80000', 'bagi_karyawan' => '30000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Minibus / Elf - Kotor',               'harga' => '95000', 'bagi_karyawan' => '35000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            // Truck & Bus
            ['nama_layanan' => 'Cuci Dam Truck - Kotor',                   'harga' => '130000', 'bagi_karyawan' => '48000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Truck Besar - Kotor',                 'harga' => '150000', 'bagi_karyawan' => '55500', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Truck Tanki',                         'harga' => '140000', 'bagi_karyawan' => '52000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['nama_layanan' => 'Cuci Truck Tanki - Kotor',                 'harga' => '160000', 'bagi_karyawan' => '59000', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
