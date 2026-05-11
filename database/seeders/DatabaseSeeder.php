<?php

namespace Database\Seeders;

use App\Models\ActivitySession;
use App\Models\Attraction;
use App\Models\GuestAllocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@wangi.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Marco',
            'email' => 'marco@wangi.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Operator',
            'email' => 'operator@wangi.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        User::factory()->create([
            'name' => 'Operator Dua',
            'email' => 'operator2@wangi.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        User::factory()->create([
            'name' => 'Operator Tiga',
            'email' => 'operator3@wangi.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $operator = User::where('email', 'operator@wangi.com')->first();

        $attractions = Attraction::factory(5)->create();

        $sessions = ActivitySession::factory(5)->recycle($attractions)->create();

        GuestAllocation::factory(5)->recycle($sessions)->create([
            'user_id' => $operator->id,
        ]);

        $this->call(RolePermissionSeeder::class);
    }
}
