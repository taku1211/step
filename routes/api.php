<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//=======================================================
//認証関連
//=======================================================

//ユーザー登録
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');
//ログイン
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
//ログアウト
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
//認証チェック
Route::post('/user', function(){return Auth::user();})->name('user');
//ユーザー情報更新
Route::post('/updateUser', [App\Http\Controllers\UserController::class, 'update'])->name('updateUser');
//パスワードリセットメール送信
Route::post('/password/request', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('sendResetLinkEmail');
//パスワード再設定
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'resetPassword'])->name('resetPassword');
Route::post('/checkToken', [App\Http\Controllers\Auth\ResetPasswordController::class, 'checkToken'])->name('checkToken');

//セッションに保存されているtokenリセット
Route::get('/token/refresh', function (Illuminate\Http\Request $request) {
    $request->session()->regenerateToken();
    return response()->json();
});
//ミドルウェアの認証
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//=======================================================
//STEP関連
//=======================================================

//登録・更新・削除

//新しいSTEPを登録
Route::post('/new', [App\Http\Controllers\StepController::class, 'create'])->name('create');
//新しいサブSTEPを登録
Route::post('/newSub', [App\Http\Controllers\SubstepController::class, 'create'])->name('createSub');
//登録したSTEPにサブステップ数と合計目標時間を追加
Route::post('/update', [App\Http\Controllers\StepController::class, 'update'])->name('update');
//登録したSTEPの更新
Route::post('/edit',[App\Http\Controllers\StepController::class, 'edit'])->name('edit');
//登録したSTEPの削除
Route::post('/destroy',[App\Http\Controllers\StepController::class, 'destroy'])->name('destroy');

//詳細取得

//STEPの詳細を取得し表示
Route::get('/steps/{id}',[App\Http\Controllers\StepController::class, 'show'])->name('show');
//STEPに登録されているサブSTEPを取得
Route::get('/substeps',[App\Http\Controllers\SubstepController::class, 'index'])->name('indexsubSteps');

//一覧取得

//STEPの一覧を取得
Route::get('/steps', [App\Http\Controllers\StepController::class, 'index'])->name('index');
//検索条件のSTEPの一覧を取得
Route::post('/search', [App\Http\Controllers\StepController::class, 'search'])->name('search');

//自分が登録したSTEPの一覧を取得
Route::get('/mysteps', [App\Http\Controllers\StepController::class, 'indexMySteps'])->name('indexMySteps');
//検索条件の自分が登録したSTEPの一覧を取得
Route::post('/mySearch', [App\Http\Controllers\StepController::class, 'SearchMySteps'])->name('searchMySteps');

//自分が挑戦したSTEPの一覧を取得
Route::get('/myChallenge', [App\Http\Controllers\StepController::class, 'indexMyChallenge'])->name('indexMyChallenge');
//自分が挑戦した検索条件のSTEPを取得
Route::post('/myChallengeSearch', [App\Http\Controllers\StepController::class, 'SearchMyChallenge'])->name('searchMyChallenge');


//=======================================================
//STEPへの挑戦関連
//=======================================================

//挑戦するSTEPを登録する
Route::post('/challenge',[App\Http\Controllers\ChallengeController::class, 'challenge'])->name('challenge');
//サブSTEPをクリアする
Route::post('/clear',[App\Http\Controllers\ChallengeController::class, 'clear'])->name('clear');
//クリア済のサブSTEPの時間を更新する
Route::post('/updateClear',[App\Http\Controllers\ChallengeController::class, 'update'])->name('updateClear');




