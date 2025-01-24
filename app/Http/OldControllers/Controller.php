<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Laravel API Documentation",
 *         version="1.0.0",
 *         description="LaravelプロジェクトのAPI仕様書"
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8002",
 *         description="ローカル開発環境"
 *     ),
 *     @OA\Server(
 *         url="https://api.example.com",
 *         description="本番環境"
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
