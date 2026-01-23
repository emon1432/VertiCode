<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformProfilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('platform_profiles')->delete();
        
        \DB::table('platform_profiles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 2,
                'platform_id' => 1,
                'handle' => 'emon_mon',
                'rating' => 986,
                'total_solved' => 308,
                'profile_url' => 'https://codeforces.com/profile/emon_mon',
                'is_active' => 1,
                'last_synced_at' => '2026-01-23 07:31:15',
                'created_at' => '2026-01-23 07:31:06',
                'updated_at' => '2026-01-23 07:31:15',
            ),
        ));
        
        
    }
}