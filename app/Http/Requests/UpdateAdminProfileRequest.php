<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminProfileRequest extends FormRequest
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
            'name' => 'string',
            'email' => 'email|unique:users,email,'.Auth::id(), ///or user $this->id, $this is for this request, and id is sent in function param num 2 it's last param to force ignore this user's id for unqiue
            'current_password' => 'required_with:password|password', //password is to match authenticared user password with input
            'password' => 'confirmed', //when new password entered, it should match "new_password_confirmation" field
        ];
    }
}
