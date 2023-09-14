<?php

namespace App\Http\Controllers;

use PDO;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Util;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function sendMessage(Request $request, $receiver_id){

        $Authenticed = Auth::id();

        $Conversation = Conversation::whereIn('sender_id', [$Authenticed, $receiver_id])
         ->whereIn('send_to_id', [$Authenticed, $receiver_id])
         ->first();

        if (! $Conversation){

            $Conversation = Conversation::create(['sender_id' => $Authenticed, 'send_to_id' => $receiver_id]);
            $Conversation->save();
        }


        /* return $countUnseen = $Conversation->withCount(['messages AS unseen_messages' => function ($query) use ($Authenticed){
            $query->where([
                ['is_seen', '=', 0],
                ['is_deleted', '=', 0],
                ['user_id', '!=', $Authenticed],
            ]);}])->value('unseen_messages'); */

        $message = $Conversation->messages()->create(['user_id'=> $Authenticed,
        'body'=> $request->input('body'),
        'message_type_id'=> $request->input('message_type_id') ?? "1"]);

            if ($request->has('file')){

             $file = $request->input('file');   //base64 encoded file
             $type = Message::AddToTypeFolder($request->input('message_type_id'));
             $path = "chats/$Conversation->id/$type";
             $name=Util::saveBase64Decoded($file,$path,$request->input('ext'));
             $message->attachments()->create(['attachment_path' => $name]);

             }

            $Conversation->messages()->save($message);


            $Conversation->update(['updated_at' => $message->created_at]); //conv that has the recent message is at top
             //for notifying user of new messages
             // when front is fetching conversation ??
            /* $countUnseen = Message::where([
                ['conversation_id', '=', $Conversation->id],
                ['is_seen', '=', 0],
                ['is_deleted', '=', 0],
                ['user_id', '=', $Authenticed] ])->selectRaw('count(messages.id) AS count')->value('count');

            if ($countUnseen == 2){
                Util::sendNotification("A new message", "You have a new messages from ". Auth::user()->name, User::where('id',$receiver_id)->first()->getTokens(), [$receiver_id], $Authenticed,$Conversation->id , 3);
            } */


            return response($message, Response::HTTP_CREATED);
    }


    public function DeleteMessage($message_id){
        $message = Message::find($message_id);
        if ($message->user_id == Auth::id()){
            $message->is_deleted = 1;
            $message->save();
            return response(['msg' => 'Message successfully deleted' ], Response::HTTP_OK);
        }

        return response(['msg' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);

    }

    public function getMyConversations(){
        $userId =  Auth::id();

        $conversations = Conversation::where('sender_id', $userId)
        ->orWhere('send_to_id', $userId)
        //->whereRaw('(conversations.sender_id <> ?) AND (conversations.send_to_id <> ?)', [$userId, $userId])
        ->select('conversations.*','users.name as sender_name','r.name as send_to_user', 'p.image_path as sender_image_path' ,'profile_images.image_path as send_to_user_image_path')
        ->join('users as r','r.id','=','conversations.send_to_id')
        ->join('users','users.id','=','conversations.sender_id')
        ->leftjoin('profile_images as p','users.profile_image_id','=','p.id')
        ->leftjoin('profile_images','r.profile_image_id','=','profile_images.id')
        ->with(['latestMessage' => fn($query) => $query->where('is_deleted', 0)->with(['message_type', 'attachments'])])
        ->withCount(['messages AS unseen_messages' => function ($query) use ($userId){
            $query->where([
                ['is_seen', '=', 0],
                ['is_deleted', '=', 0],
                ['user_id', '!=', $userId],
            ]);
        }])->orderBy('conversations.updated_at', 'DESC')
        ->get();



    return response($conversations, Response::HTTP_OK);
    }


    public function getUserConversation($receiver_id){
       $Authenticed = Auth::user()->only('id', 'name');

        $conversation = Conversation::/* whereIn('sender_id', [$Authenticed, $receiver_id])
        ->whereIn('send_to_id', [$Authenticed, $receiver_id]) */
        whereRaw('(conversations.send_to_id = ? AND conversations.sender_id = ?) OR (conversations.send_to_id = ? AND conversations.sender_id = ?)',[$Authenticed['id'], $receiver_id, $receiver_id, $Authenticed['id']])
        ->select('conversations.*','users.name as sender_name','r.name as send_to_user','p.image_path as sender_image_path' ,'profile_images.image_path as send_to_user_image_path')
        ->join('users as r','r.id','=','conversations.send_to_id')
        ->join('users','users.id','=','conversations.sender_id')
        ->leftjoin('profile_images as p','users.profile_image_id','=','p.id')
        ->leftjoin('profile_images','r.profile_image_id','=','profile_images.id')
        ->with(['messages' => fn($query) => $query->where('is_deleted', 0)->with(['message_type', 'attachments'])])->firstOrCreate(['sender_id' => $Authenticed['id'], 'send_to_id' => $receiver_id]);
        $conversation->messages()->where('user_id','!=',$Authenticed['id'])->update(['is_seen' => 1]);

    //$messages_count = Message::where('conversation_id', $conversation['id'])->count();
        //$messages_count = $conversation->messages->count();


        //** for notification */
        //messgaes count to be used in notifictation(count messages every time to send notification only one time)
       $messages_count =count($conversation['messages']);

        //messages which i sent but still not seen by receiver
        $countUnseen = $conversation['messages']->where('is_seen', 0)->where('user_id', $Authenticed['id'])->count();
       /*  $countUnseen = Message::where([
            ['conversation_id', '=', $conversation['id']],
            ['is_seen', '=', 0],
            ['is_deleted', '=', 0],
            ['user_id', '=', $Authenticed->id] ])->count(); */


        if (($countUnseen == 1) && ($messages_count > $conversation->messages_count) ){

            Util::sendNotification("A new message", "You have a new messages from ". $Authenticed['name'], User::where('id',$receiver_id)->first()->getTokens(), [$receiver_id], $Authenticed['id'],$conversation['id'] , 3);
            $conversation->update(['messages_count' => $messages_count]);
           // return "dd";
        }
        /** end for notification */


        return response($conversation, Response::HTTP_OK);
    }

    public function userInfo($id){
        $user = User::where('users.id',$id)
        ->select('users.id','users.name', 'users.phone', 'users.email','users.address', 'profile_images.image_path')
        ->leftjoin('profile_images','users.profile_image_id','=','profile_images.id')
        ->first();
        return response($user, Response::HTTP_OK);

    }


}
