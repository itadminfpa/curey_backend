<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class StaticPageController extends Controller
{
    //for website homescreen
    //id 1 for safety
    //2 for latest news
    public function update_news(Request $request){

       $section = DB::table('news')->where('id', $request->id);

        if ($request->has('image')){
            //$file = base64_encode(file_get_contents($request->file('image')));
            $file = $request->input('image');   //base64 encoded file
            $type = $request->id == 1 ? 'Safety' : 'LatestNews';
            $path = "news/$type";
            \File::cleanDirectory($path); //remove old files
            $name=Util::saveBase64Decoded($file,$path,$request->input('ext'));
            $section->update(['image_path' => $name]);
        }

        $section->update($request->only('title', 'content'));
        Cache::forget('news');
        //Redis::del('news');
        return response()->json(['successfully updated'], Response::HTTP_OK);

    }

    public function get_news(){

        $news = Cache::rememberForever('news', function () {
            return DB::table('news')->select('id','title', 'content', 'image_path')->get();
        });

        return response()->json($news, Response::HTTP_OK);

    }
    //end for website homescreen


    //terms and conditions
    public function updateTermsAndConditions(Request $request){

        DB::table('terms_and_conditions')->update($request->all());
        Cache::forget('terms');

        return response(['successfully updated'], Response::HTTP_OK);

    }

    public function getTermsAndConditions(){
        $tac = Cache::rememberForever('terms', function () {
            return DB::table('terms_and_conditions')->select('id', 'body')->get();
        });

        return response($tac, Response::HTTP_OK);

    }
    //end terms and conditions



    /** for static pages from dashboard */
    //1 for about us, 2 for Privacy Policy, 3 for Terms Of Us, 4 for Patients Privacy Policy
    public function updateStaticPages(Request $request){
        $page = DB::table('pages')->where('id', $request->input('id'))->update($request->only('body'));

        return response()->json(['msg' => 'successfully updated'], Response::HTTP_OK);
    }

    public function getStaticPages(){
        $page = DB::table('pages')->get(['id', 'body']);

        return response($page, Response::HTTP_OK);
    }
    /** end for static pages from dashboard */

    /** for sign up, to list type ids */
    public function listTypeIds(){
        return DB::table('sp_types')->get();
    }


    ///** end list type ids */


}
