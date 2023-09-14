<?php

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Contact;
use App\Models\Section;
use App\Models\SpNumber;
use App\Models\SectionDay;
use App\Models\FcmMessages;
use App\Models\Reservation;
use App\Models\UserSection;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\RejectionReason;
use App\Models\EmergReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\QuestionController;
use SebastianBergmann\CodeUnit\FunctionUnit;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaticPageController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ServiceProviderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//Route::Resources(['users' => UserController::class]);
//Route::get('users/{id}',[UserController::class,'show']);
//Route::post('users',[UserController::class,'store']);
//Route::put('users/{id}',[Us{{erController::class,'update']);
//Route::delete('users/{id}',[UserController::class,'destroy']);

Route::post('login',[AuthController::class,'login']);;
Route::post('register',[AuthController::class,'register']);;

Route::get('cities',[CityController::class,'index']);;
Route::get('cities/districts/{city_id}',[CityController::class,'districts']);;


Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::get('user',[UserController::class,'user']);
    Route::Resources(['users' => UserController::class]);
// edit profile
    Route::put('edit_profile/{id}',[AuthController::class,'edit_profile']);;
    Route::post('logout',[AuthController::class,'logout']);;


    Route::Resources(['sections' => SectionController::class]);

//    Service Provider APIs //
//    Sections //
    Route::get('list_sections_to_SP',[SectionController::class,'list_sections_to_SP']);
    Route::post('suggest_field',[SectionController::class,'suggest_field']);
    Route::post('assign_section_to_sp/{section_id}',[SectionController::class,'assign_section_to_sp']);
    Route::post('unassign_section_to_sp/{section_id}',[SectionController::class,'unassign_section_to_sp']);
    Route::get('list_SP_sections',[SectionController::class,'list_SP_sections']);
    Route::put('add_section_details/{user_section_id}',[SectionController::class,'add_section_details']);
    Route::put('sections/verify/{section_id}',[SectionController::class,'verify']);

//    Service Provider //
    Route::post('sp/list_available_SPs',[ServiceProviderController::class,'list_available_SPs']);
    Route::get('sp/get_SP_section_details/{user_section_id}',[ServiceProviderController::class,'get_SP_section_details']);
    Route::get('sp/get_SP_section_available_days/{user_section_id}',[ServiceProviderController::class,'get_SP_section_available_days']);
    Route::get('sp/list_SPs',[ServiceProviderController::class,'list_SPs']);
    Route::get('sp/get_available_hours/{user_section_id}/{date}',[ServiceProviderController::class,'get_available_hours']);
    Route::post('sp/get_nearest_locations/{section_id}',[ServiceProviderController::class,'get_nearest_locations']);


