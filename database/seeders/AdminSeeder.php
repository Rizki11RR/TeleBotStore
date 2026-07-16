<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@nexoradigital.com'],
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@nexoradigital.com',
                'password' => Hash::make('nexora@2024'),
            ]
        );
    }
}
