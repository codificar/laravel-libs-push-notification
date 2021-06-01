<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddPushIosSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::updateOrCreate(array('key' => 'ios_auth_token_file_name'), array('value' => '', 'page' => 1, 'category' => 6, 'tool_tip' => 'push-notification: Nome do arquivo p8 de autenticação para push notification da apple'));
        Settings::updateOrCreate(array('key' => 'ios_key_id'), array('value' => '', 'page' => 1, 'category' => 6, 'tool_tip' => 'push-notification: Key ID da apple'));
        Settings::updateOrCreate(array('key' => 'ios_team_id'), array('value' => '', 'page' => 1, 'category' => 6, 'tool_tip' => 'push-notification: Team ID da apple'));
        Settings::updateOrCreate(array('key' => 'ios_package_user'), array('value' => '', 'page' => 1, 'category' => 6, 'tool_tip' => 'push-notification: Nome do pacote do app usuario'));
        Settings::updateOrCreate(array('key' => 'ios_package_provider'), array('value' => '', 'page' => 1, 'category' => 6, 'tool_tip' => 'push-notification: Nome do pacote do app prestador'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}