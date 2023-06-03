<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;

class UpdateStepApiTest extends TestCase
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
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(正常系)
     */
    public function should_update_step_add_sumTime_stepNumber_true() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => $sumTime,
            'stepNumber' => $stepNumber,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $updateStep = Step::first();
        $this->assertEquals($updateStep->time_aim, $data['time']);
        $this->assertEquals($updateStep->step_number, $data['stepNumber']);
        $this->assertEquals($updateStep->id, $data['stepId']);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 合計時間空白によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_sumTime_blank() :void
    {

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => null,
            'stepNumber' => $stepNumber,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 合計時間が文字によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_sumTime_not_int() :void
    {

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => 'a',
            'stepNumber' => $stepNumber,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 合計時間が負の数によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_sumTime_not_positive_number() :void
    {

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => -30,
            'stepNumber' => $stepNumber,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEP数が空白によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepNumber_blank() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $data = [
            'time' => $sumTime,
            'stepNumber' => null,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEP数が文字によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepNumber_not_int() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $data = [
            'time' => $sumTime,
            'stepNumber' => 'a',
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEP数が負の数によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepNumber_not_poditive_number() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $data = [
            'time' => $sumTime,
            'stepNumber' => -10,
            'stepId' => $this->step->id,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEPのidが空白によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepId_blank() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => $sumTime,
            'stepNumber' => $stepNumber,
            'stepId' => null,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEPのidが文字によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepId_not_int() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => $sumTime,
            'stepNumber' => $stepNumber,
            'stepId' => 'a',
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 STEPのidが負の数によるエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepId_not_positive_number() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => $sumTime,
            'stepNumber' => $stepNumber,
            'stepId' => -10,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
    /**
     * @test
     * 新規登録したSTEPに合計時間とサブSTEP数を追加登録する機能テスト(異常系 指定されたSTEPのidがデータベースに存在しないエラー)
     */
    public function should_update_step_add_sumTime_stepNumber_false_stepId_not_match_db() :void
    {
        $sumTime = $this->substep1->time_aim + $this->substep2->time_aim;

        $allSubStep = Substep::where('step_id', $this->step->id)->get();

        $stepNumber = $allSubStep->count();

        $this->assertEquals(2, $stepNumber);

        $data = [
            'time' => $sumTime,
            'stepNumber' => $stepNumber,
            'stepId' => 100,
        ];
        $response = $this->actingAs($this->user)
                        ->json('POST', route('update'), $data);
        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        $updateStep = Step::first();
        //データが更新されていないことを確認
        $this->assertEquals($updateStep->time_aim, $this->step->time_aim);
        $this->assertEquals($updateStep->step_number, $this->step->step_number);
        $this->assertEquals($updateStep->id, $this->step->id);
    }
}
