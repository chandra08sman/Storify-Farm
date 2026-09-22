<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Storify', 'email' => 'admin@storifyfarm.test', 'role' => 'admin'],
            ['name' => 'Supervisor', 'email' => 'supervisor@storifyfarm.test', 'role' => 'supervisor'],
            ['name' => 'Petugas Gudang', 'email' => 'petugas@storifyfarm.test', 'role' => 'petugas'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'password' => Hash::make('password'),
                    'theme' => 'light',
                    'accent' => 'green',
                ]
            );

            if ($user->role === 'admin' && !$user->owner_id) {
                $user->update(['owner_id' => $user->id]);
            }
        }

        $ownerId = User::where('role', 'admin')->orderBy('id')->value('id');
        if ($ownerId) {
            User::where('role', '!=', 'admin')
                ->whereNull('owner_id')
                ->update(['owner_id' => $ownerId]);
        }
    }
}
