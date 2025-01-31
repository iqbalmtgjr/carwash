<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Muhammad Iqbal',
            'email' => 'iqbalmtgjr@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'no_wa' => '08996979079',
            'alamat' => 'Jalan. Mensiku Jaya, RT/RW:003/001.'
        ]);
    }
}
