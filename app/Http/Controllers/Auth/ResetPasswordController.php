<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    //ログインしていない状態かどうかを確認
    public function __construct()
    {
        $this->middleware('guest');
    }

    //パスワードリセット処理
    public function resetPassword()
    {

        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        //パスワードの再設定
        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        //token有効期限切れの場合
        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(['message' => 'URLの有効期限が切れています。再度、パスワード再発行メールを送信してください。', 'status' => false], 401);
        }
        //正常に再設定が完了したら200レスポンスを返却
        return response()->json(['message' => 'パスワードの再設定が完了しました。', 'status' => true], 200);
    }
}
