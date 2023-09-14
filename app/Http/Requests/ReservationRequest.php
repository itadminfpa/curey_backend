<?php

namespace App\Http\Requests;

use App\Models\UserSection;
use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'SP_id'=>'required',
            'reservation_date'=>'required',
            'user_section_id'=>'required',
        ];
    }
}
