<?php

namespace App\Http\Controllers;

use App\Models\Substep;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SubstepController extends Controller
{
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
        //親のSTEPを登録完了後、サブSTEPのcreate処理が行われるので、
        //取得したログインユーザーのIdで、最も作成日時が新しいSTEPを取得
        $stepMain = Step::where('user_id',$userId)->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->first();

        $stepMainId = $stepMain->id;

        //$requestで渡ってきたサブSTEPの配列個数（登録するサブSTEPの数）を取得
        $requestLength =  count($request->all());
        //$requestを展開
        $data = $request->all();
        $returnData = [];

        //登録するサブSTEPの数分、for文で登録を行う
        for($i=0; $i<$requestLength;$i++){
            $subStep = new Substep();

            $subStep->title = $data[$i]['subTitle'];
            $subStep->user_id = $userId;
            $subStep->step_id = $stepMainId;
            $subStep->content = $data[$i]['subContent'];
            $subStep->time_aim = $data[$i]['subTime'];
            $subStep->order = $data[$i]['order'];
            $subStep->save();

            array_push($returnData,$subStep);
        }
        return $returnData;
    }

    //親のSTEPに紐づく全てのサブSTEPを取得
    public function index()
    {
        $subStep = Substep::with(['step'])->get();

        return $subStep  ?? abort(404);

    }
}
