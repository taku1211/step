<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Step;
use App\Models\Substep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class StepController extends Controller
{
    //認証チェック
    public function __construct()
    {
        //index、show、searchは認証なしでも処理を行いたいため除外
        $this->middleware('auth')->except(['index','show','search']);
    }

    //Stepの新規登録
    public function create(Request $request)
    {

        $request->validate([
            'title' =>[ 'required', 'string', 'max:255'],
            'category_main' =>[ 'required', 'string', 'max:255'],
            'category_sub' =>[ 'required', 'string', 'max:255'],
            'content' => ['string', 'max:500','nullable'],
        ]);

        //新規登録するためのSTEPインスタンスの生成
        $step = new Step;

        //stepインスタンスに$requestの値を代入
        //time_aim・step_numberは、サブSTEP登録後に別で登録するため今回は0で登録
        $step->title = $request->title;
        $step->user_id = Auth::user()->id;
        $step->category_main = $request->category_main;
        $step->category_sub = $request->category_sub;
        $step->content = $request->content;
        $step->time_aim = 0;
        $step->step_number = 0;
        $step->save();

        return $step;
    }
    //新規登録したSTEPに合計時間とサブSTEP数を追加登録
    public function update(Request $request)
    {
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
            'stepNumber' => ['required', 'int','max:18446744073709551615','min:0'],
            'stepId' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        //更新するためにIdが$request->stepIdであるカラムを取得
        $step = Step::find($request->stepId);

        $step->time_aim = $request->time;
        $step->step_number = $request->stepNumber;
        $step->save();

        return $step;
    }
    //STEP・サブSTEPの更新処理
    public function edit(Request $request)
    {
        //バリデーション
        $request->validate([
            'title' => [ 'required', 'string', 'max:255'],
            'content' => ['string', 'max:500','nullable'],
            'category_main' =>[ 'required', 'string', 'max:255'],
            'category_sub' =>[ 'required', 'string', 'max:255'],
            'time_aim' => ['required', 'int','max:18446744073709551615','min:0'],
            'step_number' => ['required', 'int','max:18446744073709551615','min:0'],
            'subStepForm.*.title' => [ 'required', 'string', 'max:255'],
            'subStepForm.*.content' => ['string', 'max:500','nullable'],
            'subStepForm.*.time_aim' => ['required', 'int','max:18446744073709551615','min:0'],
            'subStepForm.*.order' => ['required', 'int','max:18446744073709551615','min:0']
        ]);

        //$requestを展開
        $data = $request->all();

        //更新するSTEPをuserIdとcategoryIdで絞って取得
        $userId = Auth::user()->id;
        $categoryId = $request->id;
        $step = Step::where([
            ['id', '=' , $categoryId],
            ['user_id', '=', $userId],
        ])->with(['subSteps'])->first();

        //更新結果を格納する変数を準備
        $returnData = [];

        DB::beginTransaction();
        try{
            //以下、STEP・サブSTEPの更新を行う
            //1. 削除処理
            //削除するサブSTEPのデータ数を取得
            $deleteCount = count($request->deletedSubStep);

            //deleteCountが0もしくはnullでなければ、if内を実行
            if($deleteCount !== 0 || $deleteCount !== null){
                //deleteCountの数分、for文内をループ
                for($i=0;$i<$deleteCount;$i++){
                    //deletedSubStepに格納していた削除予定のサブSTEPのidを代入し、
                    //Substepモデルを使ってデータを消去する
                    $subStepId = $data['deletedSubStep'][$i];
                    $deleteSubStep = Substep::where([
                        ['id', '=' , $subStepId],
                        ['user_id', '=', $userId],
                    ])->delete();
                    //結果を$returnData配列にpushする
                    array_push($returnData, $deleteSubStep);
                }
            }

            //2. STEP（親元）の更新処理を行う
            $step->title = $request->title;
            $step->category_main = $request->category_main;
            $step->category_sub = $request->category_sub;
            $step->content = $request->content;
            $step->time_aim = $request->time_aim;
            $step->step_number = $request->step_number;
            $step->save();

            //結果を$returnData配列にpushする
            array_push($returnData, $step);


            //3. サブSTEPの更新処理をひとつずつ行う
            //更新するサブSTEPのデータ数を取得する
            $subStepCount = count($request->subStepForm);

            //subStepCountが0もしくはnullでなければ、if内を実行
            if($subStepCount !== 0 || $subStepCount !== null){
                //subStepCountの数分for文内をループ
                for($i=0;$i<$subStepCount;$i++){
                    //SubStepモデルのidをキーとして、idが存在すれば更新、なければ新規追加する
                    $subStep = SubStep::upsert([
                        ['id' => $data['subStepForm'][$i]['id'],
                        'title' => $data['subStepForm'][$i]['title'],
                        'time_aim' => $data['subStepForm'][$i]['time_aim'],
                        'content' => $data['subStepForm'][$i]['content'],
                        'step_id' => $data['id'],
                        'order' => $data['subStepForm'][$i]['order'],
                        'user_id' => $userId]
                    ],['id']);

                    //結果を$returnData配列にpushする
                    array_push($returnData, $subStep);
                }
            }

            DB::commit();
            return $returnData;

        //エラーの場合はデータをロールバック
        } catch (\Exception $exception){
            DB::rollback();
            throw $exception;
        }
    }

    //登録したSTEPを削除する
    public function destroy(Request $request){
        $userId = Auth::user()->id;
        $step = Step::where([
            ['id', '=', $request->id],
            ['user_id', '=', $userId]
        ])->delete();
        return $step;
    }

    //登録されているSTEPをページごとに取得する（サブSTEPが0のSTEPは除く）
    public function index()
    {
        $allSteps = Step::where([
            ['step_number','!=',0],
        ])->with(['challengeStep'])->orderBy('id', 'desc')->paginate();

        return $allSteps;
    }

    //検索条件に合致するSTEPをページごとに取得する（サブSTEPが0のSTEPは除く）
    public function search(Request $request)
    {
        //キーワード検索を行う文字について、メタ文字をエスケープする
        $keyword = '%'.addcslashes($request->keyword, '%_\\').'%' ?? "";
        $categoryMain = $request->selectedCategoryMain ?? "";
        $categorySub = $request->selectedCategorySub ?? "";

        //ソートについて、$request->sortの値に応じて変数を作成
        if($request->sort === 'normal'){
            $column = 'id';
            $order = 'desc';
        }else if($request->sort === 'new'){
            $column = 'created_at';
            $order = 'desc';
        }else if($request->sort === 'old'){
            $column = 'created_at';
            $order = 'asc';
        }else if($request->sort === 'firstName'){
            $column = 'title';
            $order = 'asc';
        }else if($request->sort === 'lastName'){
            $column = 'title';
            $order = 'desc';
        }else{
            $column = 'id';
            $order = 'desc';
        }


        if($keyword !== "" && $categoryMain !== "" && $categorySub !== ""){

            //キーワード・メインカテゴリー・サブカテゴリーが選択されている場合
            $searchSteps = Step::where([
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub !== "" ){

            //メインカテゴリー・サブカテゴリーのみが選択されている場合
            $searchSteps = Step::where([
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain === "" && $categorySub !== ""){

            //サブカテゴリーのみが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::where([
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub === ""){

            //メインカテゴリーのみが選択されている場合
            $searchSteps = Step::where([
                ['category_main', '=', $request->selectedCategoryMain],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub !== ""){

            //キーワード・サブカテゴリーが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::where([
                ['title', 'like', $keyword],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain !== "" && $categorySub === ""){

            //キーワード・メインカテゴリーが選択されている場合
            $searchSteps = Step::where([
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub === ""){

            //キーワードが選択されている場合
            $searchSteps = Step::where([
                ['title', 'like', $keyword],
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else{

            //何も選択されていない場合
            $searchSteps = Step::where([
                ['step_number','!=',0],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();
        }
        return $searchSteps;
    }

    //自分が登録したSTEP一覧を取得する（サブSTEPが0のSTEPを含む）
    public function indexMySteps()
    {
        $id = Auth::user()->id;
        $mySteps = Step::where('user_id', $id)->with(['challengeStep'])->orderBy(Step::CREATED_AT, 'desc')->paginate();

        return $mySteps;
    }

    //検索条件に合致する、自分が登録したSTEPをページごとに取得する（サブSTEPが0のSTEPを含む）
    public function searchMySteps(Request $request)
    {
        $userId = Auth::user()->id;
        //キーワード検索を行う文字について、メタ文字をエスケープする
        $keyword = '%'.addcslashes($request->keyword, '%_\\').'%' ?? "";
        $categoryMain = $request->selectedCategoryMain ?? "";
        $categorySub = $request->selectedCategorySub ?? "";

        //ソートについて、$request->sortの値に応じて変数を作成
        if($request->sort === 'normal'){
            $column = 'id';
            $order = 'desc';
        }else if($request->sort === 'new'){
            $column = 'created_at';
            $order = 'desc';
        }else if($request->sort === 'old'){
            $column = 'created_at';
            $order = 'asc';
        }else if($request->sort === 'firstName'){
            $column = 'title';
            $order = 'asc';
        }else if($request->sort === 'lastName'){
            $column = 'title';
            $order = 'desc';
        }else{
            $column = 'id';
            $order = 'desc';
        }


        if($keyword !== "" && $categoryMain !== "" && $categorySub !== ""){

            //キーワード・メインカテゴリー・サブカテゴリーが選択されている場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub !== "" ){

            //メインカテゴリー・サブカテゴリーのみが選択されている場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain === "" && $categorySub !== ""){

            //サブカテゴリーのみが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['category_sub', '=', $request->selectedCategorySub],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub === ""){

            //メインカテゴリーのみが選択されている場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['category_main', '=', $request->selectedCategoryMain],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub !== ""){

            //キーワード・サブカテゴリーが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['title', 'like', $keyword],
                ['category_sub', '=', $request->selectedCategorySub],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain !== "" && $categorySub === ""){

            //キーワード・メインカテゴリーが選択されている場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub === ""){

            //キーワードが選択されている場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
                ['title', 'like', $keyword],
                ])->with(['challengeStep'])->orderBy($column, $order)->paginate();

        }else{

            //何も選択されていない場合
            $searchSteps = Step::where([
                ['user_id', '=', $userId],
            ])->with(['challengeStep'])->orderBy($column, $order)->paginate();
        }
        return $searchSteps;
    }

    //自分が挑戦しているSTEP一覧を取得する（既に削除済のSTEPも含む）
    public function indexMyChallenge()
    {
        $userId = Auth::user()->id;
        //$myChallenge = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query) use ($userId){
        $myChallenge = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query) use ($userId){
            $query->where('user_id', $userId);
        })->orderBy('created_at', 'desc')->paginate();

        return $myChallenge;
    }
    //検索条件に合致するSTEPをページごとに取得する（既に削除済のSTEPも含む）
    public function searchMyChallenge(Request $request)
    {
        //キーワード検索を行う文字について、メタ文字をエスケープする
        $keyword = '%'.addcslashes($request->keyword, '%_\\').'%' ?? "";
        $categoryMain = $request->selectedCategoryMain ?? "";
        $categorySub = $request->selectedCategorySub ?? "";

        //ソートについて、$request->sortの値に応じて変数を作成
        if($request->sort === 'normal'){
            $column = 'id';
            $order = 'desc';
        }else if($request->sort === 'new'){
            $column = 'created_at';
            $order = 'desc';
        }else if($request->sort === 'old'){
            $column = 'created_at';
            $order = 'asc';
        }else if($request->sort === 'firstName'){
            $column = 'title';
            $order = 'asc';
        }else if($request->sort === 'lastName'){
            $column = 'title';
            $order = 'desc';
        }else{
            $column = 'id';
            $order = 'desc';
        }

        if($keyword !== "" && $categoryMain !== "" && $categorySub !== ""){

            //キーワード・メインカテゴリー・サブカテゴリーが選択されている場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub !== "" ){

            //メインカテゴリー・サブカテゴリーのみが選択されている場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['category_main', '=', $request->selectedCategoryMain],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain === "" && $categorySub !== ""){

            //サブカテゴリーのみが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword === ""  && $categoryMain !== "" && $categorySub === ""){

            //メインカテゴリーのみが選択されている場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['category_main', '=', $request->selectedCategoryMain],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub !== ""){

            //キーワード・サブカテゴリーが選択されている場合
            //厳密にはメインカテゴリーを選択しないとサブカテゴリーが選択できないのでありえないが、
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['title', 'like', $keyword],
                ['category_sub', '=', $request->selectedCategorySub],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain !== "" && $categorySub === ""){

            //キーワード・メインカテゴリーが選択されている場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['title', 'like', $keyword],
                ['category_main', '=', $request->selectedCategoryMain],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else if($keyword !== "" && $categoryMain === "" && $categorySub === ""){

            //キーワードが選択されている場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['title', 'like', $keyword],
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();

        }else{

            //何も選択されていない場合
            $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function($query){
                $id = Auth::user()->id;
                $query->where('user_id', $id);
            })->where([
                ['step_number','!=',0],
                ])->orderBy($column, $order)->paginate();
        }
        return $searchSteps;
    }

    //STEP詳細画面の表示
    public function show(string $id)
    {
        $step = Step::withTrashed()->where([
            ['id', '=' , $id],
        ])->with(['substeps','challengeStep','user:id,icon,introduction'])->first();

        return $step  ?? abort(404);
    }
}
