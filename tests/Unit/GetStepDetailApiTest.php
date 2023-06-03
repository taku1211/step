<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;
use App\Models\Challenge;

class GetStepDetailApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $challengeUser;
    private $step;
    private $substep1;
    private $substep2;
    private $substep3;

    public function setUp(): void
    {
        parent::setUp();
        //ユーザーおよび、STEPとそのSTEPに挑戦している人のデータを作成
        $this->challengeUser = User::factory()->create();
        $this->user = User::factory()->create();

        $this->step = Step::factory()->create([
                'category_main' => 4,
                'category_sub' => 10,
                'title' => '%sample title' . random_int(0, 99),
                'step_number' => 3,
                'time_aim' => 180,
                'user_id' => $this->user->id
        ]);
        //親のSTEPに紐づくサブステップを3つ作成
        $this->substep1 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id,
            'order' => 1
        ]);
        $this->substep2 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id,
            'order' => 2
        ]);
        $this->substep3 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id,
            'order' => 3
        ]);

        //作成したSTEPの挑戦データを作成
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => 1,
            'clear_flg' => 0,
            'step_id' => $this->step->id,
            'substep_id' => null
        ]);
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => 1,
            'clear_flg' => 0,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id
        ]);
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => 0,
            'clear_flg' => 0,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id
        ]);
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => 0,
            'clear_flg' => 0,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id
        ]);
    }
    /**
     * @test
     * STEP詳細を取得する機能（正常系）
     */
    public function sholud_get_step_detail_true() :void
    {
        $id = ['id' => $this->step->id];
        $response = $this->json('GET', route('show', $id));
        $response->assertStatus(200);

        $data = $response->getData();

        //取得したデータが正しいかどうかを確認する
        $step = Step::withTrashed()->where([
            ['id', '=' , $this->step->id],
        ])->with(['substeps','challengeStep','user:id,icon,introduction'])->firstOrFail();

        $this->assertEquals($step->id, $data->id);
        $this->assertEquals($step->title, $data->title);
        $this->assertEquals($step->category_main, $data->category_main);
        $this->assertEquals($step->category_sub, $data->category_sub);
        $this->assertEquals($step->content, $data->content);
        $this->assertEquals($step->time_aim, $data->time_aim);
        $this->assertEquals($step->step_number, $data->step_number);
        $this->assertEquals($step->image_path, $data->image_path);
        $this->assertEquals($step->user->id, $data->user->id);
        $this->assertEquals($step->user->icon, $data->user->icon);
        $this->assertEquals($step->user->introduction, $data->user->introduction);
        $this->assertEquals($step->substeps[0]->id, $data->substeps[0]->id);
        $this->assertEquals($step->substeps[0]->title, $data->substeps[0]->title);
        $this->assertEquals($step->substeps[0]->time_aim, $data->substeps[0]->time_aim);
        $this->assertEquals($step->substeps[0]->order, $data->substeps[0]->order);
        $this->assertEquals($step->substeps[0]->content, $data->substeps[0]->content);
        $this->assertEquals($step->substeps[1]->id, $data->substeps[1]->id);
        $this->assertEquals($step->substeps[1]->title, $data->substeps[1]->title);
        $this->assertEquals($step->substeps[1]->time_aim, $data->substeps[1]->time_aim);
        $this->assertEquals($step->substeps[1]->order, $data->substeps[1]->order);
        $this->assertEquals($step->substeps[1]->content, $data->substeps[1]->content);
        $this->assertEquals($step->substeps[2]->id, $data->substeps[2]->id);
        $this->assertEquals($step->substeps[2]->title, $data->substeps[2]->title);
        $this->assertEquals($step->substeps[2]->time_aim, $data->substeps[2]->time_aim);
        $this->assertEquals($step->substeps[2]->order, $data->substeps[2]->order);
        $this->assertEquals($step->substeps[2]->content, $data->substeps[2]->content);
        $this->assertEquals($step->challengeStep[0]->id, $data->challenge_step[0]->id);
        $this->assertEquals($step->challengeStep[1]->id, $data->challenge_step[1]->id);
        $this->assertEquals($step->challengeStep[2]->id, $data->challenge_step[2]->id);
        $this->assertEquals($step->challengeStep[3]->id, $data->challenge_step[3]->id);
    }
        /**
     * @test
     * STEP詳細を取得する機能（異常系 該当するSTEPが存在しない）
     */
    public function sholud_get_step_detail_false() :void
    {
        $id = ['id' => 100];
        $response = $this->json('GET', route('show', $id));
        $response->assertStatus(404);
    }
}
