<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    
    // 許可されたフィールド
    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    // リレーション: 記事はユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);  // 1つの記事は1人のユーザーに関連
    }

    // リレーション: 記事は複数のコメントを持つ
    public function comments()
    {
        return $this->hasMany(Comment::class);  // 1つの記事は複数のコメントを持つ
    }
}
