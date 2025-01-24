<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $articles], 200);
    }

    public function show(Article $article)
    {
        return response()->json(['data' => $article], 200);
    }

    public function store(Request $request)
    {
        // バリデーションと記事作成を一度に実行
        $article = Article::create(
            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
            ]) + ['user_id' => Auth::id()] // ユーザーIDを追加
        );

        return response()->json(['data' => $article], 201);
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        // 記事の作成者のみ更新可能
        if ($article->user_id !== Auth::id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $article->update($request->only(['title', 'content']));

        return response()->json(['message' => '記事が正常に更新されました', 'data' => $article], 200);
    }

    public function destroy(Article $article)
    {
        // 記事の作成者のみ削除可能
        if ($article->user_id !== Auth::id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $article->delete();

        return response()->json(['message' => '記事が正常に削除されました'], 204);
    }
}
