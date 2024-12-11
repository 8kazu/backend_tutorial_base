<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="Operations related to article management"
 * )
 */

class ArticleController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="Article",
     *     type="object",
     *     description="記事スキーマ",
     *     @OA\Property(property="id", type="integer", description="記事ID", example=1),
     *     @OA\Property(property="title", type="string", description="記事タイトル", example="記事のタイトル"),
     *     @OA\Property(property="content", type="string", description="記事内容", example="記事の内容です。"),
     *     @OA\Property(property="user_id", type="integer", description="作成者のユーザーID", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="作成日時", example="2024-12-01T00:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="更新日時", example="2024-12-02T00:00:00Z")
     * )
     */


    /**
     * @OA\PathItem(
     *     path="/api/articles",
     *     description="記事一覧取得エンドポイントを表します"
     * )
     * @OA\Get(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="記事一覧を取得する",
     *     description="すべての記事を取得します。",
     *     operationId="getArticles",
     *     @OA\Response(
     *         response=200,
     *         description="記事一覧が正常に取得されました",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Article")
     *         )
     *     )
     * )
     */
    // 記事一覧取得
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $articles], 200);
    }

    /**
     * @OA\PathItem(
     *     path="/api/articles/{articleId}",
     *     description="特定の記事取得エンドポイントを表します"
     * )
     * @OA\Get(
     *     path="/api/articles/{article_id}",
     *     tags={"Articles"},
     *     summary="記事を取得する",
     *     description="特定の記事を取得します。",
     *     operationId="getArticle",
     *     @OA\Parameter(
     *         name="article_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="記事が正常に取得されました",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     )
     * )
     */
    // 特定の記事取得
    public function show($articleId)
    {
        $article = Article::findOrFail($articleId);
        return response()->json(['data' => $article], 200);
    }

    /**
     * @OA\PathItem(
     *     path="/api/articles",
     *     description="記事作成エンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="記事を作成する",
     *     description="新しい記事を作成します。",
     *     operationId="createArticle",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", description="記事タイトル", example="Sample Title"),
     *             @OA\Property(property="content", type="string", description="記事内容", example="This is a sample article.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="記事が正常に作成されました",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     )
     * )
     */
    // 記事作成
    public function store(Request $request)
    {
        // 認証チェック
        if (!Auth::check()) {
            return response()->json(['message' => '認証されていません。'], 401);
        }

        // バリデーション
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        // 記事作成
        $article = Article::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => Auth::id(), // ログイン中のユーザーのIDを設定
        ]);

        return response()->json(['data' => $article], 201);
    }

    /**
     * @OA\PathItem(
     *     path="/api/articles/{article_id}",
     *     description="記事更新エンドポイントを表します"
     * )
     * @OA\Put(
     *     path="/api/articles/{article_id}",
     *     tags={"Articles"},
     *     summary="記事を更新する",
     *     description="特定の記事を更新 (PUT)",
     *     operationId="updateArticle",
     *     @OA\Parameter(
     *         name="article_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", description="更新後のタイトル", example="Updated Title"),
     *             @OA\Property(property="content", type="string", description="更新後の内容", example="Updated content.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="記事が正常に更新されました"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="権限がありません"
     *     )
     * )
     */
    // 記事更新
    public function update(Request $request, $articleId)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $article = Article::findOrFail($articleId);

        // 記事の作成者のみ更新可能
        if ($article->user_id !== Auth::id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $article->update($request->only(['title', 'content']));

        return response()->json(['message' => '記事が正常に更新されました', 'data' => $article], 200);
    }

    /**
     * @OA\PathItem(
     *     path="/api/articles/{article_id}",
     *     description="記事削除エンドポイントを表します"
     * )
     * @OA\Delete(
     *     path="/api/articles/{article_id}",
     *     tags={"Articles"},
     *     summary="記事を削除する",
     *     description="特定の記事を削除します。",
     *     operationId="deleteArticle",
     *     @OA\Parameter(
     *         name="article_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="記事が正常に削除されました"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="権限がありません"
     *     )
     * )
     */
    // 記事削除
    public function destroy($articleId)
    {
        $article = Article::findOrFail($articleId);

        // 記事の作成者のみ削除可能
        if ($article->user_id !== Auth::id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $article->delete();

        return response()->json(['message' => '記事が正常に削除されました'], 204);
    }
}
