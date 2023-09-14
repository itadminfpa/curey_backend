<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user() &&  Auth::user()->verification_status_id == 1) {
            return $next($request);
     }

     $status = Auth::user()->verification_status_id == 2 ? "Pending" : "Rejected";
     //dd(Auth::user()->verification_status_id);
     return response(['msg' => 'Your Account status is '. $status .', it should be verified to perform this action'], Response::HTTP_UNAUTHORIZED);
    }
}
