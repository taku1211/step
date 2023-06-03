<?php

namespace App\Http\Controllers;

use App\Models\Substep;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class SubstepController extends Controller
{
    static $notFoundRecordErrorMessage = 'データを取得できませんでした。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $systemErrorMessage = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failSaveRecordError = 'サブSTEPの保存に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';


    //認証チェック
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }

    //サブSTEPの新規登録
    public function create(Request $request)
    {
        $request->validate([
            '*.subTitle' => [ 'required', 'string', 'max:255'],
            '*.subContent' => ['string', 'max:500','nullable'],
            '*.subTime' => ['required', 'int','max:18446744073709551615','min:0'],
            '*.order' => ['required', 'int','max:18446744073709551615','min:0']
        ]);

        $userId = Auth::user()->id;

        //認証を挟んでいるのでありえないはずだが
        //認証済みのユーザーIdがなかった場合419認証エラーを返却
        if(!$userId){
            return response()->json(['status' => false], 419);
        }

        //親のSTEPを登録完了後、サブSTEPのcreate処理が行われるので、
        //取得したログインユーザーのIdで、最も作成日時が新しいSTEPを取得
        try{
            $stepMain = Step::where('user_id',$userId)->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => SubstepController::$notFoundRecordErrorMessage, 'status' => false], 404);
        } catch (\Exception $e) {
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => SubstepController::$systemErrorMessage, 'status' => false], 500);
        }

        $stepMainId = $stepMain->id;

        //$stepMainIdが取得できなかった場合、後続処理ができないため404エラーを返却
        if(!$stepMainId){
            $recordError = new ModelNotFoundException();
            Log::error($recordError);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => SubstepController::$notFoundRecordErrorMessage,'status' => false], 404);
        }

        //$requestで渡ってきたサブSTEPの配列個数（登録するサブSTEPの数）を取得
        $requestLength =  count($request->all());

        //$requestLengthが0の場合、後続処理ができないので500エラーを返却
        if(!$requestLength){
            $dataError = new \Exception();
            Log::error($dataError);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => SubstepController::$systemErrorMessage,'status' => false], 500);
        }

        //$requestを展開
        $data = $request->all();
        $returnData = [];

        DB::beginTransaction();
        try{
            //登録するサブSTEPの数分、for文で登録を行う
            for($i=0; $i<$requestLength;$i++){
                $subStep = new Substep();

                $subStep->title = $data[$i]['subTitle'];
                $subStep->user_id = $userId;
                $subStep->step_id = $stepMainId;
                $subStep->content = $data[$i]['subContent'];
                $subStep->time_aim = $data[$i]['subTime'];
                $subStep->order = $data[$i]['order'];
                $result = $subStep->save();

                //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
                if($result){
                    //trueの場合は何も処理せずそのまま後続処理へ
                }else{
                    DB::rollBack();
                    //falseの場合は、500エラーを返却
                    $saveError = new \Exception();
                    Log::error($saveError);
                    // フロントに異常を通知するため500エラーを返却
                    return response()->json(['message' => SubStepController::$failSaveRecordError, 'status' => false], 500);
                }

                array_push($returnData,$subStep);
            }
            DB::commit();
            return $returnData;

        }catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => SubstepController::$systemErrorMessage, 'status' => false], 500);
        }
    }

    //親のSTEPに紐づく全てのサブSTEPを取得
    public function index()
    {
        try{
            $subStep = Substep::with(['step'])->get();
            //サブSTEPはデータ数が0の場合もありうるので、取得したデータはそのまま返却して問題なし
            //取得自体が失敗した場合は例外処理へ
            return $subStep;

        }catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => SubstepController::$systemErrorMessage, 'status' => false], 500);
        }

    }
}
