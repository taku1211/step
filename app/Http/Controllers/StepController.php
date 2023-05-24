<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Step;
use App\Models\Substep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class StepController extends Controller
{
    static $notFoundRecordError = 'データを取得できませんでした。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failDeleteRecordError = 'STEPまたはサブSTEPの削除に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failUpdateRecordError = 'STEPの更新に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $failSaveRecordError = 'STEPの保存に失敗しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';
    static $systemError = '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。';

    //認証チェック
    public function __construct()
    {
        //index、show、searchは認証なしでも処理を行いたいため除外
        $this->middleware('auth')->except(['index','show','search']);
    }

    //Stepの新規登録
    public function create(Request $request)
    {
        //カテゴリーを未選択の場合は、カテゴリーが"null"という文字列で渡ってくるため、
        //"null"の場合はnullに変換し、requiredのバリデーションに引っかかるように処理する
        if($request->category_main === "null"){
            $request->merge(['category_main' => null]);
        }
        if($request->category_sub === "null"){
            $request->merge(['category_sub' => null]);
        }

        $request->validate([
            'title' =>[ 'required', 'string', 'max:255'],
            'category_main' =>[ 'required', 'int'],
            'category_sub' =>[ 'required', 'int'],
            'content' => ['string', 'max:500','nullable'],
            'image' => ['nullable','file', 'max:10240', 'mimes:jpg,jpeg,png,gif'],
        ]);

        //画像ファイルが存在する場合（画像の新規登録・もしくは更新）
        if($request->image !== null){
            //保存するアイコンのファイル名を作成
            //1.ランダムの12文字を生成
            //2.$request->iconの拡張子を取得
            //3.1と2を結合する
            //4.画像データを取得する
            //5.app/storageに画像データを保存する
            $randomName = Str::random(12);
            $extension = $request->image->extension();
            $getFileName = $randomName.'.'.$extension;
            $img = $request->file('image');
            Storage::putFileAs('public',$img,$getFileName);
        }

        //新規登録するためのSTEPインスタンスの生成
        $step = new Step;

        DB::beginTransaction();

        try{
            //stepインスタンスに$requestの値を代入
            //time_aim・step_numberは、サブSTEP登録後に別で登録するため今回は0で登録
            $step->title = $request->title;
            $step->user_id = Auth::user()->id;
            $step->category_main = $request->category_main;
            $step->category_sub = $request->category_sub;

            //$request->contentがnull（概要に何も入力していない場合）、
            //nullという文字列でコントローラーにdataが渡ってきてしまうため、
            //if文で文字列のnullが渡ってきたかどうかを確認し、
            //文字列の場合は、文字列ではないnullを登録するようにする
            $step->content = ($request->content !== 'null') ? $request->content : null;
            $step->image_path = ($request->image !== null) ? $getFileName : null;
            $step->time_aim = 0;
            $step->step_number = 0;
            $result = $step->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                if($request->image !== null){
                    Storage::delete($getFileName);
                }
                //falseの場合は、500エラーを返却
                $saveError = new \Exception();
                Log::error($saveError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => StepController::$failSaveRecordError, 'status' => false], 500);
            }
            DB::commit();
            return $step;

        }catch (\Exception $e){
            Log::error($e);
            DB::rollBack();
            if($request->image !== null){
                Storage::delete($getFileName);
            }
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }
    //新規登録したSTEPに合計時間とサブSTEP数を追加登録
    public function update(Request $request)
    {
        $request->validate([
            'time' => ['required', 'int','max:18446744073709551615','min:0'],
            'stepNumber' => ['required', 'int','max:18446744073709551615','min:0'],
            'stepId' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);
        DB::beginTransaction();
        try{
            //更新するためにIdが$request->stepIdであるデータを取得
            //データが取得できなかった場合は、ModelNotFoundExceptionエラーの例外処理
            $step = Step::findorFail($request->stepId);

            $step->time_aim = $request->time;
            $step->step_number = $request->stepNumber;
            $result = $step->save();

            //\Exceptionエラーが発生せず、かつsave()がfalseで返却された場合、500エラーを返却
            if($result){
                //trueの場合は何も処理せずそのまま後続処理へ
            }else{
                DB::rollBack();
                //falseの場合は、500エラーを返却
                $saveError = new \Exception();
                Log::error($saveError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => StepController::$failSaveRecordError, 'status' => false], 500);
            }

            DB::commit();
            return $step;

        } catch (ModelNotFoundException $e) {
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => StepController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }
    //STEP・サブSTEPの更新処理
    public function edit(Request $request)
    {

        $subStepForm = json_decode($request->subStepForm, true);
        $request->merge(['subStepForm' => $subStepForm]);

        //カテゴリーを未選択の場合は、カテゴリーが"null"という文字列で渡ってくるため、
        //"null"の場合はnullに変換し、requiredのバリデーションに引っかかるように処理する
        if($request->category_main === "null"){
            $request->merge(['category_main' => null]);
        }
        if($request->category_sub === "null"){
            $request->merge(['category_sub' => null]);
        }
        Log::debug($request);

        //バリデーション
         $request->validate([
             'title' => [ 'required', 'string', 'max:255'],
             'content' => ['string', 'max:500','nullable'],
             'category_main' =>[ 'required','int'],
             'category_sub' =>[ 'required','int'],
             'image' => ['nullable','file', 'max:10240', 'mimes:jpg,jpeg,png,gif'],
             'time_aim' => ['required', 'int','max:18446744073709551615','min:0'],
             'step_number' => ['required', 'int','max:18446744073709551615','min:0'],
             'subStepForm.*.title' => [ 'required', 'string', 'max:255'],
             'subStepForm.*.content' => ['string', 'max:500','nullable'],
             'subStepForm.*.time_aim' => ['required', 'int','max:18446744073709551615','min:0'],
             'subStepForm.*.order' => ['required', 'int','max:18446744073709551615','min:0'],
        ]);

        // Validator::make($subStepForm, [
        //     '*.title' => [ 'required', 'string', 'max:255'],
        //     '*.content' => ['string', 'max:500','nullable'],
        //     '*.time_aim' => ['required', 'int','max:18446744073709551615','min:0'],
        //     '*.order' => ['required', 'int','max:18446744073709551615','min:0'],
        // ])->validate();

        //更新するSTEPをuserIdとcategoryIdで絞って取得
        $userId = Auth::user()->id;

        //認証を挟んでいるのでありえないはずだが
        //認証済みのユーザーIdがなかった場合419認証エラーを返却
        if(!$userId){
            return response()->json(['status' => false], 419);
        }

        $categoryId = $request->id;
        try{
            $step = Step::where([
                ['id', '=' , $categoryId],
                ['user_id', '=', $userId],
            ])->with(['subSteps'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => StepController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
        $imageName = $step->image_path;

        //画像ファイルが存在する場合（画像の新規登録・もしくは更新）
        if($request->image !== null){
            //保存するアイコンのファイル名を作成
            //1.ランダムの12文字を生成
            //2.$request->iconの拡張子を取得
            //3.1と2を結合する
            //4.画像データを取得する
            //5.app/storageに画像データを保存する
            $randomName = Str::random(12);
            $extension = $request->image->extension();
            $getFileName = $randomName.'.'.$extension;
            $img = $request->file('image');
            Storage::putFileAs('public',$img,$getFileName);
        }

        //更新結果を格納する変数を準備
        $returnData = [];

        DB::beginTransaction();
        try{
            //以下、STEP・サブSTEPの更新を行う
            //1. 削除処理
            //削除するサブSTEPのデータ数を取得
            $deleteData = json_decode($request->deletedSubStep, true);
            Log::debug($deleteData);
            $deleteCount = count($deleteData);

            //deleteCountが0もしくはnullでなければ、if内を実行
            if($deleteCount !== 0 || $deleteCount !== null){
                //deleteCountの数分、for文内をループ
                for($i=0;$i<$deleteCount;$i++){
                    //deletedSubStepに格納していた削除予定のサブSTEPのidを代入し、
                    //Substepモデルを使ってデータを消去する
                    $subStepId = $deleteData[$i];
                    $deleteSubStep = Substep::where([
                        ['id', '=' , $subStepId],
                        ['user_id', '=', $userId],
                    ])->delete();

                    //削除に成功した場合は、そのまま後続処理を継続
                    //削除対象のレコードが存在せず、削除ができなかった場合は、500エラーを返却
                    if($deleteSubStep){
                        //delete処理がtrueの場合は何も処理しない
                    }else{
                        DB::rollBack();
                        if($request->icon !== null){
                            Storage::delete($getFileName);
                        }
                        $deleteError = new \Exception();
                        Log::error($deleteError);
                        Log::error(StepController::$failDeleteRecordError);
                        // フロントに異常を通知するため500エラーを返却
                        return response()->json(['message' => StepController::$failDeleteRecordError, 'status' => false], 500);
                    }
                }
            }

            //2. STEP（親元）の更新処理を行う
            $step->title = $request->title;
            $step->category_main = $request->category_main;
            $step->category_sub = $request->category_sub;
            $step->time_aim = $request->time_aim;
            $step->step_number = $request->step_number;

            //$request->contentがnull（紹介文に何も入力していない場合）、
            //nullという文字列でコントローラーにdataが渡ってきてしまうため、
            //if文で文字列のnullが渡ってきたかどうかを確認し、
            //文字列の場合は、文字列ではないnullを登録するようにする
            $step->content = ($request->content !== 'null') ? $request->content : null;


            //画像ファイルが存在する場合
            if($request->image !== null){
                //ファイル名をDBに登録
                $step->image_path = $getFileName;

            //画像データがnullでかつ、アイコンのファイル名は存在する場合
            }else if($request->image === null && $request->imageName === $imageName){
                //既にDB上で登録されているアイコンから変更がないため、処理なし

            //画像データもアイコンのファイル名もnullの場合
            //つまり、アイコンが登録されておらず、今回の更新でも登録しない場合
            }else{
                //ファイル名をnullで登録
                $step->image_path = null;
            }
            $result = $step->save();

            //更新に成功した場合は、そのまま後続処理を継続
            //更新対象のレコードが存在せず、更新ができなかった場合は、500エラーを返却
            if($result){
                //save処理がtrueの場合は何も処理しない
            }else{
                DB::rollBack();
                if($request->icon !== null){
                    Storage::delete($getFileName);
                }
                $UpdateError = new \Exception();
                Log::error($UpdateError);
                Log::error(StepController::$failUpdateRecordError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => StepController::$failUpdateRecordError, 'status' => false], 500);
            }

            //結果を$returnData配列にpushする
            array_push($returnData, $step);

            //3. サブSTEPの更新処理をひとつずつ行う
            //更新するサブSTEPのデータ数を取得する
            $subStepCount = count($subStepForm);

            //subStepCountが0もしくはnullでなければ、if内を実行
            if($subStepCount !== 0 || $subStepCount !== null){
                //subStepCountの数分for文内をループ
                for($i=0;$i<$subStepCount;$i++){
                    //SubStepモデルのidをキーとして、idが存在すれば更新、なければ新規追加する
                    $subStep = SubStep::upsert([
                        ['id' => $subStepForm[$i]['id'],
                        'title' => $subStepForm[$i]['title'],
                        'time_aim' => $subStepForm[$i]['time_aim'],
                        'content' => $subStepForm[$i]['content'],
                        'step_id' => $request->id,
                        'order' => $subStepForm[$i]['order'],
                        'user_id' => $userId]
                    ],['id']);

                    //更新に成功した場合は、そのまま後続処理を継続
                    //更新対象のレコードが存在せず、更新ができなかった場合は、500エラーを返却
                    if($subStep){
                        //更新が成功した場合は何も処理しない
                    }else{
                        DB::rollBack();
                        if($request->icon !== null){
                            Storage::delete($getFileName);
                        }
                        $UpdateError = new \Exception();
                        Log::error($UpdateError);
                        Log::error(StepController::$failUpdateRecordError);
                        // フロントに異常を通知するため500エラーを返却
                        return response()->json(['message' => StepController::$failUpdateRecordError, 'status' => false], 500);
                    }
                    //結果を$returnData配列にpushする
                    array_push($returnData, $subStep);
                }
            }
            DB::commit();
            return $returnData;

        //エラーの場合はデータをロールバック
        } catch (\Exception $e){
            DB::rollback();
            if($request->icon !== null){
                Storage::delete($getFileName);
            }
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }

    //登録したSTEPを削除する
    public function destroy(Request $request){
        $userId = Auth::user()->id;

        //認証を挟んでいるのでありえないはずだが
        //認証済みのユーザーIdがなかった場合419認証エラーを返却
        if(!$userId){
            return response()->json(['status' => false], 419);
        }
        try{
            $step = Step::where([
                ['id', '=', $request->id],
                ['user_id', '=', $userId]
            ])->delete();
            //削除に成功した場合は、そのまま$stepを返却
            //削除対象のレコードが存在せず、削除ができなかった場合は、500エラーを返却
            if($step){
                return $step;
            }else{
                DB::rollback();
                $deleteError = new \Exception();
                Log::error($deleteError);
                Log::error(StepController::$failDeleteRecordError);
                // フロントに異常を通知するため500エラーを返却
                return response()->json(['message' => StepController::$failDeleteRecordError, 'status' => false], 500);
            }

        } catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }

    //登録されているSTEPをページごとに取得する（サブSTEPが0のSTEPは除く）
    public function index()
    {
        //例外処理
        //今回の場合、取得したSTEPが0件でも問題ないので、
        //取得したSTEPが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{
            $allSteps = Step::where([
                ['step_number','!=',0],
            ])->with(['challengeStep'])->orderBy('id', 'desc')->paginate();
            return $allSteps;

        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
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

        //例外処理
        //今回の場合、取得したSTEPが0件でも問題ないので、
        //取得したSTEPが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{
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
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }

    //自分が登録したSTEP一覧を取得する（サブSTEPが0のSTEPを含む）
    public function indexMySteps()
    {

        $id = Auth::user()->id;
        //例外処理
        //今回の場合、取得したSTEPが0件でも問題ないので、
        //取得したSTEPが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{
            $mySteps = Step::where('user_id', $id)->with(['challengeStep'])->orderBy(Step::CREATED_AT, 'desc')->paginate();

            return $mySteps;
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
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

        //例外処理
        //今回の場合、取得したSTEPが0件でも問題ないので、
        //取得したSTEPが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{
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
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }

    //自分が挑戦しているSTEP一覧を取得する（既に削除済のSTEPも含む）
    public function indexMyChallenge()
    {
        $userId = Auth::user()->id;
        //例外処理
        //今回の場合、取得したSTEPが0件でも問題ないので、
        //取得したSTEPが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{
            $myChallenge = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query) use ($userId){
                $query->where('user_id', $userId);
            })->orderBy('created_at', 'desc')->paginate();
            return $myChallenge;

        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }


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
        //例外処理
        //今回の場合、検索条件に合致したSTEPが0件でも問題ないので、
        //取得したデータが0件でもそのまま処理を続行する
        //そのため、例外処理は、データ取得時に予期せぬエラーが発生した場合のみ行う
        try{

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

        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }

    //STEP詳細画面の表示
    public function show(string $id)
    {
        //例外処理
        //詳細画面の表示ではかならず該当データを1件取得する必要がある
        //そのため、firstOrFail()を使用し、該当データが0件の場合は、ModelNotFoundExceptionの例外処理で404エラーを返す
        try{
            $step = Step::withTrashed()->where([
                ['id', '=' , $id],
            ])->with(['substeps','challengeStep','user:id,icon,introduction'])->firstOrFail();
            return $step;
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            // フロントに異常を通知するため404エラーを返却
            return response()->json(['message' => StepController::$notFoundRecordError, 'status' => false], 404);
        } catch (\Exception $e){
            Log::error($e);
            // フロントに異常を通知するため500エラーを返却
            return response()->json(['message' => StepController::$systemError, 'status' => false], 500);
        }
    }
}
