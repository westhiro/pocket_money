<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\StockController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TradingController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\InquiryController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RealEstateController;
use App\Http\Controllers\API\RealEstateTradingController;
use App\Http\Controllers\Auth\AuthController;

// 認証関連API（認証不要）
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ユーザー関連API（認証不要 - user_idベース）
Route::prefix('user')->group(function () {
    Route::get('/assets', [UserController::class, 'getAssets']);
    Route::get('/assets/history', [UserController::class, 'getAssetHistory']);
    Route::get('/stocks', [UserController::class, 'getStocks']);
    Route::post('/stocks/buy', [UserController::class, 'buyStock']);
    Route::post('/stocks/sell', [UserController::class, 'sellStock']);
});

// 投資取引API（認証不要 - user_idベース）
Route::prefix('trading')->group(function () {
    Route::post('/trade', [TradingController::class, 'trade']); // 株式売買
    Route::get('/portfolio', [TradingController::class, 'portfolio']); // ポートフォリオ取得
    Route::get('/history', [TradingController::class, 'history']); // 取引履歴
});

// 認証が必要なAPI（後方互換性のため残す）
Route::middleware('auth:web')->group(function () {
    Route::get('/user-me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// 株式関連API（認証不要）
Route::prefix('stocks')->group(function () {
    Route::get('/', [StockController::class, 'index']); // 株式一覧
    Route::get('/{id}', [StockController::class, 'show']); // 個別株式詳細
    Route::get('/{id}/chart/{period}', [StockController::class, 'chart']); // チャートデータ
    Route::get('/industry/{industryId}', [StockController::class, 'byIndustry']); // 業界別株式
});

// 不動産関連API（認証不要）
Route::prefix('real-estates')->group(function () {
    Route::get('/', [RealEstateController::class, 'index']); // 物件一覧
    Route::get('/current-interest-rate', [RealEstateController::class, 'getCurrentInterestRate']); // 現在の金利
    Route::get('/{id}', [RealEstateController::class, 'show']); // 物件詳細
});

// 不動産取引API（認証不要 - user_idベース）
Route::prefix('real-estate-trading')->group(function () {
    Route::post('/buy', [RealEstateTradingController::class, 'buy']); // 不動産購入
    Route::post('/sell', [RealEstateTradingController::class, 'sell']); // 不動産売却
    Route::get('/portfolio', [RealEstateTradingController::class, 'portfolio']); // ポートフォリオ取得
    Route::get('/history', [RealEstateTradingController::class, 'history']); // 取引履歴
});

// ニュース関連API（認証不要）
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']); // ニュース一覧
    Route::get('/latest', [NewsController::class, 'latest']); // 最新ニュース（ホームページ用）
    Route::get('/events', [NewsController::class, 'eventNews']); // イベント関連ニュース
    Route::get('/{id}', [NewsController::class, 'show']); // 個別ニュース詳細
});

// お知らせ関連API（認証不要 - user_idベース）
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']); // お知らせ一覧
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']); // 未読件数
    Route::get('/{id}', [NotificationController::class, 'show']); // 個別お知らせ
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']); // お知らせを既読にする
});

// お問い合わせ関連API（認証不要 - user_idベース）
Route::prefix('inquiries')->group(function () {
    Route::get('/', [InquiryController::class, 'index']); // お問い合わせ一覧
    Route::get('/{id}', [InquiryController::class, 'show']); // 個別お問い合わせ
    Route::post('/', [InquiryController::class, 'store']); // お問い合わせ作成
});

// プロフィール関連API（認証不要 - user_idベース）
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']); // プロフィール取得
    Route::put('/', [ProfileController::class, 'update']); // プロフィール更新
});

// テスト用API（認証不要）- 開発期間中のみ
Route::prefix('test')->group(function () {
    Route::post('/trade', [TradingController::class, 'tradeTest']); // テスト用取引
    Route::get('/portfolio/{userId}', [TradingController::class, 'portfolioTest']); // テスト用ポートフォリオ
});

// 株価更新トリガー（外部Cron用）
Route::get('/trigger-update', function () {
    try {
        \Log::info('株価更新トリガーが呼ばれました');

        // 株価更新コマンドを実行（forceなし = 1時間に1回のみ更新）
        \Artisan::call('stocks:update-prices');
        $output = \Artisan::output();

        \Log::info('株価更新結果: ' . $output);

        return response()->json([
            'success' => true,
            'message' => $output ?: '株価更新処理を実行しました',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        \Log::error('株価更新エラー: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// CSRFトークン取得エンドポイント
Route::get('/csrf-token', function () {
    try {
        return response()->json(['csrf_token' => csrf_token()]);
    } catch (\Exception $e) {
        \Log::error('CSRF token error: ' . $e->getMessage());
        return response()->json(['error' => 'CSRF token generation failed'], 500);
    }
});

// CORS対応のためのプリフライトリクエスト
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
})->where('any', '.*');