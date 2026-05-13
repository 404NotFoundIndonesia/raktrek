<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@raktrek.test'],
            [
                'name'     => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role'     => 'staff',
                'phone'    => '081234567890',
                'address'  => 'Jl. Perpustakaan No. 1, Banjarmasin',
            ]
        );
    }
}
