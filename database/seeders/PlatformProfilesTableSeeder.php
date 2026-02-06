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
                'platform_id' => 3,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://atcoder.jp/users/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 4,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://www.codechef.com/users/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 2,
                'platform_id' => 1,
                'handle' => 'emon_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://codeforces.com/profile/emon_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 2,
                'platform_id' => 7,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://www.hackerearth.com/@e_mon/',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 2,
                'platform_id' => 6,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://www.hackerrank.com/profile/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            5 => 
            array (
                'id' => 6,
                'user_id' => 2,
                'platform_id' => 2,
                'handle' => 'emon1432',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://leetcode.com/u/emon1432/',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            6 => 
            array (
                'id' => 7,
                'user_id' => 2,
                'platform_id' => 5,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://www.spoj.com/users/e_mon/',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            7 => 
            array (
                'id' => 8,
                'user_id' => 2,
                'platform_id' => 9,
                'handle' => '405359',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'http://acm.timus.ru/author.aspx?id=405359',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
            8 => 
            array (
                'id' => 9,
                'user_id' => 2,
                'platform_id' => 8,
                'handle' => 'emon_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'raw' => NULL,
                'profile_url' => 'https://uhunt.onlinejudge.org/id/emon_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 08:13:20',
                'updated_at' => '2026-02-06 08:13:20',
            ),
        ));
        
        
    }
}