<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Util;
use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RatingController extends Controller
{
    public function rating(Request $request){
        $user_rate = Util::get_user_answers($request->answers);
        $user_rate = round($user_rate,2);

        $reservation = Reservation::find($request->reservation_id);

        if ($reservation->user_id != Auth::id()){
            return Response(['msg' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $rating = Rating::updateOrCreate(['user_id' => $reservation->user_id, 'SP_id' => $reservation->SP_id], ['user_rating' => $user_rate]);

            $sp_average_rating = Rating::where('SP_id', $reservation->SP_id)->avg('user_rating');
            $avg = number_format(round($sp_average_rating,1), 1);
            User::where('id', $reservation->SP_id)->update(['rate' => $avg]);

            $reservation->update(['is_rated' => 1]);

            return response(['Rated successfully'], Response::HTTP_OK);
        }


    public function get_sp_ratings(Request $request){
        $ratings = User::whereId($request->input('SP_id'))->with('ratings')->firstOrFail();
        return $ratings;
    }


}
