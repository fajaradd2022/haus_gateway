<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@helpdesk.local'],
            [
                'name'              => 'Admin',
                'password'          => bcrypt('admin123'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
