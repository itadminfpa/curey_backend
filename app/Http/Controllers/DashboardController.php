<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\User;
use App\Models\Section;
use App\Models\Service;
use App\Models\SpNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    /** for SP filters */
    public function hospitalFilter(Request $request){
        $users = User::where(['users.role_id' => 2])
        ->join('verification_status', 'verification_status.id', '=', 'users.verification_status_id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->select('users.*','verification_status.status', 'verification_status.status_ar','profile_images.image_path');
        if ($request->filled('topten')) {
            $users->latest('rate')
            ->take(10)
            ->get();
        }
        if ($request->filled('alpha')) {
            $users->where('name', 'LIKE', $request->input('alpha') . '%')->get();
        }
        if ($request->filled('city')) {
            $users->where('districts.city_id', $request->input('city'))
            ->join('districts', 'users.district_id', '=', 'districts.id')
            ->join('cities', 'districts.city_id', '=', 'cities.id')
            ->get();
        }
        if ($request->filled('section')) {
            $users->where('user_sections.section_id', $request->input('section'))
            ->join('user_sections', 'user_sections.user_id', '=', 'users.id')
            ->join('sections', 'user_sections.section_id', '=', 'sections.id')
            ->addSelect(['sections.section_title', 'sections.section_title_ar']); //to add to select for the main query
        }

        if ($request->filled('verification_status')) {
            $users->where('users.verification_status_id', $request->input('verification_status'));
        }
        return $users->oldest('users.created_at')->get();

    }
    /** End For SP filters */


    /** For Patient Filter */
    public function patientFilter(Request $request){
        $users = User::where(['users.role_id' => 3])
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->select('users.*','profile_images.image_path');

        if ($request->filled('alpha')) {
            $users->where('name', 'LIKE', $request->input('alpha') . '%')->get();
        }

        if ($request->filled('city')) {
            $users->where('districts.city_id', $request->input('city'))
            ->join('districts', 'users.district_id', '=', 'districts.id')
            ->join('cities', 'districts.city_id', '=', 'cities.id')
            ->get();
        }

        return $users->oldest('users.created_at')->get();        ;

    }
    /** End for patient filter */

    /** for count staticts*/
    public function SP_count(){
        /** and is verified */
        $users = User::where(['role_id' => 2, 'verification_status_id' => 1])->count();
        return response($users, Response::HTTP_OK);
    }

    public function clientsCount(){
        $users = User::where('role_id', 3)->count();
        return response($users, Response::HTTP_OK);
    }
    /** end for count staticts */


    /** for map tab in dashboard */
    public function mapLocations(){
        $users = User::where(['role_id' => 2, 'verification_status_id' => 1])->selectRaw('users.id, users.lat, users.long AS lng, users.name')->get();

       /*  $users->each(function($user){
            $user['lna'] = floatval($user['lng']);
        }); */
        return response($users, Response::HTTP_OK);
    }
    /** end for map tab in dashboard */

    /** verfying user */
    /** 1 for approve, 2 for pending, 3 for reject */
    public function userVerification(Request $request){
        $user = User::whereId($request->input('SP_id'))->update(['verification_status_id' => $request->input('verification_id')]);

        if (! $user){
            return response(['msg' => 'error'], Response::HTTP_BAD_REQUEST);
        }


        return response(['msg' => 'Successfully changed account status to '. DB::table('verification_status')->whereId($request->input('verification_id'))->value('status')], Response::HTTP_OK);
    }

    /** end verfying user */

    /** To list all cities */
    public function allCities(){
        return City::all();
    }

    /** end listing all cities */

    /** Hospital Details */
    public function spDetails($id){
        $details = User::where('users.id',$id)
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->first();

        return response(['details' => $details, 'Numbers' => SpNumber::where(['user_id' => $id])->pluck('number')], Response::HTTP_OK);
    }

    /** End of Hospital Details */

    /** Client Details */
    public function clientDetails($id){
        $details = User::where('users.id',$id)
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->first();

        return response($details, Response::HTTP_OK);
    }
    /** End of Client Details */

    public function adminInfo(){

        return Auth::user()->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->first(['users.id', 'users.name', 'users.email', 'profile_images.image_path']);
    }

    /** review SP license and type */
    public function reviewLicense($id){
        return $user = User::whereId($id)->with('license')->first();
    }


    /** end Get SP license */


}
