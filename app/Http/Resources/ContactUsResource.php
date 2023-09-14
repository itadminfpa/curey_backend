<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'from' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans(),
        ];
    }
}
