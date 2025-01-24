<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleStoreRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    // 記事一覧取得
    public function index()
    {
        $articles = $this->articleService->getAllArticles();
        return response()->json(ArticleResource::collection($articles), 200);
    }

    // 特定記事取得
    public function show(Article $article)
    {
        return response()->json(new ArticleResource($article), 200);
    }

    // 新規記事作成
    public function store(ArticleStoreRequest $request)
    {
        $article = $this->articleService->createArticle($request->validated(), Auth::id());
        return response()->json(new ArticleResource($article), 201);
    }

    // 記事更新
    public function update(ArticleUpdateRequest $request, Article $article)
    {
        $updatedArticle = $this->articleService->updateArticle($article, $request->validated(), Auth::id());
        return response()->json(new ArticleResource($updatedArticle), 200);
    }

    // 記事削除
    public function destroy(Article $article)
    {
        $this->articleService->deleteArticle($article, Auth::id());
        return response()->json(['message' => '記事が正常に削除されました'], 204);
    }
}
