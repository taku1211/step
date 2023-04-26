<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;



class UserController extends Controller
{
    //認証チェック
    public function __construct()
    {
        $this->middleware('auth');
    }

    //User情報更新処理
    public function update(Request $request)
    {

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->whereNot('email', Auth::user()->email)],
            'introduction' => ['string', 'max:500','nullable'],
            'icon' => ['nullable','file', 'max:10240', 'mimes:jpg,jpeg,png,gif'],
        ]);

        $userId = Auth::user()->id;

        $user = User::find($userId);
        $userIcon = $user->icon;

        //画像ファイルが存在する場合（画像の新規登録・もしくは更新）
        if($request->icon !== null){
            //保存するアイコンのファイル名を作成
            //1.ランダムの12文字を生成
            //2.$request->iconの拡張子を取得
            //3.1と2を結合する
            //4.画像データを取得する
            //5.app/storageに画像データを保存する
            $randomName = Str::random(12);
            $extension = $request->icon->extension();
            $getFileName = $randomName.'.'.$extension;
            $img = $request->file('icon');
            Storage::putFileAs('public',$img,$getFileName);
        }

        DB::beginTransaction();

        try{
            $user->email = $request->email;

            //$request->introductionがnull（紹介文に何も入力していない場合）、
            //nullという文字列でコントローラーにdataが渡ってきてしまうため、
            //if文で文字列のnullが渡ってきたかどうかを確認し、
            //文字列の場合は、文字列ではないnullを登録するようにする
            if($request->introduction !== 'null'){
                $user->introduction = $request->introduction;
            }else{
                $user->introduction = null;
            }

            //画像ファイルが存在する場合
            if($request->icon !== null){
                //ファイル名をDBに登録
                $user->icon = $getFileName;

            //画像データがnullでかつ、アイコンのファイル名は存在する場合
            }else if($request->icon === null && $request->iconName === $userIcon){
                //既にDB上で登録されているアイコンから変更がないため、処理なし


            //画像データもアイコンのファイル名もnullの場合
            //つまり、アイコンが登録されておらず、今回の更新でも登録しない場合
            }else{
                //ファイル名をnullで登録
                $user->icon = null;
            }
            $user->save();

            DB::commit();
            return $user;

        } catch (\Exception $exception){
            DB::rollback();
            if($request->icon !== null){
                Storage::delete($getFileName);
            }
            throw $exception;
        }
    }
}
