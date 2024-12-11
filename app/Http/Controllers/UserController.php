<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Operations related to user management"
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="User",
     *     type="object",
     *     description="ユーザースキーマ",
     *     @OA\Property(property="id", type="integer", description="ユーザーID", example=1),
     *     @OA\Property(property="name", type="string", description="ユーザー名", example="John Doe"),
     *     @OA\Property(property="email", type="string", description="メールアドレス", example="johndoe@example.com"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="作成日時", example="2024-12-01T00:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新日時", example="2024-12-02T00:00:00Z")
     * )
     */



    // ==================== 認証関連 ====================


    // ================ユーザー登録ステップ1（仮登録）================
    /**
     *@OA\PathItem(
     *     path="/api/register/step1",
     *     description="ユーザー仮登録エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/register/step1",
     *     tags={"Users"},
     *     summary="ユーザー仮登録",
     *     description="メールアドレスを仮登録し、確認トークンを送信します。",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", description="登録するメールアドレス", example="johndoe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="仮登録が成功しました。トークンが送信されます。",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="仮登録が完了しました。メールをご確認ください。")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="無効なメールアドレスまたは既に登録済み。",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="メールアドレスが無効です。")
     *         )
     *     )
     * )
     */
    public function registerStep1(Request $request)
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

    // ================ユーザー登録ステップ2（本登録）================
    /**
     * @OA\PathItem(
     *     path="/api/register/step2",
     *     description="ユーザー本登録エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/register/step2",
     *     tags={"Users"},
     *     summary="ユーザー本登録",
     *     description="トークンを用いてユーザーを本登録します。",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "name", "password"},
     *             @OA\Property(property="token", type="string", description="仮登録時に送信されたトークン", example="abcdef123456"),
     *             @OA\Property(property="name", type="string", description="ユーザー名", example="John Doe"),
     *             @OA\Property(property="password", type="string", description="パスワード", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="本登録が成功しました。",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="本登録が完了しました。"),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="トークンが無効または入力が不正。",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="トークンが無効です。")
     *         )
     *     )
     * )
     */
    public function registerStep2(Request $request)
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

    // ================================
    // ログイン
    // ================================
    /**
     * @OA\PathItem(
     *     path="/api/login",
     *     description="ログインエンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Users"},
     *     summary="ユーザーログイン",
     *     description="既存のユーザーがログイン (POST)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", description="メールアドレス", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", description="パスワード", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ログインが成功しました",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description: 認証トークン, example="1|XyZ1234ExampleToken"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="認証失敗",
     *         )
     *     )
     * )
     */

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


        
    // ================================
    // ログアウト
    // ================================
    /**
     * @OA\PathItem(
     *     path="/api/logout",
     *     description="ログアウトエンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/logput",
     *     tags={"Users"},
     *     summary="ユーザーログアウト",
     *     description="既存のユーザーをログアウト (POST)",
     *     @OA\Response(
     *         response=200,
     *         description="ログアウトが成功しました"
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => '認証されていません'], 401);
        }

        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'ログアウトが成功しました'], 200);
    }
    

    // ==================== ユーザー管理 ====================
    // ユーザー情報の取得
    /**
     * @OA\PathItem(
     *     path="/api/users",
     *     description="ユーザー情報取得エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="ユーザー情報を取得する",
     *     description="現在のユーザーの情報を取得 (GET)",
     *     operationId="getUser",
     *     @OA\Response(
     *         response=200,
     *         description="ユーザー情報が正常に取得されました",
     *         @OA\JsonContent(
     *             $ref="#/components/schemas/User"
     *         )
     *     )
     * )
     */
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

    // ユーザー情報の更新
    /**
     * @OA\PathItem(
     *     path="/api/users",
     *     description="ユーザー情報更新エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="ユーザー情報を更新する",
     *     description="現在のユーザーの情報を更新 (PUT)",
     *     operationId="updateUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="更新後のユーザー名", example="New Name"),
     *             @OA\Property(property="password", type="string", description="更新後のパスワード", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ユーザー情報が正常に更新されました"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="バリデーションエラー"
     *     )
     */
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

    // ユーザー削除
    /**
     * @OA\PathItem(
     *     path="/api/users",
     *     description="ユーザー削除エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="ユーザーを削除する",
     *     description="現在のユーザーを削除 (DELETE)",
     *     operationId="deleteUser",
     *     @OA\Response(
     *         response=200,
     *         description="ユーザーが正常に削除されました"
     *     )
     */
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