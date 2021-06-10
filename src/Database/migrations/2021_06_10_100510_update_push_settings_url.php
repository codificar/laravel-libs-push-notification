<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePushSettingsUrl extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{		
		Permission::updateOrCreate(['name' => 'Keys and Push'], [
            'url' => '/admin/libs/push_notification',
            'is_menu' => 1,
            'parent_id' => 2319, 
            'order' => 909, 
            'icon' => 'mdi mdi-key-plus'
            ]
        );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
