<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    static $systemError = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    // AuthenticatesUsers/Authenticateメソッドのオーバーライド（リダイレクトせずにjson形式でユーザー情報を返却させる
    protected function authenticated(Request $request, $user)
    {
        //$userが空の場合は、500エラーを返却、空ではない場合はそのまま処理を続行
        if(empty($user)){
            $loginError = new \Exception();
            Log::error($loginError);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => LoginController::$systemError, 'status' => false], 500);
        }else{
            return $user;
        }

    }
    // AuthenticatesUsers/loggedoutメソッドのオーバーライド（リダイレクトせずにjson形式でレスポンスを返却させる
    protected function loggedOut(Request $request)
    {
    // セッションの再生成
    $request->session()->regenerate();

    return response()->json();
    }
}
