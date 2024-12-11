<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->string('title'); // 記事のタイトル
            $table->text('content'); // 記事の内容
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
