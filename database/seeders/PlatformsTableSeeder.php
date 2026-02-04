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

        \DB::table('platforms')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'codeforces',
                'display_name' => 'Codeforces',
                'base_url' => 'https://codeforces.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'leetcode',
                'display_name' => 'LeetCode',
                'base_url' => 'https://leetcode.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'atcoder',
                'display_name' => 'AtCoder',
                'base_url' => 'https://atcoder.jp',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => '2026-01-26 15:58:18',
                'updated_at' => '2026-01-26 15:58:18',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'codechef',
                'display_name' => 'CodeChef',
                'base_url' => 'https://www.codechef.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => '2026-01-26 11:18:14',
                'updated_at' => '2026-01-26 11:18:14',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'spoj',
                'display_name' => 'SPOJ',
                'base_url' => 'https://www.spoj.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'hackerrank',
                'display_name' => 'HackerRank',
                'base_url' => 'https://www.hackerrank.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'hackerearth',
                'display_name' => 'HackerEarth',
                'base_url' => 'https://www.hackerearth.com',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'uva',
                'display_name' => 'UVa Online Judge',
                'base_url' => 'https://uhunt.onlinejudge.org',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'timus',
                'display_name' => 'Timus Online Judge',
                'base_url' => 'http://acm.timus.ru',
                'image' => NULL,
                'status' => 'Active',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));


    }
}
