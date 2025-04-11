<?php

namespace App\Http\Requests\User;

use App\Rules\CheckVerified;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        /** 
         * ! Laravel will generate an exception that this email already exists, when the user sends an Update request using his old email address as the new email address
         * Validation: If email is provided then it must not match with any other user's email, except the user sending the PUT/PATCH request can update his own email with his existing email.
         * Because the PUT/PATCH request is IDEMPOTENT.  
         */
        return [
            'name' => 'sometimes|required',

            // 'email' => 'sometimes|required|email|unique:users,email,' . $this->user,
            // OR
            'email' => [
                'sometimes', 
                'required', 
                'email', 
                Rule::unique('users')->ignore($this->user)
            ],
            'password' => 'sometimes|required|min:6|confirmed',
            'admin' => [
                'sometimes',
                'required',
                'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
                new CheckVerified($this->user), // Rule to check if the User is allowed to update the 'admin' field in the User table
            ],
        ];
    }
}
