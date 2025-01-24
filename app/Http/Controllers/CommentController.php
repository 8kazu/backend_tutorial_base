<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Models\Comment;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index(Article $article)
    {
        $comments = $this->commentService->getCommentsByArticle($article);
        return response()->json(CommentResource::collection($comments), 200);
    }

    public function store(CommentStoreRequest $request, Article $article)
    {
        $comment = $this->commentService->createComment($article, $request->validated());
        return response()->json(new CommentResource($comment), 201);
    }

    public function update(CommentUpdateRequest $request, Comment $comment)
    {
        $updatedComment = $this->commentService->updateComment($comment, $request->validated());
        return response()->json(new CommentResource($updatedComment), 200);
    }

    public function destroy(Comment $comment)
    {
        $this->commentService->deleteComment($comment);
        return response()->json(['message' => 'コメントが正常に削除されました'], 204);
    }
}
