<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Builder;

class GetAllMyChallengeApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;

    private $stepsNoRegisterSubStep;

    public function setUp() :void
    {
        parent::setUp();
        $this->user = User::factory()->create(['id' => 50]);

        $this->step = Step::factory()->has(
            Challenge::factory()->count(3)->state(function (array $attributes, Step $step) {
                return [
                    'user_id' => 50,
                    'step_id' => $step->id];
            }), 'challengeStep')
            ->count(15)->create(
            ['step_number' => 3,'time_aim' => 180]);


    }
    /**
     * @test
     * 自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     */
    public function should_get_my_challenge_index_true() :void
    {
        $response = $this->actingAs($this->user)->json('GET', route('indexMyChallenge'));
        $response->assertStatus(200)
        ->assertJsonCount(8, 'data');

        $data = $response->getData();
        $id = $this->user->id;

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $myChallenge = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query) use ($id){
            $query->where('user_id', $id);
        })->orderBy('created_at', 'desc')->paginate();

        $stepData = $myChallenge->items();

        for($i=0; $i<8; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);

        }
    }
    /**
     * @test
     * 自分が挑戦したSTEP一覧をページごとに取得する機能（正常系）
     * ページネーションの2ページ目を取得
     */
    public function should_get_my_challenge_index_true_2_page() :void
    {
        $data = ['page' => 2];
        $response = $this->actingAs($this->user)->json('GET', route('indexMyChallenge', $data));
        $response->assertStatus(200)
        ->assertJsonCount(7, 'data');

        $data = $response->getData();
        $id = $this->user->id;

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $myChallenge = Step::withTrashed()->with(['challengeStep'])->whereHas('challengeStep', function(Builder $query) use ($id){
            $query->where('user_id', $id);
        })->orderBy('created_at', 'desc')->paginate(8, ['*'], 'page', 2);

        $stepData = $myChallenge->items();

        for($i=0; $i<7; $i++){
            $this->assertEquals($stepData[$i]['id'], $data->data[$i]->id);
            $this->assertEquals($stepData[$i]['title'], $data->data[$i]->title);
            $this->assertEquals($stepData[$i]['content'], $data->data[$i]->content);
            $this->assertEquals($stepData[$i]['category_main'], $data->data[$i]->category_main);
            $this->assertEquals($stepData[$i]['category_sub'], $data->data[$i]->category_sub);
        }
    }
}
