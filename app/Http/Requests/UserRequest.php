<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'min:8', 'max:255'],
        ];
    }
}