//    Reservation //
    Route::post('reservations/make_reservation',[ReservationController::class,'make_reservation']);
    Route::post('reservations/make_oncall_reservation',[ReservationController::class,'make_oncall_reservation']);
    Route::put('reservations/accept_reservation/{reservation_id}',[ReservationController::class,'accept_reservation']);
    Route::put('reservations/accept_oncall_reservation/{reservation_id}',[ReservationController::class,'accept_oncall_reservation']);
    Route::put('reservations/reject_reservation/{reservation_id}',[ReservationController::class,'reject_reservation']);
    Route::get('reservations/get_rejection_reasons',[ReservationController::class,'getRejectionReasons']);
    Route::put('reservations/finish_reservation/{reservation_id}',[ReservationController::class,'finish_reservation']);
    Route::put('reservations/finish_reservation_by_client/{reservation_id}',[ReservationController::class,'finish_reservation_by_client']);

    Route::get('reservations/list_reservations_to_SP',[ReservationController::class,'list_reservations_to_SP']);
    Route::get('reservations/list_oncall_to_SP',[ReservationController::class,'list_oncall_to_SP']);
    Route::get('reservations/SP_accepted_reservation',[ReservationController::class,'SP_accepted_reservation']);

    Route::get('reservations/list_reservations_to_client',[ReservationController::class,'list_reservations_to_client']);
    Route::get('reservations/client_reservaions_history',[ReservationController::class,'clientReservaionsHistory']);
    Route::get('reservations/view_reservation/{reservation_id}',[ReservationController::class,'view_reservation']);
    Route::post('reservations/confirm_code/{reservation_id}',[ReservationController::class,'codeConfirm']);
    Route::get('reservations/unread_reservations',[ReservationController::class,'unread_reservations']);


    // User Services //
    Route::get('list_services',[ServiceController::class,'list_services']);
    Route::post('add_service',[ServiceController::class,'store']);
    Route::put('add_service_details/{service_user_id}',[ServiceController::class,'add_service_details']);
    Route::post('assign_service_to_sp/{service_id}',[ServiceController::class,'assign_service_to_sp']);
    Route::get('unassign_service_to_sp/{service_id}',[ServiceController::class,'unassign_service_to_sp']);
    Route::get('list_pending_emergancy_to_sp/',[ServiceController::class,'list_pending_emergancy_to_sp']);
    Route::get('list_sp_accepted_res_for_client/{reservation_id}',[ServiceController::class,'list_sp_accepted_res_for_client']);
    Route::get('list_client_accepted_res_for_sp',[ServiceController::class,'list_client_accepted_res_for_sp']);
    Route::post('make_emerg_reservation_to_all',[ServiceController::class,'make_emerg_reservation_to_all']);
    Route::put('client_accept_emerg_reservation/{emerg_reservation_id}',[ServiceController::class,'client_accept_emerg_reservation']);
    Route::post('sp_accept_emerg_reservation/{emerg_reservation_id}',[ServiceController::class,'sp_accept_emerg_reservation']);
    Route::put('finish_request/{emerg_reservation_id}',[ServiceController::class,'FinishRequest']);
    Route::put('finish_reservation_by_client/{emerg_reservation_id}',[ServiceController::class,'finish_reservation_by_client']);
    Route::get('list_sp_services',[ServiceController::class,'MyServices']);
    Route::get('get_SP_service_details/{service_user_id}',[ServiceController::class,'get_SP_service_details']);
    Route::put('updateService/{id}',[ServiceController::class,'updateService']);
    Route::post('reject_emergency/{emerg_reservation_id}',[ServiceController::class,'RejectEmergency']);
    Route::DELETE('cancel_emerg_reservation/{emerg_reservation_id}',[ServiceController::class,'UserDestroyReservation']);
    Route::get('list_all_reservations_to_SP',[ServiceController::class,'list_all_reservations_to_SP']);
    Route::get('sp_user_confirmed_emerg',[ServiceController::class,'sp_user_confirmed_emerg']);
    Route::get('client_emerg_appointments',[ServiceController::class,'client_emerg_appointments']);
    Route::put('sp_cancel_res_after_accepted_by_both/{emerg_reservation_id}',[ServiceController::class,'sp_cancel_res_after_accepted_by_both']);
    Route::get('/view_emerg_reservation/{emerg_reservation_id}',[ServiceController::class,'view_emerg_reservation']);
    Route::post('confirm_code/{emerg_reservation_id}',[ServiceController::class,'codeConfirm']);
    Route::get('client_emerg_history',[ServiceController::class,'list_all_reservations_to_client']);



    //  END User Services //


    //  Chat feature  //

    Route::post('SendMessage/{receiver_id}',[MessageController::class,'sendMessage']);
    Route::get('getMyConversations',[MessageController::class,'getMyConversations']);
    Route::put('DeleteMessage/{message_id}',[MessageController::class,'DeleteMessage']);
    Route::get('getUserConversation/{receiver_id}',[MessageController::class,'getUserConversation']);
    Route::get('userInfo/{id}',[MessageController::class,'userInfo']);
    // END Chat feature  //




    /** Tickets */
    Route::post('request_ticket',[TicketController::class,'requestTicket']);
    Route::get('get_my_tickets',[TicketController::class,'getMyTickets']);
    Route::get('get_all_tickets',[TicketController::class,'getAllTickets'])->middleware('admin');
    Route::get('get_single_ticket/{id}',[TicketController::class,'getSingleTicket'])->middleware('admin');
    Route::post('reply_to_ticket/{id}',[TicketController::class,'adminReply'])->middleware('admin');
    Route::get('unseen_tickets',[TicketController::class,'countUnseen'])->middleware('admin');




    /** end Tickets */
    //Rating
    Route::post('score',[RatingController::class,'rating']);
    Route::post('get_sp_ratings',[RatingController::class,'get_sp_ratings']);
    Route::get('get_questions',[QuestionController::class,'get_questions']);
    //End of Rating


    //fcm token
    Route::post('saveToken',[AuthController::class,'saveToken']);
    Route::get('getNotifications',[NotificationController::class,'getNotifications']);
    Route::post('pushToFireBase',[NotificationController::class,'index']);
    //end fcm token

    /** Dashboard */

    Route::middleware(['admin'])->group(function () {
        Route::get('all_services',[ServiceController::class,'index']);
        Route::delete('destroy_service/{id}',[ServiceController::class,'destroy']);
        Route::post('edit_service/{id}',[ServiceController::class,'edit_service']);
        Route::get('sp_count',[DashboardController::class,'SP_count']);
        Route::get('clients_count',[DashboardController::class,'clientsCount']);
        Route::get('map_locations',[DashboardController::class,'mapLocations']);
        Route::post('update_static_pages',[StaticPageController::class,'updateStaticPages']);
        Route::post('update_admin_profile',[AuthController::class,'editAdminProfile']);
        Route::get('section_details/{id}',[SectionController::class,'sectionDetails']);
        Route::get('service_details/{id}',[ServiceController::class,'serviceDetails']);
        Route::post('hospital_filter',[DashboardController::class,'hospitalFilter']);
        Route::post('patient_filter',[DashboardController::class,'patientFilter']);
        Route::get('get_contacts_messages',[ContactController::class,'getAllMessages']);
        Route::get('new_messages_count',[ContactController::class,'newMessagesCount']);
        Route::post('user_verification',[DashboardController::class,'userVerification']);
        Route::get('get_all_cities',[DashboardController::class,'allCities']);
        Route::get('sp_details/{id}',[DashboardController::class,'spDetails']);
        Route::get('client_details/{id}',[DashboardController::class,'clientDetails']);
        Route::get('admin_info',[DashboardController::class,'adminInfo']);
        Route::get('review_license/{id}',[DashboardController::class,'reviewLicense']);


    });

    /*** end of Dashboard */




    Route::get('test', function(){

         $reservation = Reservation::whereId(200)->first(['SP_id', 'user_id']);

      /*   $users = User::where('role_id', 2)->join('verification_status', 'verification_status.id', '=', 'users.verification_status_id');
        ;
        return $users->where('users.verification_status_id', 2)
        ->get(); */
        // return User::where('role_id', 2)->where('user_sections.section_id', 1)
        // ->select('users.*', 'user_sections.section_id')
        //     /* ->join('user_sections', function($join){
        //         $join->on('user_sections.section_id', '=', 'services.id');
        //         $join->on('user_sections.user_id','=','rejected_sps.SP_id');
        //     }) */
        // ->join('user_sections', 'user_sections.user_id', '=', 'users.id')
        // ->join('sections', 'user_sections.section_id', '=', 'sections.id')
        // ->get();

       /*  $user= User::create([
            'name' => $request->input('name') ,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role_id'),
            'verification_status_id' => $request->input('role_id') == 2 ? 2 : 1,
        ]);
         if($user->verification_status_id != 1){
            $status = User::where('users.id',$user['id'])->select('verification_status.status')
            ->join('verification_status', 'verification_status.id', '=', 'users.verification_status_id')
            ->value('verification_status.status');
            return response(['status' => $status], Response::HTTP_UNAUTHORIZED);
        }

        return   User::where('districts.city_id', 1)
        ->join('districts', 'users.district_id', '=', 'districts.id')
        ->join('cities', 'districts.city_id', '=', 'cities.id')
        ->get();

 */



       //return DB::table('notification_types')->whereId(1)->value('type');
      // return array_merge(auth()->user()->getTokens(), User::find(56)->getTokens());
      //return FcmMessages::where('id', '13')->first();
      // return Util::sendNotification("this is title", "this is body", array_merge(auth()->user()->getTokens(), User::find(56)->getTokens()), [56 ,63]);
   });
});



