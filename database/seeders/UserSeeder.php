<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sidoharjo.desa.id'],
            [
                'name'      => 'Administrator',
                'password'  => Hash::make('sidoharjo2026'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@sidoharjo.desa.id'],
            [
                'name'      => 'Petugas Pelayanan',
                'password'  => Hash::make('petugas2026'),
                'role'      => 'petugas',
                'is_active' => true,
            ]
        );
    }
}