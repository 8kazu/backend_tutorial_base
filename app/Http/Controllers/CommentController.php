<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index(Article $article)
    {
        // 直近のコメントから順に取得
        $comments = $article->comments()->latest()->get();

        return response()->json([
            'message' => 'コメント一覧が正常に取得されました',
            'comments' => $comments, 
        ], 200);
    }
    
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'content' => 'required|min:10|max:100',
        ]);

        $comment = $article->comments()->create([
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'コメントが投稿されました。', 'comment' => $comment], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $request->validate([
            'content' => 'required|min:10|max:100',
        ]);

        $comment->update(['content' => $request->content]);

        return response()->json(['message' => 'コメントが正常に更新されました。', 'comment' => $comment], 200);
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '権限がありません。'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'コメントが正常に削除されました'], 204);
    }
}
