# Partsone バックエンドリポジトリ

## 概要
Yahooニュースのような、ユーザーが記事にアクセスし、コメントを行えるAPIを作成

## 仕様の詳細
1. 認証済みユーザーは記事を投稿可能
2. 認証済みユーザーは記事に対してコメントを作成可能
3. 全てのユーザーは記事を選択することで記事の内容とコメントを閲覧可能  
   a) 記事一覧は投稿日時によって新しい順にソート
4. 認証済みユーザーは自らが作成した記事とコメントを編集・削除可能

## 概要
以下の機能を開発:

1. 認証とユーザー管理機能の実装
2. 記事投稿機能の実装（CRUD）
3. コメント機能の実装（CRUD）
4. APIのセキュリティ管理（認証トークンによるアクセス制御）

