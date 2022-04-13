<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PushWeixinMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=54_YaaUAnKLgxNHLXSsk1QaQZ3l0uopq4qMULTbnMlS6EDZTCEAVelDlTN-hh1QA4qe0FrPPhZYM5wBT-Q7v78kfxPInFb8Gchu1ll6_moHfWRuxufPhRv9Eo6KUvV__By54st1oEkrAEf-dleJNXDgAAAUEK';
	$params = ‘{"button":[{"name":"设置放学时间","type":"view","url":"http://47.104.250.59/schedule/info"}]}’;
	$c = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_URL, $url);

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
