<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPreRegisterRequest;
use App\Http\Requests\UserVerifyRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\UserService;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle user pre-registration.
     *
     * @param UserPreRegisterRequest $request
     * @return JsonResponse
     */
    public function preregister(UserPreRegisterRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];
        $this->userService->handlePreRegistration($email);

        return (new MessageResource(['message' => '仮登録が完了しました。メールをご確認ください。']))->response();
    }

    /**
     * Verify user registration.
     *
     * @param UserVerifyRequest $request
     * @return JsonResponse
     */
    public function verify(UserVerifyRequest $request): JsonResponse
    {
        $user = $this->userService->verifyRegistration($request->validated());

        return (new MessageResource(['message' => '本登録が完了しました。', 'user' => new UserResource($user)]))->response();
    }

    /**
     * Handle user login.
     *
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $this->userService->loginUser($request->validated());

        return response()->json($data);
    }

    /**
     * Handle user logout.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return (new MessageResource(['message' => 'ログアウトが成功しました']))->response();
    }

    /**
     * Show user information.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        return (new UserResource($user))->response();
    }

    /**
     * Update user information.
     *
     * @param UserUpdateRequest $request
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = $this->userService->updateUser(Auth::user(), $request->validated());

        return (new MessageResource(['message' => 'ユーザー情報が正常に更新されました', 'user' => new UserResource($user)]))->response();
    }

    /**
     * Delete user account.
     *
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        Auth::user()->delete();

        return (new MessageResource(['message' => 'ユーザーが正常に削除されました']))->response();
    }
}
