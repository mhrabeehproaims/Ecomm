<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    public function run(): void
    {
        // Provision new accounts explicitly. Never reset an existing operator's password.
        $email = env('VANTHARA_BOOTSTRAP_ADMIN_EMAIL');
        $password = env('VANTHARA_BOOTSTRAP_ADMIN_PASSWORD');
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 16) {
            throw new \RuntimeException('Admin provisioning requires an explicit valid email and a password of at least 16 characters. No account was changed.');
        }
        if (User::where('email', $email)->exists()) {
            return;
        }
        User::create(['name' => 'Admin', 'email' => $email, 'password' => Hash::make($password)]);
    }
}
