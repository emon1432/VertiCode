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
                'raw' => '{"lastName":"Emon","country":"Bangladesh","lastOnlineTimeSeconds":1769425444,"city":"Dhaka","rating":986,"friendOfCount":14,"titlePhoto":"https:\\/\\/userpic.codeforces.org\\/1251151\\/title\\/bb229b5b047bfc07.jpg","handle":"emon_mon","avatar":"https:\\/\\/userpic.codeforces.org\\/1251151\\/avatar\\/831bc82d1bd1a504.jpg","firstName":"Khairul Islam","contribution":0,"organization":"Institute of Science and Technology","rank":"newbie","maxRating":1389,"registrationTimeSeconds":1568830934,"maxRank":"pupil"}',
                'profile_url' => 'https://codeforces.com/profile/emon_mon',
                'is_active' => 1,
                'last_synced_at' => '2026-01-26 11:09:28',
                'created_at' => '2026-01-23 07:31:06',
                'updated_at' => '2026-01-26 11:09:28',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 2,
                'handle' => 'emon1432',
                'rating' => NULL,
                'total_solved' => 99,
                'raw' => '{"username":"emon1432","profile":{"ranking":1429089},"submitStatsGlobal":{"acSubmissionNum":[{"difficulty":"All","count":99},{"difficulty":"Easy","count":81},{"difficulty":"Medium","count":17},{"difficulty":"Hard","count":1}]}}',
                'profile_url' => 'https://leetcode.com/u/emon1432/',
                'is_active' => 1,
                'last_synced_at' => '2026-01-26 11:09:28',
                'created_at' => '2026-01-26 07:40:04',
                'updated_at' => '2026-01-26 11:09:28',
            ),
            2 => 
            array (
                'id' => 5,
                'user_id' => 2,
                'platform_id' => 3,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => '{"rating":null,"total_solved":0}',
                'profile_url' => 'https://atcoder.jp/users/e_mon',
                'is_active' => 1,
                'last_synced_at' => '2026-01-26 11:09:29',
                'created_at' => '2026-01-26 11:01:14',
                'updated_at' => '2026-01-26 11:09:29',
            ),
        ));
        
        
    }
}