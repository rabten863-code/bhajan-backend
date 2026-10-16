<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // getenv() reads the real server variables even when Laravel's config is cached
        $name   = getenv('ADMIN_NAME') ?: env('ADMIN_NAME');
        $number = getenv('ADMIN_NUMBER') ?: env('ADMIN_NUMBER');

        if (! $name || ! $number || ! ctype_digit((string) $number) || strlen($number) < 8) {
            $this->command->error('Set ADMIN_NAME and ADMIN_NUMBER (digits only, at least 8).');
            return;
        }

        User::updateOrCreate(
            ['name' => $name, 'role' => 'admin'],
            ['password' => $number]
        );

        $this->command->info("Admin '$name' is ready.");
    }
}