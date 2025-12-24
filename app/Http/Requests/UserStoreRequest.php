<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'telegram_id' => 'nullable|string|max:255|unique:users',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roleId = $this->input('role_id');
            $role = \App\Models\Role::find($roleId);

            if ($role && $role->name !== 'superadmin' && !$this->input('department_id')) {
                $validator->errors()->add('department_id', 'Department is required for non-superadmin users.');
            }
        });
    }
}
