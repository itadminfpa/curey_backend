<?php

namespace App\Http\Controllers;

use PDO;
use App\Models\User;
use Kreait\Firebase;
use App\Models\FcmMessages;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{

    // protected $database;

    // public function __construct()
    // {
    //     $this->database = app('firebase.database');
    // }
    public function getNotifications(){
        $notifications = FcmMessages::where('fcm_messages.user_id',Auth::id())
        ->select('fcm_messages.id','fcm_messages.user_id','fcm_messages.title','fcm_messages.from' ,'fcm_messages.body','notification_types.type', 'b.id as action_by_user_id','b.name as action_by_name', 'fcm_messages.redirection_id', 'profile_images.image_path as action_by_profile_path')
        ->join('notification_types', 'fcm_messages.notification_type_id', '=', 'notification_types.id')
        ->join('users as b','fcm_messages.action_by_id','=','b.id')
        ->join('users as r','fcm_messages.user_id','=','r.id')
        ->leftjoin('profile_images','b.profile_image_id','=','profile_images.id')
        ->latest('fcm_messages.from')
        ->get();

        FcmMessages::query()->where('fcm_messages.user_id', Auth::id())->update(['is_seen' => 1]);


        return response($notifications, Response::HTTP_OK);

    }

    // public function index(){
    //     //path of file in contorller
    //     $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/curey-9ac82-firebase-adminsdk-ualgr-d7b8c9eb2b.json');

    //     $firebase = (new Factory)
    //     ->withServiceAccount($serviceAccount)
    //     ->withDatabaseUri('https://curey-9ac82-default-rtdb.firebaseio.com/');

    //     $user = Auth::id();

    //     $users[] = [51 => ['unseen' => 6], 65 => ['unseen' => 3]];

    //     $database = $firebase->createDatabase();
    //     $newPost = $database
    //     ->getReference()
    //    /* ->push([
    //         $user =>  ['unseen' => 5]
    //     ])->getKey(); */
    //     ->update([
    //         'notifications' => [
    //             'users' => [$user => ['unseen' => 6]]
    //         ]

    //        ]);
          /* //  'notifications' => [
            'users' => [$user => ['unseen' => 6], 78 => ['unseen' => 5]]
            ] */

        //$newPost->getKey(); // => -KVr5eu8gcTv7_AHb-3-
        //$newPost->getUri(); // => https://my-project.firebaseio.com/blog/posts/-KVr5eu8gcTv7_AHb-3-
        //$newPost->getChild('title')->set('Changed post title');
        //$newPost->getValue(); // Fetches the data from the realtime database
        //$newPost->remove();
        //}

}
