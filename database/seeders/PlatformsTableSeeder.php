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
                'profile_url' => 'https://codeforces.com/profile/',
                'image' => 'uploads/platforms/codeforces17703182706984e9becfd6a.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'leetcode',
                'display_name' => 'LeetCode',
                'base_url' => 'https://leetcode.com',
                'profile_url' => 'https://leetcode.com/u/',
                'image' => 'uploads/platforms/leetcode17703190156984eca7e2993.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'atcoder',
                'display_name' => 'AtCoder',
                'base_url' => 'https://atcoder.jp',
                'profile_url' => 'https://atcoder.jp/users/',
                'image' => 'uploads/platforms/atcoder17703180136984e8bd3090f.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'codechef',
                'display_name' => 'CodeChef',
                'base_url' => 'https://www.codechef.com',
                'profile_url' => 'https://www.codechef.com/users/',
                'image' => 'uploads/platforms/codechef17703181106984e91e544ec.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'spoj',
                'display_name' => 'SPOJ',
                'base_url' => 'https://www.spoj.com',
                'profile_url' => 'https://www.spoj.com/users/',
                'image' => 'uploads/platforms/spoj17703179086984e854cf5f9.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'hackerrank',
                'display_name' => 'HackerRank',
                'base_url' => 'https://www.hackerrank.com',
                'profile_url' => 'https://www.hackerrank.com/profile/',
                'image' => 'uploads/platforms/hackerrank17703185546984eadab4ccd.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'hackerearth',
                'display_name' => 'HackerEarth',
                'base_url' => 'https://www.hackerearth.com',
                'profile_url' => 'https://www.hackerearth.com/@',
                'image' => 'uploads/platforms/hackerearth17703184586984ea7ab2e0f.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'uva',
                'display_name' => 'UVa Online Judge',
                'base_url' => 'https://uhunt.onlinejudge.org',
                'profile_url' => 'https://uhunt.onlinejudge.org/id/',
                'image' => 'uploads/platforms/uva17703192456984ed8dcaf83.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'timus',
                'display_name' => 'Timus Online Judge',
                'base_url' => 'http://acm.timus.ru',
                'profile_url' => 'https://acm.timus.ru/author.aspx?id=',
                'image' => 'uploads/platforms/timus17703191386984ed229156a.png',
                'status' => 'Active',
                'last_contest_sync_at' => NULL,
                'last_problem_sync_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));


    }
}
