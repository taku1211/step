<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
    static $systemError = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failUpdateRecordError = 'パスワードの更新に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';

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
        try{
            $reset_password_status = Password::reset($credentials, function ($user, $password) {
                $user->password = bcrypt($password);
                $result = $user->save();

                if($result){
                    //save()の戻り値がtrueの場合は、保存に成功しているのでそのまま後続処理
                }else{
                    $resetError = new \Exception();
                    Log::error($resetError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ResetPasswordController::$failUpdateRecordError, 'status' => false], 500);
                }
            });
        } catch (\Exception $e) {
            //ログにエラー内容を入力
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ResetPasswordController::$systemError, 'status' => false], 500);
        }

        //token有効期限切れの場合
        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(['message' => 'URLの有効期限が切れています。再度、パスワード再発行メールを送信してください。', 'status' => false], 401);
        }
        //正常に再設定が完了したら200レスポンスを返却
        return response()->json(['message' => 'パスワードの再設定が完了しました。', 'status' => true], 200);
    }

    //tokenの有効期限を確認
    public function checkToken(Request $request)
    {
        $token = $request->token;

        try{
            //$tokenに格納したハッシュ化前の値をハッシュ化し、
            //password_resetsに保存されている有効期限内のハッシュ済の値と全件比較
            $exits = DB::query()->select('token')
            ->from('password_resets')->where('created_at', '>=', now()->subMinutes(config('auth.passwords.users.expire')))
            ->get()
            ->filter(fn ($record) => Hash::check($token, $record->token))
            ->isNotEmpty();

        } catch (\Exception $e) {
            //ログにエラー内容を入力
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ResetPasswordController::$systemError, 'status' => false], 500);
        }

        //一致する値がない場合は、401エラーを返却
        if(!$exits){
            return response()->json(['message' => 'URLの有効期限が切れています。再度、パスワード再発行メールを送信してください。', 'status' => false], 401);
        }
        //一致する値がある場合は、200OKを返却
        return response()->json(['status' => true], 200);
    }
}
