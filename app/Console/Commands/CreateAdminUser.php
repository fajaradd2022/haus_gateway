<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin
                            {name? : The name of the admin user}
                            {email? : The email of the admin user}
                            {password? : The password for the admin user}';

    protected $description = 'Create a new admin user for the application';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('What is the admin name?');
        $email = $this->argument('email') ?? $this->ask('What is the admin email?');
        $password = $this->argument('password') ?? $this->secret('What is the admin password?');

        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->table(['Field', 'Value'], [
            ['Name', $name],
            ['Email', $email],
            ['Role', 'admin'],
        ]);

        return self::SUCCESS;
    }
}
