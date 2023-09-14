<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactUsResource;
use PDO;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        $contact = Contact::create($request->all());

        if (! $contact){
            return response(['msg' => "failed"], Response::HTTP_BAD_REQUEST);
        }



       /*  Mail::send('mail', array(
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'body' => $request->get('message'),
        ), function($message) use ($request){
            $message->from($request->email, 'Curey');
            $message->to('DeadCode8@gmail.com', $request->name)->subject("New Email From ".ucwords(strtolower($request->get('name'))));
        }); */

        return response(['msg' => 'message sent successfully'], Response::HTTP_OK);


    }

    /** for admin to  receive messages in dashboard */
    public function getAllMessages(){
        $messages = Contact::latest()->get();

        Contact::query()->update(['is_seen' => 1]);

        return response(ContactUsResource::collection($messages), Response::HTTP_OK);
    }

    public function newMessagesCount(){
        $count = Contact::where('is_seen', 0)->count();

        return response(['msg' => $count], Response::HTTP_OK);
    }

    /** End messages dashboard */
}
