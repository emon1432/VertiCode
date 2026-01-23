<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Emon Admin',
                'username' => 'emonadmin',
                'role' => 'admin',
                'email' => 'admin@verticasoft.com',
                'phone' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Emon User',
                'username' => 'emonuser',
                'role' => 'user',
                'email' => 'user@verticasoft.com',
                'phone' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}