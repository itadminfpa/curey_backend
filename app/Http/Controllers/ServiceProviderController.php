<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Section;
use App\Models\SpNumber;
use App\Models\SectionDay;
use App\Models\Reservation;
use App\Models\UserSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Prophecy\Argument\Token\InArrayToken;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\UserSectionDetailsResource;

class ServiceProviderController extends Controller
{
    //
    public function list_available_SPs(Request $request){


        $district_ids= $request->has('district_ids') ? $request->input('district_ids')
            : User::whereNotNull('district_id')->distinct()->pluck('district_id');

        $user_ids=$request->input('rate') == 'y' ? User::whereIn('district_id', $district_ids)->orderBy('rate', 'DESC')->pluck('id')
            : User::whereIn('district_id', $district_ids)->pluck('id');

        $user_section_ids=$request->input('by_low') == 'y' ? UserSection::whereIn('user_id',$user_ids)->orderBy('charge', 'ASC')->pluck('id')
            : UserSection::whereIn('user_id',$user_ids)->pluck('id');

        $section_id = $request->input('section_id');
        $all_sps_for_section = Section::find($section_id)->users->pluck('id');

        $user_section=UserSection::whereIn('user_sections.id',$user_section_ids)
            ->select('user_sections.*',"sections.section_title","sections.section_title_ar","users.rate","users.name","districts.district_title","profile_images.image_path")
            ->join('sections','sections.id','=','user_sections.section_id')
            ->join('users','users.id','=','user_sections.user_id')
            ->join('districts','districts.id','=','users.district_id')
            ->leftJoin('profile_images','users.id','=','profile_images.user_id')->get();


        $res = $user_section->whereIn('user_id', $all_sps_for_section)->where('section_id', $section_id)->unique()->all();

        return response()->json(['User_Section' => $res], Response::HTTP_OK);

    }

    public function get_SP_section_details($user_section_id){

        $user_section=UserSection::where(['user_sections.id'=> $user_section_id])
            ->select('user_sections.*',"sections.section_title","sections.section_title_ar","sections.icon_id","users.rate","users.address","users.phone"
                ,"users.name","districts.district_title","profile_images.image_path")
            ->join('sections','sections.id','=','user_sections.section_id')
            ->join('users','users.id','=','user_sections.user_id')
            ->join('districts','districts.id','=','users.district_id')
            ->leftJoin('profile_images','users.id','=','profile_images.user_id')
            ->first();

        $days= SectionDay::where(['user_section_id'=> $user_section->id ])->pluck('day_id');


        return ['details'=>$user_section,'days'=>$days,'numbers' => SpNumber::where(['user_id' => $user_section['user_id']])->pluck('number')];


    }

    public function get_SP_section_available_days($user_section_id){

//        day=Carbon::
        $day_with_dates=Util::get_dates_with_days($user_section_id,Carbon::today()->format('Y-m-d'),Carbon::today()->addWeeks(2)->format('Y-m-d'));
        return $day_with_dates;


    }

    public function get_available_hours($user_section_id, $date){

        $UserSection = UserSection::find($user_section_id);
        $res = $UserSection->available_hours($UserSection,$user_section_id,$date);
        return $res;

    }

    public function list_SPs(){
        $SPs=User::where(['role_id'=>2])
            ->select('users.*','profile_images.image_path')
            ->where('users.verification_status_id', 1)
            ->leftjoin('profile_images','profile_images.user_id','=','users.id');

        return $SPs->paginate();

//


    }

    public function get_nearest_locations(Request $request, $section_id){

        $latt = $request->input('latitude');
        $lon = $request->input('longitude');
        $RADIUS = 20;


        $data = Cache::remember('locations_'.$section_id.'_'.Json_encode($request->all()), 600, function () use ($latt, $lon, $RADIUS, $section_id){
            return DB::table("users")
            ->select("users.name","users.rate","users.address","users.lat","users.long","user_sections.*","sections.section_title","sections.section_title_ar","districts.district_title","profile_images.image_path"
            ,DB::raw("6371 * acos(cos(radians(" . $latt . "))
            * cos(radians(users.lat))
            * cos(radians(users.long) - radians(" . $lon . "))
            + sin(radians(" .$latt. "))
            * sin(radians(users.lat))) AS distance"))
            ->join('districts','districts.id','=','users.district_id')
            ->join('user_sections','user_sections.user_id','=','users.id')
            ->join('sections','user_sections.section_id','=','sections.id')
            ->leftJoin('profile_images','users.id','=','profile_images.user_id')
            ->where('users.role_id', '=', 2)
            ->where('users.verification_status_id', 1)
            ->where('sections.id', '=' , $section_id)
            ->having('distance', '<', $RADIUS)
            ->orderBy('distance','ASC')
            ->groupBy('users.id')
            ->distinct()
            ->get();
        });

        return response()->json($data, Response::HTTP_OK);
    }


// For Website
    public function highestRate(){
        $users = User::select('users.name', 'users.description','profile_images.image_path', 'users.rate','users.lat', 'users.long' )
        ->where('users.verification_status_id', 1)
        ->leftJoin('profile_images','users.id','=','profile_images.user_id')
        ->orderBy('rate', 'desc')
        ->first();

        return response()->json($users, Response::HTTP_OK);

    }


    public function topTen(){
        $users = User::select('users.name', 'profile_images.image_path', 'users.rate', 'users.lat', 'users.long')
        ->where('users.verification_status_id', 1)
        ->leftJoin('profile_images','users.id','=','profile_images.user_id')
        ->orderBy('rate', 'desc')
        ->take(10)
        ->get();

        return response()->json($users, Response::HTTP_OK);
    }

//End for Website

}
