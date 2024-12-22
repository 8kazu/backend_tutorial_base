<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // 認証済みユーザーのみ許可
        return auth()->check();
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|max:255',
            'content' => 'sometimes|required|min:10|max:100',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です。',
            'title.max' => 'タイトルは255文字以内で指定してください。',
            'content.required' => 'コンテンツは必須項目です。',
        ];
    }
}
