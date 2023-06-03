<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;

class GetAllStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;

    private $stepsNoRegisterSubStep;

    public function setUp() :void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->step = Step::factory()->count(10)->create(['step_number' => 3, 'time_aim' => 180 ]);


        $this->stepsNoRegisterSubStep = Step::factory()->count(2)->create(['step_number' => 0]);
    }
    /**
     * @test
     * STEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     */
    public function should_get_step_index_true() :void
    {
        $response = $this->json('GET', route('index'));
        $response->assertStatus(200)
        ->assertJsonCount(8, 'data');

        $data = $response->getData();

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
        ])->with(['challengeStep'])->orderBy('id', 'desc')->paginate();

        $stepData = $allSteps->items();

        for($i=0; $i<8; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);

        }
    }
    //ページネーションの2ページ目以降のデータが取れているか確認
    //DBエラーによる例外処理のテスト
    /**
     * @test
     * STEP一覧をページごとに取得する機能（正常系 ただし、step_numberが0のデータは取得しない）
     * ページネーションの2ページ目を取得
     */
    public function should_get_step_index_true_2_page() :void
    {
        $data = ['page' => 2];
        $response = $this->json('GET', route('index', $data));
        $response->assertStatus(200)
        ->assertJsonCount(2, 'data');

        $data = $response->getData();

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $allSteps = Step::where([
            ['step_number','!=',0],
        ])->with(['challengeStep'])->orderBy('id', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $allSteps->items();

        for($i=0; $i<2; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
}
