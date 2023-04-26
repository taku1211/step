<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Challenge;
use App\Models\Substep;
use Illuminate\Support\Facades\DB;



class ChallengeController extends Controller
{
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

        //$requestで渡ってきたstep_idを持つサブSTEPを取得
        $subStep = SubStep::where([
            ['step_id','=',$request->step_id],
        ])->orderBy('order','asc')->get();

        //取得したサブSTEPの数を取得
        $subStepCount = count($subStep);

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
            //orderはサブSTEPが何番目かを示すためSTEP事態の挑戦は0で登録
            $challengeMainStep->order = 0;
            $challengeMainStep->challenge_flg = true;
            $challengeMainStep->save();

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

                if($i === 0){
                    //サブSTEPの一番目については、最初にSTEPを挑戦した際に
                    //challenge_flgをtrueで登録し、挑戦中の状態にする
                    $challengeSubStep->challenge_flg = true;
                }else{
                    //それ以外については、まだ挑戦していない状態にする
                    $challengeSubStep->challenge_flg = false;
                }

                $challengeSubStep->save();

                array_push($returnData, $challengeSubStep);
            }
            DB::commit();
            return $returnData;

        }catch (\Exception $exception){
            DB::rollback();
            throw $exception;
        }
    }
    //指定されたSTEPの挑戦データをすべて取得
    public function index(string $id)
    {
        $challenge = Challenge::where('step_id',$id)->get();

        return $challenge  ?? abort(404);
    }
    //抽選中のSTEPをクリアする処理
    public function clear(Request $request)
    {
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        //$requestで渡ってきたstep_id、substep_idで、かつログイン中のユーザーの挑戦データを取得
        $challenge = Challenge::where([
            ['user_id','=',Auth::user()->id],
            ['substep_id','=',$request->id],
            ['step_id','=',$request->mainId],
        ])->first();

        //同様に、クリアする次のサブSTEPの挑戦データを取得
        $nextChallenge = Challenge::where([
            ['user_id','=',Auth::user()->id],
            ['step_id','=',$request->mainId],
            ['order','=',$request->order+1]
        ])->first();

        $returnData = [];
        //クリアする次のサブSTEPがあるかどうかを真偽値で区別
        //$nextChallengeがnullの場合は、クリアするサブSTEPが最後のサブSTEPとなる
        $boolenNextChallenge = ($nextChallenge !== null) ? 1 : 0;

        DB::beginTransaction();
        try{
            //クリアするサブSTEPの挑戦データの時間・clear_flgを更新
            $challenge->clear_flg = true;
            $challenge->time = $request->time;
            $challenge->save();
            array_push($returnData, $challenge);

            //クリアしたサブSTEPの次のサブSTEPがある場合
            if($boolenNextChallenge === 1){
                //次に挑戦するサブSTEPの$challenge_flgをtrueにする
                $nextChallenge->challenge_flg = true;
                $nextChallenge->save();
                array_push($returnData, $nextChallenge);

                //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
                $mainChallenge = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','=',0]
                ])->first();

                //$mainCHallengeに紐づく全てのサブSTEPの挑戦データを取得し、実施時間を合計する
                $timeSum = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','<>',0]
                ])->sum('time');

                //取得した実施時間の合計を親元のSTEPの挑戦データに登録する
                $mainChallenge->time = $timeSum;
                $mainChallenge->save();

                array_push($returnData, $mainChallenge);

            //クリアするサブSTEPが、親元のSTEPに登録されている最後のサブSTEPの場合、
            }else if($boolenNextChallenge === 0){

                //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
                $mainChallenge = Challenge::where([
                    ['user_id','=',Auth::user()->id],
                    ['step_id','=',$request->mainId],
                    ['order','=',0]
                ])->first();

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

                $mainChallenge->save();
                array_push($returnData, $mainChallenge);
            }else{
                return abort(500);
            }
            DB::commit();
            return $returnData;
        }catch (\Exception $exception){
            DB::rollback();
            throw $exception;
        }
    }
    //一度クリアしたサブSTEPの実施時間を更新する
    public function update(Request $request){
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        //更新するサブSTEPの挑戦データを取得
        $challenge = Challenge::where([
            ['user_id','=',Auth::user()->id],
            ['substep_id','=',$request->id],
            ['step_id','=',$request->mainId],
        ])->first();

        $returnData = [];

        DB::beginTransaction();
        try{

            //実施時間を更新
            $challenge->time = $request->time;
            $challenge->save();
            array_push($returnData,$challenge);

            //サブSTEPの親元のSTEPの挑戦データ（orderが0のもの）を取得する
            $mainChallenge = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','=',0],
            ])->first();

            //$mainCHallengeに紐づく全てのサブSTEPの挑戦データを取得し、実施時間を合計する
            $timeSum = Challenge::where([
                ['user_id','=',Auth::user()->id],
                ['step_id','=',$request->mainId],
                ['order','<>',0],
            ])->sum('time');

            $mainChallenge->time = $timeSum;
            $mainChallenge->save();
            array_push($returnData,$mainChallenge);

            DB::commit();
            return $returnData;
        }catch (\Exception $exception){
            DB::rollback();
            throw $exception;
        }
    }
}
