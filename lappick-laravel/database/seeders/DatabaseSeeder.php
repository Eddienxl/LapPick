<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lappick.id'],
            [
                'name' => 'Admin LapPick',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'fadli@uin-suska.ac.id'],
            [
                'name' => 'Ahmad Fadli Pratama (12350111030)',
                'role' => 'user',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call([
            CriteriaSeeder::class,
            PresetProfileSeeder::class,
            LaptopSeeder::class,
        ]);
    }
}
