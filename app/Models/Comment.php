<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // コメントテーブルと紐づけ
    protected $table = 'comments';

    // 複数代入可能な属性を定義
    protected $fillable = [
        'content',  // コメントの内容
        'user_id',  // コメントを投稿したユーザーのID
        'article_id', // コメントが属する記事のID
    ];

    // 作成日時と更新日時を自動的に管理する
    public $timestamps = true;

    // コメントが属する記事とのリレーション
    public function article()
    {
        return $this->belongsTo(Article::class);  // 1つのコメントは1つの記事に関連
    }

    // コメントを投稿したユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);  // 1つのコメントは1人のユーザーに関連
    }
}
