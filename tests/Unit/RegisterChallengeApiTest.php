<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;

class RegisterChallengeApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;
    private $substep1;
    private $substep2;
    private $substep3;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->step = Step::factory()->create([
                'category_main' => 4,
                'category_sub' => 10,
                'title' => '%sample title' . random_int(0, 99),
                'step_number' => 3,
                'time_aim' => 180,
        ]);
        //親のSTEPに紐づくサブステップを3つ作成
        $this->substep1 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'order' => 1,
            'time_aim' => 60
        ]);
        $this->substep2 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'order' => 2,
            'time_aim' => 60

        ]);
        $this->substep3 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'order' => 3,
            'time_aim' => 60

        ]);
    }
    /**
     * @test
     * STEPに挑戦する機能テスト(正常系)
     */
    public function should_register_new_challenge_true() :void
    {
        $data= [
            'step_id' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('challenge'), $data);

        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $data = $response->getData();

        $this->assertEquals(4, count($data));

        //取得したデータの値が期待される値と一致するかを確認
        $this->assertEquals($data[0]->user_id, $this->user->id);
        $this->assertEquals($data[0]->step_id, $this->step->id);
        $this->assertEquals($data[0]->substep_id, null);
        $this->assertEquals($data[0]->time, 0);
        $this->assertEquals($data[0]->challenge_flg, true);
        $this->assertEquals($data[0]->order, 0);

        $this->assertEquals($data[1]->user_id, $this->user->id);
        $this->assertEquals($data[1]->step_id, $this->step->id);
        $this->assertEquals($data[1]->substep_id, $this->substep1->id);
        $this->assertEquals($data[1]->time, 0);
        $this->assertEquals($data[1]->challenge_flg, true);
        $this->assertEquals($data[1]->order, 1);

        $this->assertEquals($data[2]->user_id, $this->user->id);
        $this->assertEquals($data[2]->step_id, $this->step->id);
        $this->assertEquals($data[2]->substep_id, $this->substep2->id);
        $this->assertEquals($data[2]->time, 0);
        $this->assertEquals($data[2]->challenge_flg, false);
        $this->assertEquals($data[2]->order, 2);

        $this->assertEquals($data[3]->user_id, $this->user->id);
        $this->assertEquals($data[3]->step_id, $this->step->id);
        $this->assertEquals($data[3]->substep_id, $this->substep3->id);
        $this->assertEquals($data[3]->time, 0);
        $this->assertEquals($data[3]->challenge_flg, false);
        $this->assertEquals($data[3]->order, 3);
    }
    /**
     * @test
     * STEPに挑戦する機能テスト(異常系 該当するSTEPがDBに存在しない)
     */
    public function should_register_new_challenge_false_no_step() :void
    {
        $data= [
            'step_id' => 1000,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('challenge'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);
    }
    /**
     * @test
     * STEPに挑戦する機能テスト(異常系 該当するSTEPのサブSTEPがDBに存在しない)
     */
    public function should_register_new_challenge_false_no_substep() :void
    {
        $step = Step::factory()->create();
        $data= [
            'step_id' => $step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('challenge'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);
    }
    /**
     * @test
     * STEPに挑戦する機能テスト(異常系 ログインしていない状態で挑戦しようとする)
     */
    public function should_register_new_challenge_false_no_login() :void
    {
        $data= [
            'step_id' => $this->step->id,
        ];
        $response = $this->json('POST', route('challenge'), $data);

        //レスポンスステータスが401であることを確認
        $response->assertStatus(401);
    }
}
