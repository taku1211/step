<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    static $createRecordError = '新規登録に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $systemError = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        try{
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'icon' => null,
                'introduction' => null,
            ]);
            if(empty($user)){
                $createError = new \Exception();
                Log::error($createError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => RegisterController::$createRecordError, 'status' => false], 500);
            }else{
                return $user;
            }
        } catch (\Exception $e) {
            //ログにエラー内容を入力
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => RegisterController::$systemError,'status' => false], 500);
        }
    }
    // RegistersUsers/registerdメソッドのオーバーライド（リダイレクトせずにjson形式で登録したユーザー情報を返却させる
    protected function registered(Request $request, $user)
    {
        return $user;
    }
}
