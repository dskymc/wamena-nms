<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => env('NMS_ADMIN_EMAIL', 'admin@nms.local')],
            [
                'name' => 'NMS Administrator',
                'password' => Hash::make(env('NMS_ADMIN_PASSWORD', 'password')),
            ]
        );

        $user->syncRoles(['super_admin']);
    }
}
