<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Day;
use App\Models\City;
use App\Models\Choice;
use App\Models\Country;
use App\Models\District;
use App\Models\SpNumber;
use App\Models\FcmTokens;
use App\Models\SectionDay;
use App\Models\FcmMessages;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use PHPUnit\Framework\Warning;
use PhpParser\Node\Expr\Array_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\ServiceAccount;

class Util
{

    public static function saveBase64Decoded($base64_string, $path,$extension)
    {
        if (!file_exists( $path)) {

            mkdir($path, 0777, true);
        }
        $content = base64_decode($base64_string);  // your base64 encoded
        $fileName = Str::random(10) . uniqid() .'.'.$extension;
        file_put_contents($path . "/" . $fileName, $content);     // Save our content to the file.
        return $path . "/" . $fileName;
    }

    public  static  function get_district_id_from_country_and_city_and_district($country_title,$city_title, $district_title){
        $country=Country::where(['country_title'=>$country_title])->first();
        if(!$country){
            $country=Country::create(['country_title'=>$country_title]);
        }
        return self::get_district_id_from_city_and_district($country->id,$city_title,$district_title);
    }

    public static function get_district_id_from_city_and_district($country_id ,$city_title, $district_title)
    {
        $city=City::where(['city_title'=>$city_title,'country_id' => $country_id])->first();
        if(!$city){
            $city=City::create(['city_title'=>$city_title,'country_id' => $country_id]);
            $district=District::create(['city_id'=>$city->id,'district_title'=>$district_title]);
            return $district->id;
        }else if(! District::where(['district_title'=>$district_title,'city_id'=>$city->id])->first()){
            $district=District::create(['city_id'=>$city->id,'district_title'=>$district_title]);
            return $district->id;
        } else {
            $district=District::where(['district_title'=>$district_title,'city_id'=>$city->id])->first();
            return  $district->id;
        }

    }

    public static function get_dates_with_days($user_section_id, $from_date,$to_date)
    {
        $dates_arr=[];
        $day=Carbon::parse($from_date);
        $end_day=Carbon::parse($to_date);
        $days=  SectionDay::where(['user_section_id'=>$user_section_id])
            ->select('days.*')
            ->join('days','section_days.day_id','=','days.id')->pluck('day_name')->all();
        while ($day <= $end_day) {

            if( in_array( $day->format("l") , $days ) ){
                array_push($dates_arr,[
                    'id'=>Day::where(['day_name'=> $day->format("l") ])->get()->first()->id,
                    'date' => $day->format("Y-m-d"),
                    'day_name' => $day->format("l")
                ]);
            }
            $day=$day->addDays(1);
        }

        return $dates_arr;

    }

    public static function get_day_in_ar($day){
        $days_en = array ("Sat", "Sun", "Mon", "Tue", "Wed" , "Thu", "Fri");
        $days_in_ar = array ("السبت", "الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة");
        $key = array_search($day, $days_en);
        return $days_in_ar[$key];
    }

    public static function add_or_update_sp_numbers($user_id, $numbers_array)
    {
        SpNumber::where(['user_id'=>$user_id])->delete();
        foreach($numbers_array as $number){
            SpNumber::create(['user_id'=>$user_id,'number'=>$number]);
        }

    }

