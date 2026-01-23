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
                'is_active' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}