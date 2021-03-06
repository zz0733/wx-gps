<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 查看定位记录详情
     * @param $id
     * @return $this
     */
    public function show($id) {
        $visit = \App\Models\Visit::where('id', $id)->where('user_id', Auth::id())->first();
        if(!$visit) {
            return Redirect::back()->with('tips', [
                'status' => false,
                'message' => '定位记录不存在',
            ]);
        }

        $location = file_get_contents('http://api.map.baidu.com/location/ip?ip='.$visit->ip .'&ak=yogxH1g0VzghVO38jG0jF1CEFuNpjyiR&coor=bd09ll');
        $visit->location = json_decode($location);
        if(!isset($visit->location->message)) {
            $visit->service = explode('|', $visit->location->address);
            $visit->service = $visit->service[4];
        }
        if($visit->auth_data) {
            $visit->auth_data = json_decode($visit->auth_data);
            $visit->auth_location = json_decode(file_get_contents('http://api.map.baidu.com/geoconv/v1/?coords='.$visit->auth_data->longitude.','.$visit->auth_data->latitude.'&from=1&to=5&ak=yogxH1g0VzghVO38jG0jF1CEFuNpjyiR'));
        }

        return view('visit')->with('visit', $visit);
    }

    /**
     * 调用地图插件
     * @param $x
     * @param $y
     * @return $this
     */
    public function map($x, $y) {
        return view('map')->with('location', [
            'x' => $x,
            'y' => $y,
        ]);
    }
}
