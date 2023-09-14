<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function index()
    {
        return User::where(['role_id'=>3, 'verification_status_id' => 1])->paginate();

    }

    public function show($id)
    {
        return User::find($id);
    }

    public function store(UserCreateRequest $request)
    {
        $user= User::create([

            'name' => $request->input('name')  ,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        return response($user,Response::HTTP_CREATED);
    }

    public function update(UserUpdateRequest $request,$id)
    {
        $user=User::find($id);
        $user->update($request->only('name','email'));
        return response($user,Response::HTTP_CREATED);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response(null,204);
    }

    public function user(){
        // return \Auth::user()->id;

        $user= User::where(['users.id'=> \Auth::user()->id ])
            ->select('users.*','profile_images.image_path','districts.district_title','cities.city_title')
            ->leftJoin('profile_images','users.profile_image_id','=','profile_images.id')
            ->leftJoin('districts','users.district_id','=','districts.id')
            ->leftJoin('cities','districts.city_id','=','cities.id')
            ->with('SpNumbers')
            ->first();

        return $user;
    }



}
