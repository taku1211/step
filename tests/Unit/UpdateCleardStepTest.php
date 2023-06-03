<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;
use App\Models\Challenge;

class UpdateCleardStepTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;
    private $substep1;
    private $substep2;
    private $substep3;
    private $updateChallengeData;
    private $updateMainChallengeData;
    private $notCleardData;

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
        //作成したSTEPのクリア済挑戦データ(2番目のSTEPまでクリア済)を作成
        $this->updateMainChallengeData = Challenge::factory()->create([
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
        $this->updateChallengeData = Challenge::factory()->create([
            'order' => 2,
            'challenge_flg' => true,
            'clear_flg' => true,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep2->id,
            'user_id' => $this->user->id,
            'time' => 120,
        ]);
        $this->notCleardData = Challenge::factory()->create([
            'order' => 3,
            'challenge_flg' => false,
            'clear_flg' => false,
            'step_id' => $this->step->id,
            'substep_id' => $this->substep3->id,
            'user_id' => $this->user->id,
        ]);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（正常系）
     */
    public function should_update_cleard_sub_step_time_true() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => 30,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);
        $data = $response->getData();


        //1.$data[0]に格納されているクリアしたサブSTEPの挑戦データの確認
        //時間が更新されていることを確認+clear_flgがtrueに更新されていることを確認
        $this->assertEquals($data[0]->id, $this->updateChallengeData->id);
        $this->assertEquals($data[0]->time, 30);

        //2.$data[1]に格納されているSTEP自体の挑戦データの確認
        //達成時間が120分(1つ目のサブSTEPの達成時間)+30分(更新した2つ目のサブSTEPの達成時間)=150分であることを確認
        //全てのサブSTEPをクリアしていないので、$clear_flgがfalseであることを確認
        $this->assertEquals($data[1]->time, 150);
        $this->assertEquals($data[1]->clear_flg, false);
        $this->assertEquals($data[1]->challenge_flg, true);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 時間未選択によるエラー）
     */
    public function should_update_cleard_sub_step_time_false_time_blank() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => null,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 時間が数字以外によるエラー）
     */
    public function should_update_cleard_sub_step_time_false_time_not_int() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => 'time',
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 時間が負の数によるエラー）
     */
    public function should_update_cleard_sub_step_time_false_time_not_positive_number() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => -1,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 未ログインによるエラー）
     */
    public function should_update_cleard_sub_step_time_false_not_login() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => 30,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->json('POST', route('updateClear'), $data);

        //レスポンスステータスが401であることを確認
        $response->assertStatus(401);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 更新したいデータに紐づくサブSTEPが存在しないことによるエラー）
     */
    public function should_update_cleard_sub_step_time_false_not_exist_sub_step() : void
    {
        $data = [
            'id' => 1000000,
            'time' => 30,
            'order' => $this->substep2->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 更新したいSTEPに紐づくSTEPが存在しないことによるエラー）
     */
    public function should_update_cleard_sub_step_time_false_not_exist_related_step() : void
    {
        $data = [
            'id' => $this->substep2->id,
            'time' => 30,
            'order' => $this->substep2->order,
            'mainId' => 1000000,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //2番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[2]->id, $this->updateChallengeData->id);
        $this->assertEquals($challenge[2]->clear_flg, $this->updateChallengeData->clear_flg);
        $this->assertEquals($challenge[2]->time, $this->updateChallengeData->time);
    }
    /**
     * @test
     * クリアしたサブSTEPの時間を更新する機能（異常系 まだクリアしていない挑戦データの時間を更新しようとしてエラー）
     */
    public function should_update_cleard_sub_step_time_false_not_cleard_step() : void
    {
        $data = [
            'id' => $this->substep3->id,
            'time' => 30,
            'order' => $this->substep3->order,
            'mainId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
        ->json('POST', route('updateClear'), $data);

        //レスポンスステータスが500であることを確認
        $response->assertStatus(500);

        //データが更新されていないことを確認
        $challenge = Challenge::where([
            'step_id' => $this->step->id,
            'user_id' => $this->user->id
        ])->get();


        //親元のサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[0]->id, $this->updateMainChallengeData->id);
        $this->assertEquals($challenge[0]->clear_flg, $this->updateMainChallengeData->clear_flg);
        $this->assertEquals($challenge[0]->time, $this->updateMainChallengeData->time);

        //3番目の達成時間を更新したかったサブSTEPが更新されていないことを確認
        $this->assertEquals($challenge[3]->id, $this->notCleardData->id);
        $this->assertEquals($challenge[3]->clear_flg, $this->notCleardData->clear_flg);
        $this->assertEquals($challenge[3]->challenge_flg, $this->notCleardData->challenge_flg);
        $this->assertEquals($challenge[3]->time, $this->notCleardData->time);
    }

}
