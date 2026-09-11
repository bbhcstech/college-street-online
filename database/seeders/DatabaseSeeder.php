<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin ---
        User::create([
            'name' => 'Site Admin',
            'email' => 'admin@collegestreetonline.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }
}
