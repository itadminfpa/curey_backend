<?php

namespace App\Http\Controllers;

use PDO;
use Stringable;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\FcmTokens;
use Illuminate\Support\Str;
use App\Models\ProfileImage;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\ForgotRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\UpdateAdminProfileRequest;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request['verification_status_id'] = 1; //to auth only verfied users
        if(Auth::attempt($request->only('email','password','role_id', 'verification_status_id')))
        {
            $user=Auth::user();

            $token=$user->createToken('user')->accessToken;

            return response($user)->header('token',$token);

        }

        if ($UnAuthUser = User::where('email', $request->input('email'))->first()){
            if (    (Hash::check($request->input('password'), $UnAuthUser->password))   && ($UnAuthUser->role_id == $request->input('role_id'))){
                //all user's creaditional are correct but account not verified
                $status = User::where('users.id',$UnAuthUser->id)->select('verification_status.status')
                ->join('verification_status', 'verification_status.id', '=', 'users.verification_status_id')
                ->value('verification_status.status');

                return response(['status' => $status], Response::HTTP_UNAUTHORIZED);
            }

        }

        return response([
            'error' => 'Invalid Credentials'
        ],Response::HTTP_UNAUTHORIZED);
    }

    public function register(UserCreateRequest $request){
        $user= User::create([
            'name' => $request->input('name') ,
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role_id' => $request->input('role_id'),
            'verification_status_id' => $request->input('role_id') == 2 ? 2 : 1,

        ]);

        $user->update($request->only('lat','long','gender','address', 'phone'));

        if ( $request->has('image_string') ){

            $path=Util::saveBase64Decoded($request->input('image_string'),"uploads/".$user->id."/profile_imgs",$request->input('ext'));
            $profile_image=ProfileImage::create(['user_id'=>$user->id,'image_path'=> $path]);
            $user->profile_image_id=$profile_image->id;
            $user->save();
        }

        if ($request->has('country_title') && $request->has('city_title') && $request->has('district_title')){
            $district_id=Util::get_district_id_from_country_and_city_and_district($request["country_title"],$request["city_title"],$request["district_title"]);
            $user->district_id=$district_id;
            $user->save();
        }
        if ($request->has('sp_numbers')){
            Util::add_or_update_sp_numbers($user->id,$request["sp_numbers"]);

        }


        if ($request->has('license') && $request->has('sp_type')){
           // $image = base64_encode(file_get_contents($request->file('license')));
            $file = $request->input('license');  //base64 encoded file
            $path = "SP_licenses/$user->id";
            $license_path=Util::saveBase64Decoded($file,$path,$request->input('license_ext'));
            $user->license()->create(['license' => $license_path, 'sp_type_id' => $request->input('sp_type')]);
        }


        /// should be last check
        if($user->verification_status_id != 1){
            $status = User::where('users.id',$user['id'])->select('verification_status.status')
            ->join('verification_status', 'verification_status.id', '=', 'users.verification_status_id')
            ->value('verification_status.status');
        return response(['status' => $status], Response::HTTP_OK);
        }

        return response($user,Response::HTTP_CREATED)->header('token',$user->createToken('user')->accessToken);

    }

    public function edit_profile(Request $request,$id){
        $user=User::find($id);
        $user->update($request->only('name','email','phone', 'description' ,'role_id','lat','long', 'password' ,'gender','address'));

        if ( $request->has('image_string') ){

            $path=Util::saveBase64Decoded($request->input('image_string'),"uploads/".$user->id."/profile_imgs",$request->input('ext'));
            $profile_image=ProfileImage::create(['user_id'=>$user->id,'image_path'=> $path]);
            $user->profile_image_id=$profile_image->id;
            $user->save();
        }

        if ($request->has('country_title') && $request->has('city_title') && $request->has('district_title')){
            $district_id=Util::get_district_id_from_country_and_city_and_district($request["country_title"],$request["city_title"],$request["district_title"]);
            $user->district_id=$district_id;
            $user->save();
        }
        if ($request->has('sp_numbers') ){
            Util::add_or_update_sp_numbers($user->id,$request["sp_numbers"]);

        }
        return response($user)->header('token',$user->createToken('user')->accessToken);

    }

    public function logout(Request $request)
    {
        if ($request->has('fcm_token')){
            FcmTokens::where(['fcm_token' => $request->input('fcm_token')])->delete();
        }
        auth()->user()->token()->revoke(); //logout from current device
        return response()->json([
            'message' => 'Successfully logged out'
        ]);

    }

    /** for dashboard to edit admin profile */
    public function editAdminProfile(UpdateAdminProfileRequest $request){

        $user= Auth::user();

        $user->update($request->only('name','email', 'password'));

        if ( $request->has('image') ){
              //  $file = base64_encode(file_get_contents($request->file('icon_image')));
            $path=Util::saveBase64Decoded($request->input('image'),"uploads/".$user->name."/profile_imgs",$request->input('ext'));
            $profile_image=ProfileImage::create(['user_id'=>$user->id,'image_path'=> $path]);
            $user->profile_image_id=$profile_image->id;
            $user->save();
        }

        return response($user)->header('token',$user->createToken('user')->accessToken);

    }

    /** end for dashboard to edit admin profile */


    public function saveToken(Request $request)
    {
        //return auth()->user()->getTokens();
        auth()->user()->fcm_tokens()->updateOrCreate(['fcm_token'=> $request->input('exp_token') ?? $request->input('fcm_token')],['fcm_token'=>$request->input('fcm_token')]);
        return response()->json(['token saved successfully.']);
    }


    public function forgot(ForgotRequest $request) {

        $email = $request->input('email');

        if (User::whereEmail($email)->doesntExist()){
            return response(['message' => 'user does not exist'], 404);
        }
        $token = Str::random(10);

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        //sendemail
        /* Mail::send('resetmail', ['token' => $token, 'name' => User::whereEmail($email)->first()->name], function (Message $message) use ($email) {
            $message->from('curey@noreply.com', 'Curey Team');
            $message->sender('curey@noreply.com', 'Curey Team');
            $message->to('DeadCode8@gmail.com');
            $message->subject('Reset Password');
        }); */


        return response()->json(["msg" => 'Reset password link sent on your email.']);
    }

    public function resetPassword(ResetRequest $request){
        $token = $request->input('token');
        if (! $passwordResets = DB::table('password_resets')->whereToken($token)->first()){
            return response([
                'message' => 'invalid token'
            ], 400);
        }
        if (Carbon::now()->greaterThan($passwordResets->expires_at)){
            return response([
                'message' => 'expired token'
            ], 400);
        }
        if (! $user = User::whereEmail($passwordResets->email)->first()){
            return response([
                'message' => 'User does not exist'
            ], 404);
        }
        $user->password =  $request->input('password');
        $user->save();
        return response([
            'message' => 'success'
        ]);
    }
}
