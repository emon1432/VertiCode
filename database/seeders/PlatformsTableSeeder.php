<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('platforms')->delete();

        \DB::table('platforms')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'codeforces',
                'display_name' => 'Codeforces',
                'base_url' => 'https://codeforces.com',
                'is_active' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'leetcode',
                'display_name' => 'LeetCode',
                'base_url' => 'https://leetcode.com',
                'is_active' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'atcoder',
                'display_name' => 'AtCoder',
                'base_url' => 'https://atcoder.jp',
                'is_active' => 1,
                'created_at' => '2026-01-26 15:58:18',
                'updated_at' => '2026-01-26 15:58:18',
            ),
            3 =>
            array(
                'id' => 4,
                'name' => 'codechef',
                'display_name' => 'CodeChef',
                'base_url' => 'https://www.codechef.com',
                'is_active' => 1,
                'created_at' => '2026-01-26 11:18:14',
                'updated_at' => '2026-01-26 11:18:14',
            ),
            4 =>
            array(
                'id' => 5,
                'name' => 'spoj',
                'display_name' => 'SPOJ',
                'base_url' => 'https://www.spoj.com',
                'is_active' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 =>
            array(
                'id' => 6,
                'name' => 'hackerrank',
                'display_name' => 'HackerRank',
                'base_url' => 'https://www.hackerrank.com',
                'is_active' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
    }
}
