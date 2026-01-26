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
                'raw' => NULL,
                'profile_url' => 'https://codeforces.com/profile/emon_mon',
                'is_active' => 1,
                'last_synced_at' => '2026-01-23 07:31:15',
                'created_at' => '2026-01-23 07:31:06',
                'updated_at' => '2026-01-23 07:31:15',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 2,
                'handle' => 'emon1432',
                'rating' => NULL,
                'total_solved' => 99,
                'raw' => '{"username":"emon1432","profile":{"ranking":1428048},"submitStatsGlobal":{"acSubmissionNum":[{"difficulty":"All","count":99},{"difficulty":"Easy","count":81},{"difficulty":"Medium","count":17},{"difficulty":"Hard","count":1}]}}',
                'profile_url' => 'https://leetcode.com/u/emon1432/',
                'is_active' => 1,
                'last_synced_at' => '2026-01-26 07:40:23',
                'created_at' => '2026-01-26 07:40:04',
                'updated_at' => '2026-01-26 07:40:23',
            ),
        ));
        
        
    }
}