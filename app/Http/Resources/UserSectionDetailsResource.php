<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSectionDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parsed = Carbon::parse($this->reservation_date);
        return [
            'id' => $this->id,
            'reservation_date' => $this->reservation_date,
            'day' => $parsed->format('l'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
