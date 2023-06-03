<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;

class RegisterSubStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;

    public function setUp() :void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->step = Step::factory()->create(['user_id' => $this->user->id]);
    }
    /**
     * @test
     * サブSTEP登録機能テスト(正常系)
     */
    public function should_register_sub_step_true() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesample',
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認
        $this->assertEquals($data[0]['subTitle'], $subSteps[0]['title']);
        $this->assertEquals($data[0]['subContent'], $subSteps[0]['content']);
        $this->assertEquals($data[0]['subTime'], $subSteps[0]['time_aim']);
        $this->assertEquals($data[0]['order'], $subSteps[0]['order']);
        $this->assertEquals($this->user->id, $subSteps[0]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[0]['step_id']);
        $this->assertEquals($data[1]['subTitle'], $subSteps[1]['title']);
        $this->assertEquals($data[1]['subContent'], $subSteps[1]['content']);
        $this->assertEquals($data[1]['subTime'], $subSteps[1]['time_aim']);
        $this->assertEquals($data[1]['order'], $subSteps[1]['order']);
        $this->assertEquals($this->user->id, $subSteps[1]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[1]['step_id']);

        //登録されているデータの個数を確認
        $this->assertEquals(2, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(正常系 タイトル・内容(subContent)が最大文字数)
     */
    public function should_register_sub_step_true_maximum_subTitle_subContent() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => str_repeat('a',255),
              'subContent' => str_repeat('a',500),
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認
        $this->assertEquals($data[0]['subTitle'], $subSteps[0]['title']);
        $this->assertEquals($data[0]['subContent'], $subSteps[0]['content']);
        $this->assertEquals($data[0]['subTime'], $subSteps[0]['time_aim']);
        $this->assertEquals($data[0]['order'], $subSteps[0]['order']);
        $this->assertEquals($this->user->id, $subSteps[0]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[0]['step_id']);
        $this->assertEquals($data[1]['subTitle'], $subSteps[1]['title']);
        $this->assertEquals($data[1]['subContent'], $subSteps[1]['content']);
        $this->assertEquals($data[1]['subTime'], $subSteps[1]['time_aim']);
        $this->assertEquals($data[1]['order'], $subSteps[1]['order']);
        $this->assertEquals($this->user->id, $subSteps[1]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[1]['step_id']);

        //登録されているデータの個数を確認
        $this->assertEquals(2, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(正常系 内容(subContent)が空白)
     */
    public function should_register_sub_step_true_subContent_blank() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => null,
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認
        $this->assertEquals($data[0]['subTitle'], $subSteps[0]['title']);
        $this->assertEquals($data[0]['subContent'], $subSteps[0]['content']);
        $this->assertEquals($data[0]['subTime'], $subSteps[0]['time_aim']);
        $this->assertEquals($data[0]['order'], $subSteps[0]['order']);
        $this->assertEquals($this->user->id, $subSteps[0]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[0]['step_id']);
        $this->assertEquals($data[1]['subTitle'], $subSteps[1]['title']);
        $this->assertEquals($data[1]['subContent'], $subSteps[1]['content']);
        $this->assertEquals($data[1]['subTime'], $subSteps[1]['time_aim']);
        $this->assertEquals($data[1]['order'], $subSteps[1]['order']);
        $this->assertEquals($this->user->id, $subSteps[1]['user_id']);
        $this->assertEquals($this->step->id, $subSteps[1]['step_id']);

        //登録されているデータの個数を確認
        $this->assertEquals(2, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 タイトルが空白)
     */
    public function should_register_sub_step_false_subTitle_blank() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => null,
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 タイトルが文字数超過)
     */
    public function should_register_sub_step_false_subTitle_over_255words() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => str_repeat('a',256),
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 内容(subContent)が文字数超過)
     */
    public function should_register_sub_step_false_subContent_over_500words() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => str_repeat('a',501),
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 時間が未選択)
     */
    public function should_register_sub_step_false_subTime_blank() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => null,
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 時間に文字が入っているためエラー)
     */
    public function should_register_sub_step_false_subTime_not_int() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => 'a',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 時間に負の数が入っているためエラー)
     */
    public function should_register_sub_step_false_subTime_not_positive_number() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => -30,
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 順番が空白によるエラー)
     */
    public function should_register_sub_step_false_order_blank() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => null,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 順番に文字が入力されていることによるエラー)
     */
    public function should_register_sub_step_false_order_not_int() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => 'a',
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 順番に負の数が入力されていることによるエラー)
     */
    public function should_register_sub_step_false_order_not_positive_number() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => -1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('createSub'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
    /**
     * @test
     * サブSTEP登録機能テスト(異常系 未ログインによる認証エラー)
     */
    public function should_register_sub_step_false_not_login() :void
    {
        $data = [
            0 =>
            [
              'subTitle' => 'sample title1',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '60',
              'order' => 1,
            ],
            1 =>
            [
              'subTitle' => 'sample title2',
              'subContent' => 'samplesamplesamplesample',
              'subTime' => '90',
              'order' => 2,
            ],
        ];
        $response = $this->json('POST', route('createSub'), $data);
        //レスポンスステータスが401であることを確認
        $response->assertStatus(401);

        $subSteps = SubStep::all();
        //データが正常に登録されているかを確認

        //登録されているデータの個数が0件であることを確認
        $this->assertEquals(0, $subSteps->count());
    }
}
