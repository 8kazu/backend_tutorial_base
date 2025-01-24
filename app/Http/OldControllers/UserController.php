<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;

class UserController extends Controller
{
    public function preregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '無効なメールアドレスまたは既に登録済み'], 400);
        }

        $token = Str::random(60);

        // 仮トークンを保存
        cache([$token => $request->email], now()->addMinutes(30));

        // 仮トークンをメール送信
        Mail::raw("仮登録用のトークン: $token", function ($message) use ($request) {
            $message->to($request->email)->subject('仮登録のご案内');
        });

        return response()->json(['message' => '仮登録が完了しました。メールをご確認ください。']);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '入力が無効です。'], 400);
        }

        $email = cache($request->token);

        if (!$email) {
            return response()->json(['message' => 'トークンが無効です。'], 401);
        }
        // ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
        ]);

        // トークンを無効化
        cache()->forget($request->token);

        return response()->json(['message' => '本登録が完了しました。', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '入力が無効です。'], 400);
        }

        // ユーザーを検索
        $user = User::where('email', $request->email)->first();

        // ユーザーが存在し、パスワードが一致するか確認
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'パスワードが違います。'], 401);
        }

        // トークンを作成
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'ログインが成功しました。',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => '認証されていません'], 401);
        }

        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'ログアウトが成功しました'], 200);
    }
    
    public function show()
    {
        $user = Auth::user();
    
        // 認証に失敗した場合の早期リターン
        if (!$user) {
            return response()->json([
                'message' => '認証失敗'
            ], 401);
        }
    
        // 認証成功時のレスポンス
        return response()->json([
            'message' => 'ユーザー情報が正常に取得されました。',
            'data' => $user, // 必要に応じてユーザー情報を整形
        ], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => '認証されていません。'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()->all(),
            ], 400);
        }

        if ($request->has('name')) {
            $user->name = $request->input('name');
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return response()->json(['message' => 'ユーザー情報が正常に更新されました', 'user' => $user], 200);
    }

    public function destroy()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => '認証されていません。'], 401);
        }

        $user->delete();

        return response()->json(['message' => 'ユーザーが正常に削除されました'], 200);
    }
}