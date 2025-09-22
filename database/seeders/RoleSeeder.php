<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::create([
            'role' => 'admin',
        ]);
        \App\Models\Role::create([
            'role' => 'client',
        ]);
        \App\Models\Role::create([
            'role' => 'freelancer',
        ]);
        \App\Models\Role::create([
            'role' => 'user',
        ]);
        \App\Models\Role::create([
            'role' => 'human resource',
        ]);
        \App\Models\Role::create([
            'role' => 'marketing manager',
        ]);
        \App\Models\Role::create([
            'role' => 'technical manager',
        ]);
        \App\Models\Role::create([
            'role' => 'contracts manager',
        ]);
        \App\Models\Role::create([
            'role' => 'project manager',
        ]);
        \App\Models\Role::create([
            'role' => 'frontend developer',
        ]);
        \App\Models\Role::create([
            'role' => 'backend developer',
        ]);
        \App\Models\Role::create([
            'role' => 'full stack developer',
        ]);
        \App\Models\Role::create([
            'role' => 'software developer',
        ]);
        \App\Models\Role::create([
            'role' => 'system analyst',
        ]);
        \App\Models\Role::create([
            'role' => 'QA engineer',
        ]);
        \App\Models\Role::create([
            'role' => 'AI developer',
        ]);
        \App\Models\Role::create([
            'role' => 'flutter developer',
        ]);

    }
}
