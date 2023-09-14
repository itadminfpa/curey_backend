<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TicketsRequest;
use App\Http\Resources\TicketsResource;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function requestTicket(TicketsRequest $request){
        $ticket = Auth::user()->tickets()->create($request->all() + ['from' => Carbon::now(), 'is_seen' => 0]);

        return response(new TicketsResource($ticket), Response::HTTP_CREATED) ;
    }


    public function getAllTickets(){
        $tickets = Ticket::with('TicketReply','User')->latest()->get();

        return response(TicketsResource::collection($tickets), Response::HTTP_OK) ;
    }


    public function getSingleTicket($id){
        $ticket = Ticket::with('TicketReply')->where('tickets.id',$id)->first();
        $ticket->update(['is_seen' => 1]);

        return response(new TicketsResource($ticket), Response::HTTP_OK);
    }

    public function adminReply($id,Request $request){
        $ticket = Ticket::with('TicketReply')->where('tickets.id',$id)->first();
        $ticket->TicketReply()->create(['admin_reply' => $request->input('TicketReply')]);

        return response(new TicketsResource($ticket), Response::HTTP_OK);
    }


    public function countUnseen(){
        $count = Ticket::whereIsSeen(0)->count();

        return response($count, Response::HTTP_OK);

    }

    public function getMyTickets(){
        $tickets = Ticket::with('TicketReply')->where('tickets.user_id',Auth::id())->latest()->get();

        return response(TicketsResource::collection($tickets), Response::HTTP_OK) ;

    }
}
