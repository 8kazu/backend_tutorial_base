<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="Comment operations"
 * )
 */


class CommentController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Comment",  
     *     type="object",  
     *     required={"content", "user_id", "article_id"},
     *     @OA\Property(property="id", type="integer", description="コメントID", example=1),
     *     @OA\Property(property="content", type="string", description="コメント内容", example="このコメントはサンプルです。"),
     *     @OA\Property(property="user_id", type="integer", description="コメントを投稿したユーザーのID", example=1),
     *     @OA\Property(property="article_id", type="integer", description="コメントが属する記事のID", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="コメントの作成日時", example="2024-12-01T12:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="コメントの更新日時", example="2024-12-01T12:00:00Z")
     * )
     */


    /**
     * コメント一覧取得 (GET)
     * @OA\PathItem(
     *     path="/api/articles/{article_id}/comments",
     *     description="指定された記事のコメントエンドポイントを表します"
     * )
     * @OA\Get(
     *     path="/api/articles/{article_id}/comments",
     *     summary="指定された記事に紐づくコメント一覧を取得します",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="article_id",
     *         in="path",
     *         required=true,
     *         description="コメント一覧を取得する対象の記事ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathItem(
     *         path="/api/articles/{article_id}/comments",
     *         description="指定された記事に紐づくコメント一覧を取得します"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="コメント一覧が正常に取得されました",
     *         @OA\JsonContent(
     *             type="array", 
     *             @OA\Items(ref="#/components/schemas/Comment")  
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="指定された記事が見つかりません",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Article not found")
     *         )
     *     )
     * )
     */
    public function index($article_id)
    {
        // 該当する記事を取得
        $article = Article::find($article_id);

        // 記事が存在しない場合は404を返す
        if (!$article) {
            return response()->json(['message' => '記事が見つかりません'], 404);
        }

        // 記事に紐づくコメントを取得
        $comments = $article->comments()->get();

        // コメント一覧を返す
        return response()->json([
            'message' => 'コメント一覧が正常に取得されました',
            'comment' => $comments, 
        ], 200);
    }
    

    /**
     * コメント投稿 (POST)
     * @OA\PathItem(
     *     path="/api/articles/{article_id}/comments",
     *     description="指定された記事のコメントエンドポイントを表します"
     * )
     * @OA\Post(
     *     path="/api/articles/{article_id}/comments",
     *     summary="指定された記事にコメントを投稿します",
     *     tags={"Comments"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="article_id",
     *         in="path",
     *         required=true,
     *         description="コメントを投稿する対象の記事のID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathItem(
     *     path="/api/articles/{article_id}/comments",
     *     description="指定された記事のコメントエンドポイントを表します"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="コメントの内容をJSON形式で送信します",
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(
     *                 property="content",
     *                 type="string",
     *                 description="投稿するコメント内容",
     *                 example="これはサンプルのコメントです。"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="コメントが正常に投稿されました",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="コメントが投稿されました。"),
     *             @OA\Property(property="comment", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="入力が無効です",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="入力が無効です。")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="認証が必要です",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="ログインが必要です。")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $article_id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'ログインが必要です。'], 401);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '入力が無効です。'], 400);
        }

        $comment = Comment::create([
            'article_id' => $article_id,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'コメントが投稿されました。', 'comment' => $comment], 201);
    }


    /**
     * コメント更新 (UPDATE)
     * @OA\PathItem(
     *     path="/api/articles/{article_id}",
     *     description="指定された記事のエンドポイントを表します"
     * )
     * @OA\Put(
     *     path="/api/comments/{comment_id}",
     *     summary="コメントを更新する",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathItem(
     *         path="/api/comments/{comment_id}",
     *         description="コメントを更新する"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", description="コメント内容", example="更新されたコメントです。")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="コメントが正常に更新されました",
     *         @OA\JsonContent(ref="#/components/schemas/Comment")  
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="権限がありません"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="入力が無効です"
     *     )
     * )
     */
    public function update(Request $request, $comment_id)
    {
        $comment = Comment::findOrFail($comment_id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '入力が無効です'], 404);
        }

        $comment->update(['content' => $request->content]);

        return response()->json(['message' => 'コメントが正常に更新されました。', 'comment' => $comment], 200);
    }
    /**
     * コメント削除 (DELETE)
     * @OA\PathItem(
     *     path="/api/articles/{article_id}",
     *     description="指定された記事のエンドポイントを表します"
     * )
     * @OA\Delete(
     *     path="/api/comments/{comment_id}",
     *     summary="コメントを削除する",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathItem(
     *         path="/api/comments/{comment_id}",
     *         description="コメントを削除する"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="コメントが正常に削除されました"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="権限がありません"
     *     )
     * )
     */
    public function destroy($comment_id)
    {
        $comment = Comment::findOrFail($comment_id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'コメントが正常に削除されました'], 204);
    }
}
