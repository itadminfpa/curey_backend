<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;

class CityController extends Controller
{
    //
    public function index(){
        return City::all();
    }

    public function districts(Request $request,$city_id)
    {
        return District::where(['city_id'=>$city_id])->get();
    }
}
