<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = \App\Models\Comment::class;

    public function definition()
    {
        return [
            'content' => $this->faker->text(100),
            'article_id' => \App\Models\Article::factory(), // 記事と関連付け
            'user_id' => \App\Models\User::factory(), // ユーザーと関連付け
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
