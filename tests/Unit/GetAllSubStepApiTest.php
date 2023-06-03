<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Substep;
use App\Models\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;


class GetAllSubStepApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Substep::factory()->count(5)->for(Step::factory())->create();
    }
    /**
     * @test
     * 登録されているサブSTEPをすべて取得する（正常系）
     */
    public function should_get_all_sub_steps_true() :void
    {
        $response = $this->json('GET', route('indexsubSteps'));
        $response->assertStatus(200);

        $data = $response->getData();

        //上記で取得したデータが正しいかを確認するため、テストでもデータを取得
        $subStep = Substep::with(['step'])->get();

        //データの取得件数が一致していることを確認
        $this->assertEquals(count($data), count($subStep));

        //取得したデータの中身とリレーション先のSTEPが取得できており、
        //テスト内で取得したデータと一致することを確認
        for($i=0;$i<5;$i++){
            $this->assertEquals($data[$i]->id, $subStep[$i]->id);
            $this->assertEquals($data[$i]->title, $subStep[$i]->title);
            $this->assertEquals($data[$i]->content, $subStep[$i]->content);
            $this->assertEquals($data[$i]->time_aim, $subStep[$i]->time_aim);
            $this->assertEquals($data[$i]->order, $subStep[$i]->order);
            $this->assertEquals($data[$i]->step->id, $subStep[$i]->step->id);
        }

    }
}
