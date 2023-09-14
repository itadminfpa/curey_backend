<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'message' => $this->message,
            'is_seen' => $this->is_seen,
            'from' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans(),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('User'),
            'reply' => $this->whenLoaded('TicketReply')


        ];
    }
}