    public static function get_nearby_locations($latt, $lon, $table){

    $RADIUS = 20;
    $nearby_ids = DB::table($table)
        ->select("$table.id","$table.user_id"
        ,DB::raw("6371 * acos(cos(radians(" . $latt . "))
        * cos(radians($table.current_lat))
        * cos(radians($table.current_long) - radians(" . $lon . "))
        + sin(radians(" .$latt. "))
        * sin(radians($table.current_lat))) AS distance"))
        ->whereNull("$table.SP_id")
        ->having('distance', '<', $RADIUS)
        ->orderBy('distance','ASC')
        ->groupBy('user_id')
        ->distinct()
        ->pluck('user_id')->toArray();

        return $nearby_ids;

    }

    public static function get_user_answers(Array $answers){

        $final = 0;
       foreach($answers as $key => $answer) {
        $final += Choice::where(['question_id' => $key+1, 'choice_no' => $answer ])->first()->score;
        }

        return ($final / 240) * 5;

    }


    /* public static function notifyByFirebase($title,$body,$tokens,$data = [])        // paramete 5 =>>>> $type
    {
        $SERVER_API_KEY = 'AAAAcZY87Eg:APA91bE8s4krPrxbjgoVAYKn1rMZIbJ-Geop8Vz1FAqajUXHZt8qhhF85PiedaZhogm0NpYx4eJNT71WQS21rX2RLqKQq27KCMDFt3vnaR7hlxbwcQpzRsu6dUsXqDslT206ljgsgUBe';
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'body' => $title,
            'title' => $body,
            'sound' => "default",
            'color' => "#203E78"
        ];
        $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];
        $fcmNotification = [
            'registration_ids' => $tokens, //multple token array
            // 'to'        => $token, //single token
            'notification' => $notification,
            'data' => $data
        ];
        $headers = [
            'Authorization: key='.$SERVER_API_KEY,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);
        // return $result;
    } */

    //for notification
    public static function get_nearby_users($latt, $lon){

        $RADIUS = 20;
        $nearby_ids = DB::table("users")
        ->select("users.id"
        ,DB::raw("6371 * acos(cos(radians(" . $latt . "))
        * cos(radians(users.lat))
        * cos(radians(users.long) - radians(" . $lon . "))
        + sin(radians(" .$latt. "))
        * sin(radians(users.lat))) AS distance"))
        ->where('users.role_id', '=', 2)
        ->where('users.verification_status_id', 1)
        ->having('distance', '<', $RADIUS)
        ->orderBy('distance','ASC')
        ->groupBy('users.id')
        ->distinct()
        ->pluck('users.id')->toArray();

        return $nearby_ids;

        }

        /** Push count to firebase */
   public static function initializeFirbaseDatabase(){
        //$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/curey-9ac82-firebase-adminsdk-ualgr-d7b8c9eb2b.json');
        $firebase = (new Factory)
        ->withServiceAccount(__DIR__.'/curey-9ac82-firebase-adminsdk-ualgr-d7b8c9eb2b.json')
        ->withDatabaseUri('https://curey-9ac82-default-rtdb.firebaseio.com/');
        return $database = $firebase->createDatabase();
    }

    public static function push_count_to_firbaseDatabase($user_id, $database){

        $current_val = $database->getReference("notifications/users/$user_id/unseen_count")->getValue();
        $database
        ->getReference("notifications/users/$user_id/")
        ->update([
            'unseen_count' => $current_val+1
        ]);
    }
/** end push count to firebase */



    public static function sendNotification($title, $body, $tokens, $user_ids, $action_by, $redirection_id,$type_id)
   {


        $SERVER_API_KEY = 'AAAAcZY87Eg:APA91bE8s4krPrxbjgoVAYKn1rMZIbJ-Geop8Vz1FAqajUXHZt8qhhF85PiedaZhogm0NpYx4eJNT71WQS21rX2RLqKQq27KCMDFt3vnaR7hlxbwcQpzRsu6dUsXqDslT206ljgsgUBe';

        $data = [

            "registration_ids" => $tokens,

            "notification" => [

                "title" => $title,

                "body" => $body,

                "sound"=> "default" // required for sound on ios

            ],

        ];

        $dataString = json_encode($data);

        $headers = [

            'Authorization: key=' . $SERVER_API_KEY,

            'Content-Type: application/json',

        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);



        //add notifications to db to view them later to user


       //user_ids are users that notifications sent to them
       //action by => id of user made the action (ex: id of sp accepted a reservation)
       //redirection id => is id of related notifcaiotn type(ex: 'reservation_id" of notification type of NORMAL RESERVATION)


        /** push to firebase */
       /** create notifications log */
       if ($type_id != 3){

        //if not notification message
       $database = self::initializeFirbaseDatabase();
        foreach ($user_ids as $user_id) {
            FcmMessages::create(['title' => $title,
            'body' => $body,
            'user_id' => $user_id,
            'action_by_id' => $action_by,
            'redirection_id' => $redirection_id,
            'from' => Carbon::now(),
            'notification_type_id' => $type_id]);

            ///** continue push to firebase */
            Self::push_count_to_firbaseDatabase($user_id, $database);
        /** end push to firebase */
        }

       }

        //end add notifications to db to view them later to user


        return $response = curl_exec($ch);
    }



}
