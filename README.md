# Hacklog

Hacklog は、案件（Issue）を軸にプロジェクトを管理する Laravel 製の Web アプリケーションです。案件ごとに WBS・ガントチャート・カンバン・バグ管理・機能要望・Wiki・ファイル・リポジトリをまとめて扱えます（Backlog 風の構成です）。

## 主な機能

| 機能 | 内容 |
| --- | --- |
| ダッシュボード | アーカイブされていない案件の一覧と全体進捗、最新のお知らせ 8 件 |
| 案件 | 作成・編集・コメント・ウォッチ／スター、アーカイブとその解除、ステータスでの絞り込み。キー（`ISSUE-n`）は自動で採番されます |
| WBS | 階層付きの作業分解（`1`、`1.1` …）、担当者・成果物・進捗・期間の管理 |
| ガントチャート | ステータス・担当者・期間で絞り込み可能。バーをドラッグすると日程を変更でき、親タスクを移動すると子タスクも同じ日数だけ動きます |
| カンバン | WBS のタスクをステータス別の列で表示し、ステータスを変更できます |
| バグ管理 | 重大度・優先度・ステータス、再現手順・期待する動作・実際の動作・環境情報 |
| 機能要望 | ユーザーストーリー・優先度・ステータス、1 人 1 票の投票（もう一度押すと取り消し） |
| Wiki | Markdown（GitHub 形式）と Mermaid 図に対応。階層番号順に並び、`.md` でダウンロードできます |
| ファイル／リポジトリ | ファイルの情報（名前・カテゴリ・サイズ・パス・説明）とリポジトリ URL を登録します。ファイル本体はアップロードしません |
| ユーザー管理 | 管理者だけがユーザーの作成・編集・削除と権限の設定を行えます |

## 技術スタック

- **バックエンド**: PHP 8.3 以上、Laravel 10、Laravel Sanctum（API トークン）、Laravel Breeze（認証画面）
- **フロントエンド**: Blade テンプレート、Vite 5（`laravel-vite-plugin`）
  - スタイルは各レイアウトの `<style>` に直接書いています。Tailwind などの CSS フレームワークは使っていません
  - Mermaid 11 は Wiki ページを開いたときに jsDelivr の CDN から読み込みます
  - フォントは Bunny Fonts の Figtree を使っています
- **データベース**: MySQL（初期値）。Laravel が対応する DB なら設定を変えるだけで使えます
- **開発ツール**: PHPUnit 10、Laravel Pint、Laravel Sail、Playwright（`devDependencies` に入っています）

## ディレクトリ構成（主なもの）

```
app/
  Http/Controllers/   案件・WBS・バグ・要望・Wiki・ファイル・リポジトリ・ユーザーの各コントローラ
  Models/             Issue, WorkPackage, Bug, FeatureRequest, WikiPage, ProjectFile, Repository, ...
  Policies/           IssuePolicy（案件の権限）
  Services/           LeadParserService（Claude API で案件募集テキストを構造化する）
config/lead_parser.php  LeadParserService の設定
database/migrations/    テーブル定義
database/seeders/       デモデータ
resources/views/        Blade 画面
routes/web.php          画面のルーティング
```

## データモデル

すべての機能は案件（`issues`）にぶら下がる形になっています。

```
users
issues ─┬─ issue_comments
        ├─ milestones
        ├─ work_packages（parent_id による親子構造）
        ├─ bugs
        ├─ feature_requests ── feature_request_votes
        ├─ wiki_pages
        ├─ project_files
        ├─ repositories
        └─ notification_items
```

主なステータスは次のとおりです。

| 対象 | 値 |
| --- | --- |
| 案件・WBS | `not_started` / `in_progress` / `done` / `blocked` |
| 優先度 | `critical` / `high` / `normal` / `low` |
| バグ | `open` / `investigating` / `fixed` / `verified` / `closed`（重大度は `critical` / `high` / `medium` / `low`） |
| 機能要望 | `proposed` / `reviewing` / `accepted` / `rejected` / `in_progress` / `done` |

WBS の親タスクの進捗と期間は、子タスクから自動で計算されます。案件全体の進捗は、最上位の WBS タスクの進捗の平均です。

## 権限

| 操作 | 管理者（`admin`） | 一般（`general`） |
| --- | :---: | :---: |
| 案件・関連データの閲覧と編集 | ○ | ○ |
| 案件の新規作成 | ○ | × |
| ユーザー管理 | ○ | × |

画面からの新規登録はできません。ユーザーは管理者が「ユーザー管理」で作成します。

## セットアップ

### 必要なもの

- PHP 8.3 以上と Composer
- Node.js と npm
- MySQL（または Laravel が対応するほかの DB）

### 手順

```bash
git clone https://github.com/kuma0158/Hacklog.git
cd Hacklog

composer install
npm install

cp .env.example .env
php artisan key:generate
```

`.env` の DB 設定（`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD` など）を環境に合わせて書き換えてから、次を実行します。

```bash
php artisan migrate --seed   # テーブル作成とデモデータの投入
npm run build                # 開発中は npm run dev
php artisan serve            # http://localhost:8000
```

### デモアカウント

`--seed` で次のユーザーとデモ案件 `ISSUE-1` が作られます。パスワードはすべて `password` です。**本番環境では必ず変更してください。**

| メールアドレス | 名前 | 権限 |
| --- | --- | --- |
| admin@example.com | 管理者 | 管理者 |
| sato@example.com | 佐藤 開発 | 一般 |
| tanaka@example.com | 田中 PM | 一般 |
| suzuki@example.com | 鈴木 QA | 一般 |

### 任意の設定（案件テキストの自動解析）

`LeadParserService` は、取引先から届いた案件募集テキストを Claude API で JSON（案件名・必須スキル・単価・場所など）に変換します。使うときは `.env` に次を追加します。

```
ANTHROPIC_API_KEY=your-api-key
ANTHROPIC_MODEL=claude-sonnet-4-6   # 省略時の値
ANTHROPIC_MAX_TOKENS=4096
ANTHROPIC_TIMEOUT=60
```

> 注: `LeadParseController` はまだ `routes/web.php` に登録されていないため、画面からは使えません。

## 使い方

1. **ログイン**: 管理者アカウントでログインすると、ダッシュボードに案件の一覧と最新のお知らせが表示されます。
2. **ユーザーを追加する**: ナビゲーションの「ユーザー管理」から、メンバーを作成して権限を選びます。
3. **案件を作る**: ダッシュボードの「案件追加」または案件一覧の「+ 案件追加」から、件名・ステータス・優先度・担当者・期間を入力します（管理者のみ）。
4. **WBS を組む**: 案件の画面で「WBS」を開き、タスクを登録します。親タスクを指定すると階層になります。
5. **進捗を管理する**:
   - ガントチャートでは、バーをドラッグして日程を変えられます。
   - カンバンでは、各カードのステータスを切り替えられます。
6. **バグと要望を記録する**: 案件の画面の「バグ」「機能要望」から登録します。機能要望には投票できます。
7. **ドキュメントを残す**: 「Wiki」に Markdown で書きます。` ```mermaid ` のコードブロックは図として表示され、各ページは `.md` でダウンロードできます。
8. **終わった案件を片付ける**: 案件をアーカイブすると一覧とダッシュボードに表示されなくなります。案件一覧の「アーカイブ済みを表示」から元に戻せます。

## 開発コマンド

```bash
php artisan test       # PHPUnit
./vendor/bin/pint      # コード整形
npm run dev            # Vite 開発サーバー
```

## ライセンス

MIT
