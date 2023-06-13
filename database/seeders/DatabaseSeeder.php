<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@argon.com',
            'password' => bcrypt('secret'),
            'role' => 'superadmin',
            'last_login' => Carbon::now(),
        ]);

        DB::table('landing_pages')->insert([
            'judul' => 'Metode Topsis',
            'deskripsi' => 'Ini deskripsi'
        ]);
    }
}
