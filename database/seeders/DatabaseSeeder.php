<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $roles=['user','admin','superadmin'];
        foreach($roles as $role)
        {
            Role::firstOrCreate(['name' => $role]);
        }
        User::firstOrCreate(
            ['email' => 'superadmin@postix.ai'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id' => Role::where('name', 'superadmin')->value('id'),
                'oferta_read' => true,
            ]
        );
    }
}
