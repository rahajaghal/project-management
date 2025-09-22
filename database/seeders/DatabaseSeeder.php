<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
         \App\Models\User::factory()->create([
             'name' => 'admin',
             'email' => 'admin@gmail.com',
             'password'=>'admin',
             'role_id'=>1,
             'approved'=>1,
             'phone'=>"0945964173"
         ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'front',
        //     'email' => 'front@gmail.com',
        //     'password'=>'12345678',
        //     'role_id'=>10,
        //     'approved'=>1,
        //     'phone'=>"0945964173",
        // ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'back',
        //     'email' => 'back@gmail.com',
        //     'password'=>'12345678',
        //     'role_id'=>11,
        //     'approved'=>1,
        //     'phone'=>"0945964173"
        // ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'tech',
        //     'email' => 'tech@gmail.com',
        //     'password'=>'12345678',
        //     'role_id'=>7,
        //     'approved'=>1,
        //     'phone'=>"0945964173"
        // ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Pmanager',
        //     'email' => 'Pmanager@gmail.com',
        //     'password'=>'12345678',
        //     'role_id'=>9,
        //     'approved'=>1,
        //     'phone'=>"0945964173"
        // ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Cmanager',
        //     'email' => 'Cmanager@gmail.com',
        //     'password'=>'12345678',
        //     'role_id'=>8,
        //     'approved'=>1,
        //     'phone'=>"0945964173"
        // ]);


    }
}
