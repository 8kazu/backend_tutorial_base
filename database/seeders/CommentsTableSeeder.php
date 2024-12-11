<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Article;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 各記事にランダムな数のコメントを作成
        Article::all()->each(function ($article) {
            // 各記事に3〜7件のコメントをランダムに作成
            Comment::factory()->count(rand(3, 7))->create([
                'article_id' => $article->id,
            ]);
        });
    }
}
