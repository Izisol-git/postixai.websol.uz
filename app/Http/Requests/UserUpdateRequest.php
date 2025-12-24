<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $this->route('user')->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role_id' => 'sometimes|required|exists:roles,id',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'telegram_id' => 'sometimes|nullable|string|max:255|unique:users,telegram_id,' . $this->route('user')->id,
        ];
    }
}
