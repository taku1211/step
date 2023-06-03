<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Builder;

class GetSearchedMyChallengeApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $anotherUser;
    private $step;
    private $anotherUserStep;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['id' => 50]);
        $this->anotherUser = User::factory()->create(['id' => 51]);

        $this->step = Step::factory()->has(
            Challenge::factory()->count(3)->state(function (array $attributes, Step $step) {
                return [
                    'user_id' => 50,
                    'step_id' => $step->id];
            }), 'challengeStep')
            ->count(15)->create([
                'category_main' => 4,
                'category_sub' => 10,
                'title' => '%sample title' . random_int(0, 99),
                'step_number' => 3,
                'time_aim' => 180,
        ]);
        $this->anotherUserStep = Step::factory()->has(
            Challenge::factory()->count(3)->state(function (array $attributes, Step $step) {
                return [
                    'user_id' => 51,
                    'step_id' => $step->id];
            }), 'challengeStep')
            ->count(15)->create([
                'category_main' => 4,
                'category_sub' => 10,
                'title' => '%sample title' . random_int(0, 99),
                'step_number' => 3,
                'time_aim' => 180,
        ]);
    }
    /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * キーワード・メインカテゴリー・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_all_conditions(): void
    {
        $conditions = [
            'keyword' => 'sample',
            'selectedCategoryMain' => 4,
            'selectedCategorySub' => 10,
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();

        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得

        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['title', 'like', $keyword],
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['category_sub', '=', $conditions['selectedCategorySub']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items(8, ['*'], 'page', 2);

        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
    /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * メインカテゴリー・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_conditions_except_keyword(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => '',
            'selectedCategoryMain' => 4,
            'selectedCategorySub' => 10,
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['category_sub', '=', $conditions['selectedCategorySub']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();

        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
        /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_sub_category_and_sort_conditions(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => '',
            'selectedCategoryMain' => '',
            'selectedCategorySub' => 10,
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['category_sub', '=', $conditions['selectedCategorySub']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();

        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
        /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * メインカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_main_category_and_sort_conditions(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => '',
            'selectedCategoryMain' => 4,
            'selectedCategorySub' => '',
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();

        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
        /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * キーワード・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_conditions_except_main_category(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => 'sample',
            'selectedCategoryMain' => '',
            'selectedCategorySub' => 10,
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['title', 'like', $keyword],
            ['category_sub', '=', $conditions['selectedCategorySub']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();


        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
        /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * キーワード・メインカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_all_conditions_except_sub_category(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => 'sample',
            'selectedCategoryMain' => 4,
            'selectedCategorySub' => '',
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['title', 'like', $keyword],
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();


        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
    /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * キーワード・ソート順が指定されている場合
     */
    public function should_get_searched_my_challenge_index_true_include_keyword_and_sort_conditions(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => 'sample',
            'selectedCategoryMain' => '',
            'selectedCategorySub' => '',
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['title', 'like', $keyword],
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();


        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
    /**
     * @test
     * 検索条件の自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * 検索条件が指定されていない場合
     */
    public function should_get_searched_my_challenge_index_true_include_no_conditions(): void
    {
        Step::factory()->count(15)->create([
            'category_main' => 4,
            'category_sub' => 10,
            'title' => '%sample title' . random_int(0, 99),
            'step_number' => 3,
            'time_aim' => 180
        ]);
        $conditions = [
            'keyword' => '',
            'selectedCategoryMain' => '',
            'selectedCategorySub' => '',
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->actingAs($this->user)->json('POST', route('searchMyChallenge'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $searchSteps = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query){
            $id = $this->user->id;
            $query->where('user_id', $id);
        })->where([
            ['step_number','!=',0],
            ])->orderBy('title', 'desc')->paginate();

        $stepData = $searchSteps->items();


        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
}
