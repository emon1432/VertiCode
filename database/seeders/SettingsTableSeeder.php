<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'icon' => 'briefcase',
                'key' => 'business_settings',
                'value' => '{"company_name":"VertiCode","email":"info@verticode.com","phone":"01638849305","address":"Dhaka, Bangladesh"}',
                'created_at' => '2025-04-19 05:17:43',
                'updated_at' => '2026-01-04 18:46:18',
            ),
            1 => 
            array (
                'id' => 2,
                'icon' => 'settings',
                'key' => 'system_settings',
                'value' => '{"app_name":"VertiCode","app_url":"https:\\/\\/verticode.verticasoft.tech","app_locale":"en","app_timezone":"Asia\\/Dhaka","date_format":"d-m-Y","time_format":"h:i A","decimal_separator":".","thousand_separator":",","decimal_precision":"2","footer_text":"\\u00a9 2025 ProdInsight. All rights reserved.","copyright":"Copyright \\u00a9 2025 ProdInsight"}',
                'created_at' => '2025-04-19 05:17:43',
                'updated_at' => '2026-01-22 12:27:25',
            ),
            2 => 
            array (
                'id' => 3,
                'icon' => 'mail',
                'key' => 'mail_settings',
                'value' => '{"mail_driver":"smtp","mail_host":"sandbox.smtp.mailtrap.io","mail_port":"2525","mail_username":"fa39ddca0a2a4f","mail_password":"89a49d9d605777","mail_encryption":"tls","mail_from_address":"info@prodinsight.com","mail_from_name":"ProdInsight"}',
                'created_at' => '2025-04-19 05:17:43',
                'updated_at' => '2025-12-08 10:59:49',
            ),
        ));
        
        
    }
}