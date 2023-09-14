<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Reservation;
use App\Models\UserSection;
use Illuminate\Http\Request;
use App\Models\RejectionReason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReservationRequest;
use App\Http\Requests\RejectionReasonRequest;
use App\Http\Traits\FcmNotificationTrait;
use App\Jobs\FCMsend;
use App\Models\FcmMessages;
use App\Models\Rating;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    //
    public function make_reservation(ReservationRequest $request){
        $user_section=UserSection::find($request->input('user_section_id'));
        $reservation=Reservation::create(['SP_id'=> $request->input('SP_id')
            ,'user_section_id'=>$request->input('user_section_id'),'user_id'=>Auth::user()->id
            ,'reservation_date'=> $request->input('reservation_date'),'start_time'=> $request->input('start_time'),'end_time'=>$request->input('start_time')+1, 'is_request_finished'=>'n'
            ,'reservation_status_id'=> 1,'reservation_type_id'=>1,'section_id'=>$user_section->section_id
        ]);

        if (! $reservation){
            return response(['msg' => "error"], Response::HTTP_NO_CONTENT);
        }

        Util::sendNotification("new reservation!", "You've received a new reservation request", User::where('id',$reservation['SP_id'])->first()->getTokens(), [$reservation['SP_id']], $reservation['user_id'], $reservation['id'], 1);

        return response($reservation, Response::HTTP_CREATED);

    }

    public function make_oncall_reservation(Request $request){
        $reservation=Reservation::create(['section_id'=>$request->input('section_id'),'user_id'=>Auth::user()->id
            ,'is_request_finished'=>'n' ,'reservation_status_id'=> 1,'reservation_type_id'=>2
            ,'reservation_date' => date("Y-m-d")
        ]);

        return $reservation;

    }

    public function list_oncall_to_SP(){
        $section_ids=UserSection::where(['is_emergency'=>'y'])->distinct()->pluck('section_id');
        // return $section_ids;
        return Reservation::where(['SP_id'=> null ,'reservation_type_id' => 2 ,'is_request_finished'=>'n'])
            ->whereIn('section_id', $section_ids)
            ->select('reservations.*','profile_images.image_path','users.name','sections.section_title','sections.section_title_ar')
            ->join('users','reservations.user_id','=','users.id')
            ->join('sections','reservations.section_id','=','sections.id')
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->get();

    }


    public function list_reservations_to_SP(){
        $reservations = Reservation::where(['reservations.SP_id'=> Auth::id(),'reservations.is_request_finished'=>'n'])
            ->whereIn('reservations.reservation_status_id', [1,2])
            ->select('reservations.*', DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'),'profile_images.image_path','users.name', 'users.phone','sections.section_title','sections.section_title_ar')
            ->join('users','reservations.user_id','=','users.id')
            ->join('user_sections','user_sections.id','=','reservations.user_section_id')
            ->join('sections','user_sections.section_id','=','sections.id')
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->get();



            return response($reservations, Response::HTTP_OK);

    }



    //** appointments for client */
    public function list_reservations_to_client(){
            $reservations = Reservation::where(['reservations.user_id'=> Auth::user()->id, 'is_rated' => 0])
            ->select('reservations.*',DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'),'reasons_of_rejection.reason as reason_of_rejection','rejection_reasons.comment','profile_images.image_path','users.name','sections.section_title','sections.section_title_ar', 'reservations.is_rated')
            ->join('users','reservations.SP_id','=','users.id')
            ->join('user_sections','user_sections.id','=','reservations.user_section_id')
            ->leftjoin('rejection_reasons','reservations.id','=','rejection_reasons.reservation_id')
            ->leftjoin('reasons_of_rejection','reasons_of_rejection.id','=','rejection_reasons.rejection_reason_id')
            ->join('sections','user_sections.section_id','=','sections.id')
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
            ->latest()
            ->distinct()
            ->get();

            return response($reservations, Response::HTTP_OK);

    }

    public function accept_oncall_reservation(Request $request,$reservation_id)
    {
        $reservation=Reservation::findOrFail( $reservation_id);
        $reservation->reservation_status_id=3;
        $reservation->SP_id=Auth::user()->id;
        $reservation->secret_code=mt_rand(1000, 9999);

        $reservation->save();

        return $reservation;
    }

    public function reject_reservation(RejectionReasonRequest $request,$reservation_id)
    {
        $reservation=Reservation::findOrFail($reservation_id);
        $reservation->reservation_status_id=4;
        $reservation->save();

        $reservation->reason()->create(['rejection_reason_id' => $request->input('rejection_reason_id'), 'comment' => $request->input('comment') ?? null]);
        Util::sendNotification("Oops", "Your Reservation for ".Auth::user()->name ." has been declined", User::where('id',$reservation->user_id)->first()->getTokens(), [$reservation->user_id], $reservation->SP_id, $reservation_id,1);
        return response($reservation,Response::HTTP_OK);
    }

    public function getRejectionReasons(){
        return DB::table('reasons_of_rejection')->select('id','reason', 'reason_ar')->get();

    }

    public function view_reservation($reservation_id)
    {
        $reservation=Reservation::where(['reservations.id'=> $reservation_id])
            ->select('reservations.*',DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'),'profile_images.image_path','users.name', 'users.address','users.phone','sections.section_title','sections.section_title_ar', 'user_sections.charge')
            ->join('users','reservations.user_id','=','users.id')
            ->join('user_sections','user_sections.id','=','reservations.user_section_id')
            ->join('sections','user_sections.section_id','=','sections.id')
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->first();

        if($reservation->reservation_status_id == 1 && Auth::user()->role_id == 2) {
            $reservation->update(['reservation_status_id' => 2]);
            $reservation->save();
        }

       // $reservation['Day_in_ar'] = Util::get_day_in_ar($reservation['Day']);
        return response($reservation, Response::HTTP_OK);
    }

    public function accept_reservation(Request $request,$reservation_id)
    {
        $reservation=Reservation::findOrFail( $reservation_id);
        $reservation->reservation_status_id=3;
        $reservation->secret_code=mt_rand(1000, 9999);
        $reservation->save();

        Util::sendNotification("Congrats!", "Your reservation for ". Auth::user()->name." has been confirmed, your secret code is ". $reservation->secret_code, User::where('id',$reservation->user_id)->first()->getTokens(), [$reservation->user_id], $reservation->SP_id,$reservation_id , 1);

        return $reservation;
    }


    //appiontments screen for sp
    public function SP_accepted_reservation(Request $request)
    {
        $reservations = Reservation::where(['SP_id'=> Auth::id(),'client_request_finished'=>'n','reservation_status_id'=>3])
            ->select('reservations.*', DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'),'profile_images.image_path','users.name', 'users.address','users.phone','sections.section_title','sections.section_title_ar','user_sections.charge', 'reservations.code_confirmed')
            ->join('users','reservations.user_id','=','users.id')
            ->join('user_sections','user_sections.id','=','reservations.user_section_id')
            ->join('sections','sections.id','=','reservations.section_id')
            ->latest()
            ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')->get();



            return response($reservations, Response::HTTP_OK);
    }


    //finish by sp
    public function finish_reservation($reservation_id)
    {
        $reservation=Reservation::findOrFail($reservation_id);
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

    /** finish reservation by client */
    public function finish_reservation_by_client($reservation_id){
        $reservation=Reservation::findOrFail($reservation_id);

        // if($reservation->user_id != Auth::id()){
        //     return response(['Unauthorized'], Response::HTTP_UNAUTHORIZED);
        // }

        if ($reservation->is_request_finished== 'n'){
            return response(['msg' => 'Error! SP has NOT finished your reuqeust yet.'],Response::HTTP_BAD_REQUEST);
        }

        $reservation->update(['client_request_finished' => 'y']);
        return $reservation;
    }
    /** end finish reservation by client  */

    public function codeConfirm(Request $request, $reservation_id){
        $reservation=Reservation::findOrFail($reservation_id);
        if($reservation->secret_code == $request->input('secret_code')){
            $reservation->update(['code_confirmed' => 1]);
            return response(["success" => true], Response::HTTP_OK);

        }else {
            return response(['success'=> false], Response::HTTP_UNPROCESSABLE_ENTITY);

        }
    }

    public function unread_reservations(){
        $count = Reservation::where(['SP_id' => Auth::id(), 'reservation_status_id' => 1, 'is_request_finished' => 'n'])->count();


        return response(['status' => $count > 0 ? true : false]);

    }

    //RESERVATIONS HISTORY FOR CLIENT
    public function clientReservaionsHistory(){
        $reservations = Reservation::where(['reservations.user_id' => Auth::id(), 'reservations.client_request_finished' => 'y'])
        ->select('reservations.*',DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'), 'reservation_statuses.status_title', 'reservation_statuses.status_title_ar','users.name','users.phone', 'profile_images.image_path','sections.section_title','sections.section_title_ar')
        ->join('users','reservations.SP_id','=','users.id')
        ->join('user_sections','user_sections.id','=','reservations.user_section_id')
        ->join('sections','user_sections.section_id','=','sections.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->join('reservation_statuses', 'reservation_statuses.id', '=', 'reservations.reservation_status_id')
        ->orderBy('reservations.reservation_date', 'DESC')
        ->get();



        return response($reservations, Response::HTTP_OK);
}


    //for website

//all reservations HISTORY FOR SP
    public function reservaionsHistory(){
        $reservations = Reservation::where(['reservations.SP_id' => Auth::id(), 'reservations.client_request_finished' => 'y'])
        ->select('reservations.*',DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'), 'reservation_statuses.status_title', 'reservation_statuses.status_title_ar','users.name','users.phone', 'profile_images.image_path','sections.section_title','sections.section_title_ar')
        ->join('users','reservations.user_id','=','users.id')
        ->join('user_sections','user_sections.id','=','reservations.user_section_id')
        ->join('sections','user_sections.section_id','=','sections.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->join('reservation_statuses', 'reservation_statuses.id', '=', 'reservations.reservation_status_id')
        ->orderBy('reservations.reservation_date', 'DESC')
        ->get();



        return response($reservations, Response::HTTP_OK);
}


    public function latestReservations(){
        $reservations = Reservation::where(['reservations.SP_id' => Auth::id(), 'reservations.is_request_finished' => 'n'])
        ->whereIn('reservations.reservation_status_id', [1,2])
        ->select('reservations.*',DB::raw('LEFT(DAYNAME(reservations.reservation_date),3) AS Day'),'users.name','users.phone', 'profile_images.image_path','sections.section_title','sections.section_title_ar')
        ->join('users','reservations.user_id','=','users.id')
        ->join('user_sections','user_sections.id','=','reservations.user_section_id')
        ->join('sections','user_sections.section_id','=','sections.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->orderBy('reservations.reservation_date', 'DESC')
        ->take(6)->get();



        return response($reservations, Response::HTTP_OK);
}

    public function nearestAppointments(){
        $reservations = Reservation::where(['reservations.SP_id' => Auth::id(), 'reservations.reservation_status_id' => 3, 'reservations.is_request_finished' => 'n'])
        ->select('reservations.id','reservations.reservation_date','users.name', 'profile_images.image_path','sections.section_title','sections.section_title_ar')
        ->join('users','reservations.user_id','=','users.id')
        ->join('user_sections','user_sections.id','=','reservations.user_section_id')
        ->join('sections','user_sections.section_id','=','sections.id')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->orderBy('reservations.reservation_date', 'DESC')
        ->take(4)->get();

        return response($reservations, Response::HTTP_OK);
    }

    // end for website
}
