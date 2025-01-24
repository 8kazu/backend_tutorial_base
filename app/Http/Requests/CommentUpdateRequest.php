<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // 認証済みユーザーのみ許可
    }

    public function rules()
    {
        return [
            'content' => 'required|min:10|max:100',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'コメント内容は必須です。',
            'content.min' => 'コメントは10文字以上で入力してください。',
            'content.max' => 'コメントは100文字以内で入力してください。',
        ];
    }
}
