<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;
use App\Models\Challenge;



class ClearChallengeApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;
    private $substep1;
    private $substep2;
    private $substep3;
    private $stepNotIncludeSubStep;

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
            'order' => 1,
            'time_aim' => 60
        ]);
        $this->substep2 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'order' => 2,
            'time_aim' => 60

        ]);
        $this->substep3 = Substep::factory()->create([
            'step_id' => $this->step->id,
            'order' => 3,
            'time_aim' => 60
        ]);

        $this->stepNotIncludeSubStep = Step::factory()->create(['step_number' => 0]);

    }
    /**
     * @test
     * STEPをクリアする機能（正常系 ）
     */
    public function should_clear_step_true() :void
    {
        //作成したSTEPの挑戦データを作成
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
        ]);
        Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => false,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目のサブSTEPをクリアした状態で、2番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep2->id,
            'time' => 60,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $data = $response->getData();

        //更新されたデータが期待値と一致していることを確認
        //更新データの順番は、[0]クリアしたサブSTEPの挑戦データ、[1]次のサブSTEPの挑戦データ、[2]親元のSTEPの挑戦データ

        //1.$data[0]に格納されているクリアしたサブSTEPの挑戦データの確認
        //時間が更新されていることを確認+clear_flgがtrueに更新されていることを確認
        $this->assertEquals($data[0]->time, 60);
        $this->assertEquals($data[0]->clear_flg, true);
        $this->assertEquals($data[0]->challenge_flg, true);

        //2.$data[1]に格納されている次のサブSTEPの挑戦データの確認
        //challenge_flgがtrueになっていることを確認
        $this->assertEquals($data[1]->time, 0);
        $this->assertEquals($data[1]->clear_flg, false);
        $this->assertEquals($data[1]->challenge_flg, true);


        //3.$data[2]に格納されているSTEP自体の挑戦データの確認
        //達成時間が120分(1つ目のサブSTEPの達成時間)+60分(2つ目のサブSTEPの達成時間)=180分であることを確認
        //全てのサブSTEPをクリアしていないので、$clear_flgがfalseであることを確認
        $this->assertEquals($data[2]->time, 180);
        $this->assertEquals($data[2]->clear_flg, false);
        $this->assertEquals($data[2]->challenge_flg, true);
    }
    /**
     * @test
     * STEPをクリアする機能（正常系 最後のサブSTEPをクリアした場合）
     */
    public function should_clear_step_true_last_sub_step() :void
    {
        //作成したSTEPの挑戦データを作成
        Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、ラストの3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $data = $response->getData();

        //更新されたデータが期待値と一致していることを確認
        //更新データの順番は、[0]クリアしたサブSTEPの挑戦データ、[1]親元のSTEPの挑戦データ

        //1.$data[0]に格納されているクリアしたサブSTEPの挑戦データの確認
        //時間が更新されていることを確認+clear_flgがtrueに更新されていることを確認
        $this->assertEquals($data[0]->time, 120);
        $this->assertEquals($data[0]->clear_flg, true);
        $this->assertEquals($data[0]->challenge_flg, true);

        //3.$data[2]に格納されているSTEP自体の挑戦データの確認
        //達成時間が3つのサブステップの達成時間の合計である360分であることを確認
        //全てのサブSTEPをクリアしたので、$clear_flgがtrueであることを確認
        $this->assertEquals($data[1]->time, 360);
        $this->assertEquals($data[1]->clear_flg, true);
        $this->assertEquals($data[1]->challenge_flg, true);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 達成時間空白によるバリデーションエラー）
     */
    public function should_clear_step_false_time_blank() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => null,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 達成時間が数字以外によるバリデーションエラー）
     */
    public function should_clear_step_false_time_not_int() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => 'time',
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 達成時間が負の数によるバリデーションエラー）
     */
    public function should_clear_step_false_time_not_positive_number() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => -1,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 クリアしたいSTEPが存在しないためエラー）
     */
    public function should_clear_step_false_clear_step_not_exist() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' =>  $this->substep3->id,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => 10000,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('clear'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 未ログインのためクリアできずにエラー）
     */
    public function should_clear_step_false_not_login() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->json('POST', route('clear'), $data);

        //レスポンスステータスが401であることを確認
        $response->assertStatus(401);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 サブSTEPが存在しないためエラー）
     */
    public function should_clear_step_false_not_exist_substep() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => $this->stepNotIncludeSubStep,
        ];
        $response = $this->actingAs($this->user)
            ->json('POST', route('clear'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 クリア予定のサブSTEPのひとつ前のサブSTEPがクリアできていないため500エラー）
     */
    public function should_clear_step_false_not_cleared_prev_substep() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => $this->substep3->id,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
            ->json('POST', route('clear'), $data);

        //レスポンスステータスが500であることを確認
        $response->assertStatus(500);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
    /**
     * @test
     * STEPをクリアする機能（異常系 クリア予定のサブSTEPの挑戦データが存在しないためエラー）
     */
    public function should_clear_step_false_not_select_clear_sub_step() :void
    {
        //作成したSTEPの挑戦データを作成
        $challengeMain = Challenge::factory()->create([
            'order' => 0,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => null,
            'user_id' => $this->user->id,
            'time' => 240,
        ]);
        Challenge::factory()->create([
            'order' => 1,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep1->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $challengeSub = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => true,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);

        //1番目と2番目のサブSTEPをクリアした状態で、3番目のサブSTEPをクリアする
        $data = [
            'id' => 10000,
            'time' => 120,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
            ->json('POST', route('clear'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();

        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $challengeMain->id);
        $this->assertEquals($challenge[0]->clear_flg, $challengeMain->clear_flg);
        $this->assertEquals($challenge[0]->time, $challengeMain->time);

        //3番目のクリアしたかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $challengeSub->id);
        $this->assertEquals($challenge[3]->clear_flg, $challengeSub->clear_flg);
        $this->assertEquals($challenge[3]->time, $challengeSub->time);
    }
}
