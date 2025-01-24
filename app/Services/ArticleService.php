<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
    public function getAllArticles()
    {
        return Article::orderBy('created_at', 'desc')->get();
    }

    public function getArticleById($id)
    {
        return Article::findOrFail($id);
    }

    public function createArticle(array $data, $userId)
    {
        return Article::create(array_merge($data, ['user_id' => $userId]));
    }

    public function updateArticle(Article $article, array $data, $userId)
    {
        if ($article->user_id !== $userId) {
            abort(403, '権限がありません');
        }

        $article->update($data);
        return $article;
    }

    public function deleteArticle(Article $article, $userId)
    {
        if ($article->user_id !== $userId) {
            abort(403, '権限がありません');
        }

        $article->delete();
    }
}
