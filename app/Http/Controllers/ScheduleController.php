<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class ScheduleController extends Controller
{	
     public function menu(){
        $
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=54_YaaUAnKLgxNHLXSsk1QaQZ3l0uopq4qMULTbnMlS6EDZTCEAVelDlTN-hh1QA4qe0FrPPhZYM5wBT-Q7v78kfxPInFb8Gchu1ll6_moHfWRuxufPhRv9Eo6KUvV__By54st1oEkrAEf-dleJNXDgAAAUEK';
	$params = "{\"button\":[{\"name\":\"设置放学时间\",\"type\":\"view\",\"url\":\"http://47.104.250.59/schedule/info\"}]}";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);

     }

     public function info(){
     	return '多云转晴';
     }
     public function push(){
	$accessToken = "55_UqBJ4i2kyDCwRjWMReil9LHMOvgRqnSWQdbuoGdNFIzO2q694U7iEWDIGVleYCqlGSbQWfWSH7ef_6V2KXDYSCtIXkJVBY6d1aTK-KOCM1XjgPiZDEbGonxporNUjabTDQ48eth2aRTnufITILHiAHAXAW";
	$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$accessToken;
        $data = [
	    'weather'=>['value'=>'晴','color'=>'#269d2a'],
            'tem' => ['value'=>'14','color'=>'#269d2a'],
            'wind' => ['value'=>2,'color'=>'#269d2a'],
            'sundown' => ['value'=>'18:10'],
            'sportstime' => ['value'=>'16:00-17:00'],
            'sports' => ['value'=>'跳绳、篮球'],
        ];
	$params = [
	     //'touser'=>'oNyZHtzgptAz3068fT3a0y6K-jfk',
	     //'touser'=>'oNyZHt37jE8lvpU3m_ghyjakDd08',
	     'touser'=>'oNyZHt9R-XSRACrV779VNgZo9ToA',
	     'template_id'=>'sSmFtLC0CNLU54ABFSSnngxvPeTKzay-mBavt0nRaXM',
	     'url'=>'https://mp.weixin.qq.com/s/Q3y5rZpUuu1Z3RI3m_1Imw',
	     //'topcolor'=>'#00ff1f',
	     'topcolor'=>'#ccff00',
	     //'topcolor'=>'#ff3b00',
	     'data'=>$data,
	];
	$params = json_encode($params);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	dd($res);
     }
}
