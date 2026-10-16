<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $name   = env('ADMIN_NAME');
        $number = env('ADMIN_NUMBER');

        if (! $name || ! $number || ! ctype_digit((string) $number) || strlen($number) < 8) {
            $this->command->error('Set ADMIN_NAME and ADMIN_NUMBER (digits only, at least 8) in .env first.');
            return;
        }

        User::updateOrCreate(
            ['name' => $name, 'role' => 'admin'],
            ['password' => $number]
        );

        $this->command->info("Admin '$name' is ready.");
    }
}