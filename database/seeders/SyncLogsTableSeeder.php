<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SyncLogsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sync_logs')->delete();
        
        \DB::table('sync_logs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'platform_profile_id' => 1,
                'status' => 'success',
                'http_code' => NULL,
                'error_message' => NULL,
                'duration_ms' => 1345,
                'created_at' => '2026-01-23 13:31:15',
            ),
        ));
        
        
    }
}