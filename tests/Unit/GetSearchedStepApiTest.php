<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;

class GetSearchedStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $step;

    private $stepsNoRegisterSubStep;

    public function setUp(): void
    {
        parent::setUp();

        $this->step = Step::factory()->count(10)->create(['step_number' => 3, 'time_aim' => 180]);
    }
    /**
     * @test
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * キーワード・メインカテゴリー・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_all_conditions(): void
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
            'selectedCategorySub' => 10,
            'sort' => 'lastName',
            'page' => 2,
        ];
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['title', 'like', $keyword],
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['category_sub', '=', $conditions['selectedCategorySub']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();


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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * メインカテゴリー・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_conditions_except_keyword(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['category_main', '=', $conditions['selectedCategoryMain']],
            ['category_sub', '=', $conditions['selectedCategorySub']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();

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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_sub_category_and_sort_conditions(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['category_sub', '=', $conditions['selectedCategorySub']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();

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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * メインカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_main_category_and_sort_conditions(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['category_main', '=', $conditions['selectedCategoryMain']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();

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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * キーワード・サブカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_conditions_except_main_category(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['title', 'like', $keyword],
            ['category_sub', '=', $conditions['selectedCategorySub']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();


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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * キーワード・メインカテゴリー・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_all_conditions_except_sub_category(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['title', 'like', $keyword],
            ['category_main', '=', $conditions['selectedCategoryMain']],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();


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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * キーワード・ソート順が指定されている場合
     */
    public function should_get_searched_step_index_true_include_keyword_and_sort_conditions(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ7件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(7, 'data');

        $data = $response->getData();



        //検索キーワードを検索用に変換
        $keyword = '%'.addcslashes($conditions['keyword'], '%_\\').'%' ?? "";

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
            ['title', 'like', $keyword],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();


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
     * 検索条件のSTEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * 検索条件が指定されていない場合
     */
    public function should_get_searched_step_index_true_include_no_conditions(): void
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
        $response = $this->json('POST', route('search'), $conditions);

        //レスポンスが200であり、かつ2ページ目のデータ8件が返ってきていることを確認
        $response->assertStatus(200)
            ->assertJsonCount(8, 'data');

        $data = $response->getData();


        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
        ])->with(['challengeStep'])->orderBy('title', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();


        for($i=0; $i<8; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
}
