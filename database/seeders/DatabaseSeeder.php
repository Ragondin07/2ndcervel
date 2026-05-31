<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.test')],
            [
                'name' => env('ADMIN_NAME', 'Administrateur'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMeNow!')),
                'role' => 'admin',
            ],
        );

        if (filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call(MvpDemoSeeder::class);
        }
    }
}
