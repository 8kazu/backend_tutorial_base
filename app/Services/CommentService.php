<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function getCommentsByArticle(Article $article)
    {
        return $article->comments()->latest()->get();
    }

    public function createComment(Article $article, array $data)
    {
        return $article->comments()->create(array_merge($data, [
            'user_id' => Auth::id(),
        ]));
    }

    public function updateComment(Comment $comment, array $data)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403, '権限がありません');
        }

        $comment->update($data);
        return $comment;
    }

    public function deleteComment(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403, '権限がありません');
        }

        $comment->delete();
    }
}
