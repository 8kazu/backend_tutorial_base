<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserService
{
    /**
     * 仮登録処理
     *
     * @param string $email
     * @return void
     */
    public function handlePreRegistration(string $email): void
    {
        $token = Str::random(60);

        // 仮トークンを保存
        Cache::put($token, $email, now()->addMinutes(30));

        // 仮トークンをメール送信
        Mail::raw("仮登録用のトークン: $token", function ($message) use ($email) {
            $message->to($email)->subject('仮登録のご案内');
        });
    }

    public function verifyRegistration(array $data): User
    {
        $email = Cache::get($data['token']);
    
        if (!$email) {
            abort(401, 'トークンが無効です。');
        }
    
        // 二重登録チェック
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            abort(409, 'このメールアドレスは既に登録されています。');
        }
    
        // ユーザー作成
        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password']),
        ]);
    
        // トークンを無効化
        Cache::forget($data['token']);
    
        return $user;
    }
    
    

    /**
     * ログイン処理
     *
     * @param array $data
     * @return array
     */
    public function loginUser(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        // ユーザーが存在しないか、パスワードが不一致の場合
        if (!$user || !Hash::check($data['password'], $user->password)) {
            abort(401, 'パスワードが違います。');
        }

        // トークン生成
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'message' => 'ログインが成功しました。',
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * ユーザー情報更新処理
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }
}
