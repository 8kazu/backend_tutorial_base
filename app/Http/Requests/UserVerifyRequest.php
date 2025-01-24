<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required|string',
            'name' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
    

    public function messages(): array
    {
        return [
            'token.required' => 'トークンは必須です。',
            'name.required' => '名前は必須です。',
            'name.max' => '名前は最大255文字までです。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
        ];
    }
}