// For Website

Route::get('highest_rate',[ServiceProviderController::class,'highestRate']);
Route::get('top_ten',[ServiceProviderController::class,'topTen']);
Route::get('getSixSystemSections',[SectionController::class,'getSixSystemSections']); //system six sections with no token
Route::get('getSections',[SectionController::class,'getFiveSections'])->middleware('auth:api'); //sp sections
Route::get('reservaionsHistory',[ReservationController::class,'reservaionsHistory'])->middleware('auth:api');
Route::get('latestReservations',[ReservationController::class,'latestReservations'])->middleware('auth:api');
Route::get('nearest_appointments',[ReservationController::class,'nearestAppointments'])->middleware('auth:api');
Route::post('update_news',[StaticPageController::class,'update_news'])->middleware('auth:api');
Route::get('get_news',[StaticPageController::class,'get_news']);
Route::post('updateTermsAndConditions',[StaticPageController::class,'updateTermsAndConditions'])->middleware('auth:api');
Route::get('getTermsAndConditions',[StaticPageController::class,'getTermsAndConditions']);
Route::get('get_static_pages',[StaticPageController::class,'getStaticPages']);
Route::get('get_sp_types',[StaticPageController::class,'listTypeIds']);



Route::post('contact_us',[ContactController::class,'store']);
Route::post('forgot',[AuthController::class,'forgot']);
Route::post('reset',[AuthController::class,'resetPassword']);


//End for Website

