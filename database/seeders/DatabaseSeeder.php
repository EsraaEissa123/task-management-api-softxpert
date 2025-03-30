<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Manager',
            'email' => 'manager@softxpert.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@softxpert.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
