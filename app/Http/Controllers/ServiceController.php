<?php

namespace App\Http\Controllers;

use PDO;
use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Choice;
use App\Models\Service;
use App\Models\SpNumber;
use App\Models\ServiceDay;
use App\Models\Reservation;
use App\Models\ServiceUser;
use Illuminate\Http\Request;
use App\Models\EmergReservation;
use App\Models\FcmTokens;
use App\Models\Question;
use App\Models\RejectedSp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function index(){
         return Service::all();
     }

     //** where user doesn't have */
    public function list_services()
    {
        $user_services = ServiceUser::where(['user_id' => Auth::id()])->pluck('service_id')->toArray();
        return Service::whereNotIn('id',$user_services)->get();
    }

    public function MyServices()
    {
        $services = ServiceUser::where('service_user.user_id',Auth::id())
            ->select("services.service_title","services.service_title_ar","services.icon_id" ,"service_user.id","services.id as original_service_id")
            ->join('services','services.id','=','service_user.service_id')
            ->get();

            return response()->json($services, Response::HTTP_OK);
        }


     public function destroy($id)
    {
        Service::destroy($id);
        return response(null,204);

    }


    public function edit_service(Request $request ,$id)
    {
        $service=Service::find($id);
        $service->update($request->only('service_title','service_title_ar', 'icon_id'));

        return response($service,Response::HTTP_OK);

    }

    public function add_service_details(Request $request,$service_user_id){
        $service_user=ServiceUser::findOrFail($service_user_id);
        $service_user->update($request->only('from','to','waiting_time_in_mins','charge'));

        if($request['days_array']){
            $days = $request['days_array'];
            ServiceDay::where('service_user_id',$service_user_id)->delete();
            foreach ($days as $day_id) {
                ServiceDay::create(
                    ['service_user_id'=>$service_user_id,
                    'day_id'=>$day_id
                    ]);
            }
        }

        return response()->json($service_user, Response::HTTP_OK);
    }
    /** For Dashboard */
     /** Service Details */
     public function serviceDetails($id){
        $service = Service::whereId($id)->firstOrFail();

        return response($service, Response::HTTP_OK);

    }
    /** end of service Details */

    /** End for dashboard */




    public function assign_service_to_sp($service_id){

        $authenticated = Auth::user()->id;
        $service_user=ServiceUser::where(['user_id'=> $authenticated,'service_id'=>$service_id])->first();
        if (! $service_user)
        {
            $service_user = ServiceUser::create(['user_id' => $authenticated, 'service_id' => $service_id]);
        }

        return response($service_user,Response::HTTP_CREATED);
    }


    public function unassign_service_to_sp($service_id)
    {
        $service_user=ServiceUser::where(['user_id'=> Auth::user()->id,'service_id'=>$service_id])->first();
        if ($service_user)
        {
            ServiceUser::destroy($service_user->id);
        }

        return response(["result"=> true],Response::HTTP_OK);
    }


    public function make_emerg_reservation_to_all(Request $request){

        $curr_lat = $request->input('latitude');
        $curr_lon = $request->input('longitude');
        $service_id = $request->input('service_id');

         $reservation = EmergReservation::create(['service_id'=> $service_id
            ,'user_id'=> Auth::user()->id, 'is_request_finished'=>'n','reservation_status_id'=> 1
            , 'reservation_date'=> date("Y-m-d"), 'call_time' => Carbon::now()->format("H:i"), 'current_lat'=> $curr_lat,'current_long' => $curr_lon
        ]);

        //to get sps' ids who should rececive the reservation, to send notification to them
        $users = Util::get_nearby_users($curr_lat, $curr_lon);
        $sps =  Service::get_sps_ids($reservation, $users);
        Util::sendNotification("New Emergency!", "You've received a new emergency request",FcmTokens::whereIn('user_id', $sps)->pluck('fcm_token')->toArray(), $sps, $reservation['user_id'], $reservation['id'],2);


        return response($reservation,Response::HTTP_CREATED);
    }



    public function sp_accept_emerg_reservation($emerg_reservation_id){

        $reservation = EmergReservation::find($emerg_reservation_id);
        // to prevent to be shown to user as accepted by sp( rejected after he accepted it)
        $authenticated = Auth::user()->only('id', 'name');
        if($reservation->reservation_status_id == 3 && $reservation->SP_id == $authenticated['id']){
            $reservation->reservation_status_id = 4;
            $reservation->save();
        }
        $accepted = RejectedSp::create(['emerg_reservation_id' => $emerg_reservation_id, 'SP_id' => $authenticated['id'], 'reservation_status_id' => 3]);
        $accepted->save();

        Util::sendNotification("SP confirmed your emergency!", $authenticated['name']." has confirmed emergency request", User::where('id',$reservation->user_id)->first()->getTokens(), [$reservation->user_id], $authenticated['id'], $emerg_reservation_id,2);

        return $accepted;

    }


    public function client_accept_emerg_reservation(Request $request,$emerg_reservation_id){

        $authenticated = Auth::user()->only('id', 'name');

        $reservation = EmergReservation::findOrFail($emerg_reservation_id);
        $service_user_id = ServiceUser::where(['service_id' => $reservation->service_id, 'user_id' => $request->input('SP_id')])->first()->id;
        $reservation->update(['SP_id' => $request->input('SP_id'),'service_user_id' => $service_user_id, 'reservation_status_id' => 3, 'client_accept_status' => 1,'secret_code' => mt_rand(1000, 9999)]);
        $reservation->save();


        Util::sendNotification("Client Confirmed!", $authenticated['name']." has confirmed emergency appointement", User::where('id',$reservation->SP_id)->first()->getTokens(), [$reservation->SP_id], $reservation->user_id, $emerg_reservation_id,2);
        Util::sendNotification("Appointment has been set!","An appointment has been made with ".$authenticated['name'].",your secret code is ". $reservation->secret_code, User::where('id',$reservation->user_id)->first()->getTokens(), [$reservation->user_id], $reservation->SP_id, $emerg_reservation_id,2);

        return $reservation;

    }



    //sp here can cancel after reservation confirmed by client and sp (final)
    public function sp_cancel_res_after_accepted_by_both($emerg_reservation_id){
        $reservation = EmergReservation::findOrFail($emerg_reservation_id);
        $reservation->update(['SP_id' => null,'reservation_status_id' => 1,'service_user_id' => null ,'client_accept_status' => 0]);
        $authenticated = Auth::user()->only('id', 'name');

        //update emerg status from previously accepted to rejected prevent showing to him again on pending emerg reservation page
        $add_to_rejected = RejectedSp::where(['emerg_reservation_id' => $emerg_reservation_id, 'SP_id' => $authenticated['id']]);
        $add_to_rejected->update(['reservation_status_id' => 4]);

        Util::sendNotification("Reserevation Cancelled!", "Your emergency reservation cancelled by ". $authenticated['name'] , User::where('id',$reservation->user_id)->first()->getTokens(), [$reservation->user_id], $authenticated['id'], $emerg_reservation_id,2);

        return response()->json(['msg' => 'Reservation Cancelled'], Response::HTTP_OK);

    }


    public function list_pending_emergancy_to_sp(){
// for locations

        $authenticated = Auth::id();
        $SP= User::find($authenticated);
        $latt = $SP->lat;
        $lon = $SP->long;
        if ((! $latt) || (! $lon)){
            return response(['message' => "latitude or longitude can't be null"],Response::HTTP_BAD_REQUEST);
        }
        $data = Util::get_nearby_locations($latt,$lon, 'emerg_reservations');
//end locations

// for service days
         $today = Carbon::now()->format('l');
         $service_days_of_user = ServiceUser::where(['user_id' => $authenticated])
        ->select('days.day_name','days.id AS Day_id', 'services.id')
        ->join('service_days','service_days.service_user_id', '=', 'service_user.id')
        ->join('services','service_user.service_id','=','services.id')
        ->join('days','service_days.day_id', '=', 'days.id')
        ->where('days.day_name', $today)
        ->distinct()
        ->pluck('services.id')->toArray();
//end service days

// remove rejected or accepted reservations

        $rejected = RejectedSp::where(['SP_id' => $authenticated])->pluck('emerg_reservation_id')->toArray();

//end of remove rejected reservations

        $reservations = EmergReservation::where(['SP_id'=> null, 'is_request_finished'=>'n', 'reservation_status_id'=> 1])
        ->whereIn('emerg_reservations.service_id',$service_days_of_user)
        ->whereIn('emerg_reservations.user_id',$data)
        ->whereNotIn('emerg_reservations.id', $rejected)
        ->select('emerg_reservations.*', 'profile_images.image_path','users.name','users.phone','services.service_title','services.service_title_ar','service_user.from','service_user.to', 'service_user.charge')
        ->leftjoin('users','emerg_reservations.user_id','=','users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        //->leftjoin('service_user','services.id','=','service_user.service_id')
        //->join('service_user','emerg_reservations.service_id','=', 'service_user.service_id')
        ->join('service_user', function($join) use ($authenticated){
            $join->on('service_user.service_id', '=', 'services.id');
            $join->where('service_user.user_id','=',$authenticated);
        })
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->whereRaw('TIME_FORMAT(emerg_reservations.call_time, "%H:%i") between TIME_FORMAT(service_user.from, "%H:%i") and TIME_FORMAT(service_user.to, "%H:%i")')
        ->whereDate('emerg_reservations.created_at', '>=', Carbon::now()->subDay())
        ->get();

        return response()->json($reservations,Response::HTTP_OK);
    }


    public function list_sp_accepted_res_for_client($reservation_id){
        $reservations = EmergReservation::where(['emerg_reservations.user_id' => Auth::user()->id, 'emerg_reservations.reservation_status_id' => 1 ,'is_request_finished'=>'n'])
        ->select('rejected_sps.*' ,'emerg_reservations.call_time','emerg_reservations.reservation_date','users.name','users.phone','users.address','users.rate','profile_images.image_path','service_user.service_id','service_user.from','service_user.to','service_user.waiting_time_in_mins','service_user.charge','services.service_title','services.service_title_ar')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->join('rejected_sps','emerg_reservations.id','=','rejected_sps.emerg_reservation_id')
        ->leftjoin('users','rejected_sps.SP_id','=','users.id')
        //->join('service_user','rejected_sps.service_user_id','=','service_user.id')
        ->join('service_user', function($join){
            $join->on('service_user.service_id', '=', 'services.id');
            $join->on('service_user.user_id','=','rejected_sps.SP_id');
        })
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->where('rejected_sps.reservation_status_id', 3)
        ->where('emerg_reservations.SP_id', null)
        ->where('rejected_sps.emerg_reservation_id', $reservation_id)
        ->get();

        return response()->json($reservations,Response::HTTP_OK);

    }

    public function list_client_accepted_res_for_sp(){

        // if client_accept_status = 0 => client still not accept
        // if client_accept_status = 1 => client accept
        $reservations = EmergReservation::where(['SP_id' => Auth::id(), 'reservation_status_id' => 3 ,'client_accept_status' => 1 ,'is_request_finished'=>'n'])
        ->select('emerg_reservations.*', 'profile_images.image_path','users.name', 'users.address', 'service_user.charge','services.service_title','services.service_title_ar')
        ->leftjoin('users','emerg_reservations.user_id','=','users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->get();

        return response()->json($reservations,Response::HTTP_OK);
    }


    public function store(Request $request)
    {
        $service= Service::create([

            'service_title' => $request->input('service_title')  ,
            'service_title_ar' => $request->input('service_title_ar'),
            'icon_id' => $request->input('icon_id'),
        ]);
        return response($service,Response::HTTP_CREATED);
    }

    //view single emerg reservation

    public function view_emerg_reservation($emerg_reservation_id)
    {
        $reservation=EmergReservation::where(['emerg_reservations.id'=> $emerg_reservation_id])
            ->select('emerg_reservations.*',DB::raw('LEFT(DAYNAME(emerg_reservations.reservation_date),3) AS Day'),'profile_images.image_path','users.name','users.phone', 'users.address','services.service_title','services.service_title_ar','service_user.charge', 'service_user.waiting_time_in_mins')
            ->leftjoin('users','emerg_reservations.user_id','=','users.id')
            ->join('services','emerg_reservations.service_id','=','services.id')
            //->leftjoin('service_user','services.id','=','service_user.service_id')
            ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->first();


            return response()->json($reservation,Response::HTTP_OK);
        }
    //End view single emerg reservation



    //for sp hisory, only finished requests
    public function list_all_reservations_to_SP(){

        $reservations = EmergReservation::where(['emerg_reservations.SP_id' => Auth::id(),'emerg_reservations.client_request_finished' => 'y'])
        ->select('emerg_reservations.*',DB::raw('LEFT(DAYNAME(emerg_reservations.reservation_date),3) AS Day'),'users.name','users.phone', 'services.service_title','services.service_title_ar','service_user.charge','profile_images.image_path')
        ->join('users', 'emerg_reservations.user_id','=' ,'users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->orderBy('emerg_reservations.reservation_date', 'DESC')
        ->get();


        return response()->json($reservations,Response::HTTP_OK);

    }

    //for client history, only finished requests
    public function list_all_reservations_to_client(){

        $reservations = EmergReservation::where(['emerg_reservations.user_id' => Auth::id(),'emerg_reservations.client_request_finished' => 'y'])
        ->select('emerg_reservations.*',DB::raw('LEFT(DAYNAME(emerg_reservations.reservation_date),3) AS Day'),'users.name','users.phone', 'services.service_title','services.service_title_ar','service_user.charge','profile_images.image_path')
        ->join('users', 'emerg_reservations.SP_id','=' ,'users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->orderBy('emerg_reservations.reservation_date', 'DESC')
        ->get();


        return response()->json($reservations,Response::HTTP_OK);

    }

    //for sp list of accepted by both sides, for appointments page:
    public function sp_user_confirmed_emerg(){

        $reservations = EmergReservation::where(['emerg_reservations.SP_id' => Auth::id(), 'emerg_reservations.client_request_finished' => 'n'])
        ->select('emerg_reservations.*', DB::raw('LEFT(DAYNAME(emerg_reservations.reservation_date),3) AS Day') ,'users.name','users.phone', 'services.service_title','services.service_title_ar','service_user.charge','profile_images.image_path', 'service_user.from', 'service_user.to','emerg_reservations.code_confirmed')
        ->join('users', 'emerg_reservations.user_id','=' ,'users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->where(['emerg_reservations.reservation_status_id' => 3,'emerg_reservations.client_accept_status' => 1 ])
        //->orderBy('emerg_reservations.reservation_date', 'DESC')
        ->latest()
        ->get();




        return response()->json($reservations,Response::HTTP_OK);
    }

    //for client list of accepted by both sides for appointments page
    public function client_emerg_appointments(){

        $reservations = EmergReservation::where(['emerg_reservations.user_id' => Auth::id(),'emerg_reservations.client_request_finished'=>'n'])
        ->select('emerg_reservations.*', DB::raw('LEFT(DAYNAME(emerg_reservations.reservation_date),3) AS Day'),'users.name','users.phone', 'services.service_title','services.service_title_ar','service_user.charge','service_user.from', 'service_user.to','profile_images.image_path')
        ->join('users', 'emerg_reservations.SP_id','=' ,'users.id')
        ->join('services','emerg_reservations.service_id','=','services.id')
        ->leftjoin('service_user','emerg_reservations.service_user_id','=','service_user.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->where(['emerg_reservations.reservation_status_id' => 3,'emerg_reservations.client_accept_status' => 1 ])
        //->orderBy('emerg_reservations.reservation_date', 'DESC')
        ->latest()
        ->get();


        //to get days name in ar
        //$reservations->each(fn($res) => $res['Day_in_ar'] = Util::get_day_in_ar($res['Day']));


        /* foreach($reservations as $key => $value) {
            $reservations[$key]['Day_in_ar'] = Util::get_day_in_ar($reservations[$key]['Day']);
        } */

        return response()->json($reservations,Response::HTTP_OK);
    }




    public function get_SP_service_details($service_user_id){

        $service_user=ServiceUser::where(['service_user.id'=> $service_user_id])
            ->select('service_user.*',"services.service_title","services.service_title_ar", "services.icon_id","services.id as original_service_id","users.rate","users.address","users.phone"
                ,"users.name","districts.district_title","profile_images.image_path")
            ->join('services','services.id','=','service_user.service_id')
            ->join('users','users.id','=','service_user.user_id')
            ->join('districts','districts.id','=','users.district_id')
            ->leftJoin('profile_images','users.id','=','profile_images.user_id')
            ->first();

        $days= ServiceDay::where(['service_user_id'=> $service_user->id ])->pluck('day_id');


        return ['details'=>$service_user,'days'=>$days,'numbers' => SpNumber::where(['user_id' => $service_user['user_id']])->pluck('number')];


    }

    //reject or ignore by sp in first step, no need to inform user
    public function RejectEmergency($emerg_reservation_id){
        $reservation = EmergReservation::find($emerg_reservation_id);
        // to prevent to be shown to user as accepted by sp( rejected after he accepted it)
        if($reservation->reservation_status_id == 3 && $reservation->SP_id == Auth::id()){
            $reservation->reservation_status_id = 4;
            $reservation->save();
        }
        $rejected = RejectedSp::create(['emerg_reservation_id' => $emerg_reservation_id, 'SP_id' => Auth::id()]);
        $rejected->save();

        return $rejected;
    }


    public function UserDestroyReservation($emerg_reservation_id)
    {
        $reservation = EmergReservation::findOrFail($emerg_reservation_id);
        $res = $reservation->delete();
        if ($res){
            $data=[
            'status'=>'1',
            'msg'=>'success'
          ];
          }else{
            $data=[
            'status'=>'0',
            'msg'=>'fail'
          ];
        }

          return response()->json($data);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function updateService(Request $request, $id)
    {
        $service=Service::find($id);
        $service->update($request->only('service_title','service_title_ar','icon_id'));
        return response($service,Response::HTTP_CREATED);
    }

    /** finish by sp */
    public function FinishRequest($emerg_reservation_id)
    {
        $reservation=EmergReservation::findOrFail($emerg_reservation_id);
        if ($reservation->code_confirmed){
            if($reservation->client_request_finished == 'n'){
                if ($reservation->SP_id == Auth::id()){
                    $reservation->is_request_finished="y";
                    $reservation->save();

                    return $reservation;
                }
                else{
                    return response(['Unauthorized'], Response::HTTP_UNAUTHORIZED);
                }
            }
        }

        return response(['msg' => "Secret code is not confirmed yet!"], Response::HTTP_UNPROCESSABLE_ENTITY);

    }
    /** end finish by sp */


     /** finish reservation by client */
     public function finish_reservation_by_client($emerg_reservation_id){
        $reservation=EmergReservation::find($emerg_reservation_id);

        if($reservation->user_id != Auth::id()){
            return response(['Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if ($reservation->is_request_finished== 'n'){
            return response(['msg' => 'Error! SP has NOT finished your reuqeust yet.'],Response::HTTP_BAD_REQUEST);
        }

        $reservation->update(['client_request_finished' => 'y']);
        return true;
    }
    /** end finish reservation by client  */



    public function codeConfirm(Request $request, $emerg_reservation_id){
        $reservation=EmergReservation::findOrFail($emerg_reservation_id);
        if($reservation->secret_code == $request->input('secret_code')){
            $reservation->update(['code_confirmed' => 1]);
            return response(["success" => true], Response::HTTP_OK);

        }else {
            return response(['success'=> false], Response::HTTP_UNPROCESSABLE_ENTITY);

        }
    }

}
