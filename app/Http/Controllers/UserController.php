<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class UserController extends Controller
{
    static $notFoundRecordErrorMessage = 'データを取得できませんでした。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $systemErrorMessage = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failSaveRecordError = 'ユーザー情報の更新に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';


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


        //ログインユーザーのIdを取得
        $userId = Auth::user()->id;

        //認証を挟んでいるのでありえないはずだが
        //認証済みのユーザーIdがなかった場合419認証エラーを返却
        if(!$userId){
            return response()->json(['status' => false], 419);
        }

        try{
            $user = User::findorFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => UserController::$notFoundRecordErrorMessage, 'status' => false], 404);
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => UserController::$systemErrorMessage, 'status' => false], 500);
        }
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
            $user->introduction = ($request->introduction !== 'null') ? $request->introduction : null;

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
            $result = $user->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                if($request->icon !== null){
                    Storage::delete($getFileName);
                }
                //falseの場合は、500エラーを返却
                $saveError = new \Exception();
                Log::error($saveError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => UserController::$failSaveRecordError, 'status' => false], 500);
            }

            DB::commit();
            return $user;

        } catch (\Exception $e){
            DB::rollback();
            if($request->icon !== null){
                Storage::delete($getFileName);
            }
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => UserController::$systemErrorMessage,'status' => false], 500);

        }
    }
}
