<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Challenge;
use App\Models\Substep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;



class ChallengeController extends Controller
{
    static $notFoundRecordError = 'データを取得できませんでした。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $systemError = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $saveError = 'STEPへのチャレンジもしくはクリアしたSTEPの更新が失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $challengeError = '前のサブSTEPをクリアしていないため、このサブSTEPのクリアができませんでした。サブSTEPは前から順番にクリアしてください。';
    static $updateError = 'このサブSTEPはまだクリアしていないため、達成時間を更新できませんでした。';
    //認証チェック
    public function __construct()
    {
        $this->middleware('auth');
    }
    //チャレンジを開始する処理
    public function challenge(Request $request)
    {
        $request->validate([
            'step_id' => ['required', 'int','max:18446744073709551615','min:0'],
            'substep_id' => [ 'int','max:18446744073709551615','min:0'],
        ]);


        //ログインユーザーのIdを取得
        $userId = Auth::user()->id;

        //認証を挟んでいるのでありえないはずだが
        //認証済みのユーザーIdがなかった場合419認証エラーを返却
        if(!$userId){
            return response()->json(['status' => false], 419);
        }

        //$requestで渡ってきたstep_idを持つサブSTEPを取得
        try{
            $subStep = SubStep::where([
                ['step_id','=',$request->step_id],
            ])->orderBy('order','asc')->get();
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError,'status' => false], 500);
        }

        //取得したサブSTEPの数を取得
        $subStepCount = count($subStep);

        //サブSTEPがなかった場合、500エラーを返却
        if(!$subStepCount){
            $recordError = new ModelNotFoundException();
            Log::error($recordError);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError,'status' => false], 404);
        }

        //更新結果を格納する変数を準備
        $returnData = [];

        DB::beginTransaction();

        try{
            $challengeMainStep = new Challenge;

            //DBのchallengesにSTEPに挑戦したことを登録
            $challengeMainStep->user_id = $userId;
            $challengeMainStep->step_id = $request->step_id;
            //STEP自体での挑戦ではsubstep_idはnull
            $challengeMainStep->substep_id = $request->substep_id;
            //まだ挑戦をクリアしていないので、実施時間は0で登録
            $challengeMainStep->time = 0;
            //orderはサブSTEPが何番目かを示すためSTEP自体の挑戦は0で登録
            $challengeMainStep->order = 0;
            $challengeMainStep->challenge_flg = true;
            $result = $challengeMainStep->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                //falseの場合は、500エラーを返却
                $challengeError = new \Exception();
                Log::error($challengeError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
            }

            array_push($returnData, $challengeMainStep);

            //次にサブSTEPひとつずつに対して挑戦したことをDBに登録する
            for($i=0; $i<$subStepCount; $i++){

                $challengeSubStep = new Challenge;
                $challengeSubStep->user_id = $userId;
                $challengeSubStep->step_id = $request->step_id;
                $challengeSubStep->substep_id = $subStep[$i]['id'];
                //まだサブSTEPはクリアしていないので、実施時間は0で登録
                $challengeSubStep->time = 0;
                //orderはサブSTEPの順番を登録
                $challengeSubStep->order = $subStep[$i]['order'];

                //サブSTEPの一番目については、最初にSTEPを挑戦した際にchallenge_flgをtrue(挑戦中状態)に、
                //それ以外についてはfalse（まだ挑戦していない状態）にする
                $challengeSubStep->challenge_flg = ($i === 0) ? true : false;

                $challengeSubStep->order = $subStep[$i]['order'];
                $result = $challengeSubStep->save();

                //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
                if($result){
                    //trueの場合は何も処理せずそのまま後続処理へ
                }else{
                    DB::rollBack();
                    //falseの場合は、500エラーを返却
                    $challengeError = new \Exception();
                    Log::error($challengeError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
                }

                array_push($returnData, $challengeSubStep);
            }
            DB::commit();
            return $returnData;

        }catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }
    }
    //抽選中のSTEPをクリアする処理
    public function clear(Request $request)
    {
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        //今回クリアする予定の挑戦データよりも、前のサブSTEPを全てクリアしているか確認する
        //そのため、クリア予定のサブSTEPに関連する、サブSTEPの挑戦データをすべて取得
        try{
            $allChallenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','<>',0]
            ])->get();
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }
        //取得データが0件の場合は、404エラーを返却
        //取得データが1件の場合（サブSTEPが1つだけ）、何もせず後続処理へ
        //2件以上ある場合は、今回クリアするサブSTEPよりも前のサブSTEPが全てクリア済か確認する
        $subStepCount = $allChallenge->count();

        if($subStepCount === 0){
            $recordError = new ModelNotFoundException();
            Log::error($recordError);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError,'status' => false], 404);
        }else if($subStepCount === 1){
            //そのまま後続処理へ
        }else{
            //今回更新するサブSTEPのひとつ前のサブSTEPまでがクリア済かを確認
            for($i=0; $i<$request->order - 1; $i++){ //例：今回更新するサブSTEPが3つ目の場合は、1つ目2つ目のサブSTEPまで確認
                //クリア済みではない場合、500エラーを返却
                if($allChallenge[$i]['clear_flg'] === 0){
                    $recordError = new \Exception();
                    Log::error($recordError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ChallengeController::$challengeError,'status' => false], 500);
                }
            }
        }

        //$requestで渡ってきたstep_id、substep_idで、かつログイン中のユーザーの挑戦データを取得
        try{
            $challenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['substep_id','=',$request->id],
                ['step_id','=',$request->mainId],
            ])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }

        //同様に、クリアする次のサブSTEPの挑戦データを取得
        //取得データは0件でも問題ない（次のサブSTEPがないだけ）ので、first()を使用
        try{
            $nextChallenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','=',$request->order+1]
            ])->first();
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }

        $returnData = [];
        //クリアする次のサブSTEPがあるかどうかを真偽値で区別
        //$nextChallengeがnullの場合は、クリアするサブSTEPが最後のサブSTEPとなる
        $boolenNextChallenge = ($nextChallenge !== null) ? 1 : 0;

        DB::beginTransaction();
        try{
            //クリアするサブSTEPの挑戦データの時間・clear_flgを更新
            $challenge->clear_flg = true;
            $challenge->time = $request->time;
            $result = $challenge->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                //falseの場合は、500エラーを返却
                $challengeError = new \Exception();
                Log::error($challengeError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
            }

            array_push($returnData, $challenge);

            //クリアしたサブSTEPの次のサブSTEPがある場合
            if($boolenNextChallenge === 1){
                //次に挑戦するサブSTEPの$challenge_flgをtrueにする
                $nextChallenge->challenge_flg = true;
                $result = $nextChallenge->save();

                //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
                if($result){
                    //trueの場合は何も処理せずそのまま後続処理へ
                }else{
                    DB::rollBack();
                    //falseの場合は、500エラーを返却
                    $challengeError = new \Exception();
                    Log::error($challengeError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
                }

                array_push($returnData, $nextChallenge);

                //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
                $mainChallenge = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','=',0]
                ])->firstOrFail();

                //$mainCHallengeに紐づく全てのサブSTEPの挑戦データを取得し、実施時間を合計する
                $timeSum = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','<>',0]
                ])->sum('time');

                //取得した実施時間の合計を親元のSTEPの挑戦データに登録する
                $mainChallenge->time = $timeSum;
                $result = $mainChallenge->save();

                //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
                if($result){
                    //trueの場合は何も処理せずそのまま後続処理へ
                }else{
                    DB::rollBack();
                    //falseの場合は、500エラーを返却
                    $challengeError = new \Exception();
                    Log::error($challengeError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
                }

                array_push($returnData, $mainChallenge);

            //クリアするサブSTEPが、親元のSTEPに登録されている最後のサブSTEPの場合、
            }else if($boolenNextChallenge === 0){

                //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
                $mainChallenge = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','=',0]
                ])->firstOrFail();

                //$mainCHallengeに紐づく全てのサブSTEPの挑戦データを取得し、実施時間を合計する
                $timeSum = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','<>',0]
                ])->sum('time');

                //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）のclear_flgをtrueにする
                $mainChallenge->clear_flg = true;
                //取得した実施時間の合計を親元のSTEPの挑戦データに登録する
                $mainChallenge->time = $timeSum;

                $result = $mainChallenge->save();

                //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
                if($result){
                    //trueの場合は何も処理せずそのまま後続処理へ
                }else{
                    DB::rollBack();
                    //falseの場合は、500エラーを返却
                    $challengeError = new \Exception();
                    Log::error($challengeError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
                }

                array_push($returnData, $mainChallenge);
            }else{
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
            }
            DB::commit();
            return $returnData;
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }
    }
    //一度クリアしたサブSTEPの実施時間を更新する
    public function update(Request $request){
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        try{
            //更新するサブSTEPの挑戦データを取得
            $challenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['substep_id','=',$request->id],
                ['step_id','=',$request->mainId],
            ])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }

        //取得した挑戦データが未クリアの場合は、更新処理ではないため500エラーを返却
        if($challenge->clear_flg === 0){
            $recordError = new \Exception();
            Log::error($recordError);
            return response()->json(['message' => ChallengeController::$updateError, 'status' => false], 500);
        }

        $returnData = [];

        DB::beginTransaction();
        try{

            //実施時間を更新
            $challenge->time = $request->time;
            $result = $challenge->save();
            array_push($returnData,$challenge);

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                //falseの場合は、500エラーを返却
                $challengeError = new \Exception();
                Log::error($challengeError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
            }

            //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
            $mainChallenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','=',0],
            ])->firstorFail();

            //$mainCHallengeに紐づく全てのサブSTEPの挑戦データを取得し、実施時間を合計する
            $timeSum = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','<>',0],
            ])->sum('time');

            $mainChallenge->time = $timeSum;
            $result = $mainChallenge->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                //falseの場合は、500エラーを返却
                $challengeError = new \Exception();
                Log::error($challengeError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => ChallengeController::$saveError, 'status' => false], 500);
            }

            array_push($returnData,$mainChallenge);

            DB::commit();
            return $returnData;
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => ChallengeController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => ChallengeController::$systemError, 'status' => false], 500);
        }
    }
}
