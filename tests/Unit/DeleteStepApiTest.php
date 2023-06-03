<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;

class DeleteStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;
    private $substep1;
    private $substep2;

    public function setUp() :void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->step = Step::factory()->create(['user_id' => $this->user->id]);
        $this->substep1 = Substep::factory()->create([
            'user_id' => $this->user->id,
            'step_id' => $this->step->id,
            'order' => 1
        ]);
        $this->substep2 = Substep::factory()->create([
            'user_id' => $this->user->id,
            'step_id' => $this->step->id,
            'order' => 2
        ]);
    }
    /**
     * @test
     * 登録されているSTEPを削除する機能テスト(正常系)
     */
    public function should_delete_step_true() :void
    {
        $data = [
            'id' =>  $this->step->id,
            'user_id' =>  $this->user->id,
        ];
        //データが存在することを確認
        $deleteStep = Step::where([
            ['id', '=', $this->step->id],
            ['user_id', '=', $this->user->id]
        ])->get();

        $this->assertEquals(1, $deleteStep->count());

        $response = $this->actingAs($this->user)
                        ->json('POST', route('destroy'), $data);

        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //論理削除されたデータを取得
        $deletedStep = Step::withTrashed()->where([
            ['id', '=', $this->step->id],
            ['user_id', '=', $this->user->id]
        ])->get();

        //取得数およびdeleted_atがnullでないことを確認
        $this->assertEquals(1, $deletedStep->count());
        $this->assertNotNull($deletedStep[0]['deleted_at']);
    }
    /**
     * @test
     * 登録されているSTEPを削除する機能テスト(異常系 該当データが存在しない)
     */
    public function should_delete_step_false() :void
    {
        $data = [
            'id' =>  100,
            'user_id' =>  $this->user->id,
        ];
        //データが存在しないことを確認
        $deleteStep = Step::where([
            ['id', '=', 100],
            ['user_id', '=', $this->user->id]
        ])->get();

        $this->assertEquals(0, $deleteStep->count());

        $allStepCount = Step::count();
        $this->assertEquals(1, $allStepCount);

        $response = $this->actingAs($this->user)
                        ->json('POST', route('destroy'), $data);

        //レスポンスステータスが500であることを確認
        $response->assertStatus(500);

        //データ数を取得し、データが削除されていないことを確認
        $afterAllStepCount = Step::count();
        $this->assertEquals($afterAllStepCount, $allStepCount);
    }
    /**
     * @test
     * 登録されているSTEPを削除する機能テスト(異常系 未ログインのため削除失敗)
     */
    public function should_delete_step_false_not_login() :void
    {
        $data = [
            'id' =>  100,
            'user_id' =>  $this->user->id,
        ];
        //データが存在することを確認
        $deleteStep = Step::where([
            ['id', '=', $this->step->id],
            ['user_id', '=', $this->user->id]
        ])->get();

        $this->assertEquals(1, $deleteStep->count());



        $allStepCount = Step::count();
        $this->assertEquals(1, $allStepCount);

        $response = $this->json('POST', route('destroy'), $data);


        //レスポンスステータスが401であることを確認
        $response->assertStatus(401);

        //データ数を取得し、データが削除されていないことを確認
        $afterAllStepCount = Step::count();
        $this->assertEquals($afterAllStepCount, $allStepCount);
    }
}
