<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Laravel API",
 *     version="1.0.0",
 *     description="Laravel APIの概要を提供します。"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8002",
 *     description="ローカル開発環境"
 * )
 * 
 * @OA\Server(
 *     url="https://api.example.com",
 *     description="本番環境"
 * )
 * 
 * @OA\Tag(
 *     name="Default",
 *     description="基本的な操作"
 * )
 */
class ApiController extends Controller
{
    // ここに共通のメソッドやプロパティを追加することも可能です。
}

