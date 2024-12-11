<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('user_id') // user_idカラムを追加
                  ->constrained('users') // usersテーブルと関連付け
                  ->onDelete('cascade') // ユーザーが削除された場合にコメントも削除
                  ->after('article_id'); // article_idの後に配置
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // 外部キー制約を削除
            $table->dropColumn('user_id'); // user_idカラムを削除
        });
    }
}
