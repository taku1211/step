<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UpdateStepAndSubStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $step;
    private $substep1;
    private $substep2;
    private $substep3;

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
        $this->substep3 = Substep::factory()->create([
            'user_id' => $this->user->id,
            'step_id' => $this->step->id,
            'order' => 3
        ]);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     *
     */
    public function should_update_step_and_substep_true() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //削除したいサブSTEPが削除されていることを確認
        $deleteSubStep = Substep::where('id',$this->substep1->id)->count();
        $this->assertEquals(0, $deleteSubStep);

        //更新・追加したサブSTEPを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($subStepData[0]['title'], $updateSubStep[0]['title']);
        $this->assertEquals($subStepData[0]['content'], $updateSubStep[0]['content']);
        $this->assertEquals($subStepData[0]['time_aim'], $updateSubStep[0]['time_aim']);
        $this->assertEquals($subStepData[0]['order'], $updateSubStep[0]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[0]['user_id']);
        $this->assertEquals($subStepData[1]['title'], $updateSubStep[1]['title']);
        $this->assertEquals($subStepData[1]['content'], $updateSubStep[1]['content']);
        $this->assertEquals($subStepData[1]['time_aim'], $updateSubStep[1]['time_aim']);
        $this->assertEquals($subStepData[1]['order'], $updateSubStep[1]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[1]['user_id']);
        $this->assertEquals($subStepData[2]['title'], $updateSubStep[2]['title']);
        $this->assertEquals($subStepData[2]['content'], $updateSubStep[2]['content']);
        $this->assertEquals($subStepData[2]['time_aim'], $updateSubStep[2]['time_aim']);
        $this->assertEquals($subStepData[2]['order'], $updateSubStep[2]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[2]['user_id']);
        $this->assertEquals($subStepData[3]['title'], $updateSubStep[3]['title']);
        $this->assertEquals($subStepData[3]['content'], $updateSubStep[3]['content']);
        $this->assertEquals($subStepData[3]['time_aim'], $updateSubStep[3]['time_aim']);
        $this->assertEquals($subStepData[3]['order'], $updateSubStep[3]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[3]['user_id']);


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($this->user->id, $step->user_id);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['time_aim'], $step->time_aim);
        $this->assertEquals($data['step_number'], $step->step_number);

        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 正常系 親STEPの内容(content)・サブSTEPの内容(content)が空白
     */
    public function should_update_step_and_substep_true_content_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => null,
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => null,
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //削除したいサブSTEPが削除されていることを確認
        $deleteSubStep = Substep::where('id',$this->substep1->id)->count();
        $this->assertEquals(0, $deleteSubStep);

        //更新・追加したサブSTEPを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($subStepData[0]['title'], $updateSubStep[0]['title']);
        $this->assertEquals($subStepData[0]['content'], $updateSubStep[0]['content']);
        $this->assertEquals($subStepData[0]['time_aim'], $updateSubStep[0]['time_aim']);
        $this->assertEquals($subStepData[0]['order'], $updateSubStep[0]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[0]['user_id']);
        $this->assertEquals($subStepData[1]['title'], $updateSubStep[1]['title']);
        $this->assertEquals($subStepData[1]['content'], $updateSubStep[1]['content']);
        $this->assertEquals($subStepData[1]['time_aim'], $updateSubStep[1]['time_aim']);
        $this->assertEquals($subStepData[1]['order'], $updateSubStep[1]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[1]['user_id']);
        $this->assertEquals($subStepData[2]['title'], $updateSubStep[2]['title']);
        $this->assertEquals($subStepData[2]['content'], $updateSubStep[2]['content']);
        $this->assertEquals($subStepData[2]['time_aim'], $updateSubStep[2]['time_aim']);
        $this->assertEquals($subStepData[2]['order'], $updateSubStep[2]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[2]['user_id']);
        $this->assertEquals($subStepData[3]['title'], $updateSubStep[3]['title']);
        $this->assertEquals($subStepData[3]['content'], $updateSubStep[3]['content']);
        $this->assertEquals($subStepData[3]['time_aim'], $updateSubStep[3]['time_aim']);
        $this->assertEquals($subStepData[3]['order'], $updateSubStep[3]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[3]['user_id']);


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($this->user->id, $step->user_id);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['time_aim'], $step->time_aim);
        $this->assertEquals($data['step_number'], $step->step_number);

        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }

    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * タイトルと内容(content)、画像サイズが最大の場合
     */
    public function should_update_step_and_substep_true_maximum() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg')->size(10240);

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => str_repeat('a',255),
                'content' => str_repeat('a',500),
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => str_repeat('a',255),
                'content' => str_repeat('a',500),
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => str_repeat('a',255),
                'content' => str_repeat('a',500),
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => str_repeat('a',255),
                'content' => str_repeat('a',500),
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => str_repeat('a',255),
            'category_main' => 2,
            'category_sub' => 2,
            'content' => str_repeat('a',500),
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //削除したいサブSTEPが削除されていることを確認
        $deleteSubStep = Substep::where('id',$this->substep1->id)->count();
        $this->assertEquals(0, $deleteSubStep);

        //更新・追加したサブSTEPを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($subStepData[0]['title'], $updateSubStep[0]['title']);
        $this->assertEquals($subStepData[0]['content'], $updateSubStep[0]['content']);
        $this->assertEquals($subStepData[0]['time_aim'], $updateSubStep[0]['time_aim']);
        $this->assertEquals($subStepData[0]['order'], $updateSubStep[0]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[0]['user_id']);
        $this->assertEquals($subStepData[1]['title'], $updateSubStep[1]['title']);
        $this->assertEquals($subStepData[1]['content'], $updateSubStep[1]['content']);
        $this->assertEquals($subStepData[1]['time_aim'], $updateSubStep[1]['time_aim']);
        $this->assertEquals($subStepData[1]['order'], $updateSubStep[1]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[1]['user_id']);
        $this->assertEquals($subStepData[2]['title'], $updateSubStep[2]['title']);
        $this->assertEquals($subStepData[2]['content'], $updateSubStep[2]['content']);
        $this->assertEquals($subStepData[2]['time_aim'], $updateSubStep[2]['time_aim']);
        $this->assertEquals($subStepData[2]['order'], $updateSubStep[2]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[2]['user_id']);
        $this->assertEquals($subStepData[3]['title'], $updateSubStep[3]['title']);
        $this->assertEquals($subStepData[3]['content'], $updateSubStep[3]['content']);
        $this->assertEquals($subStepData[3]['time_aim'], $updateSubStep[3]['time_aim']);
        $this->assertEquals($subStepData[3]['order'], $updateSubStep[3]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[3]['user_id']);


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($this->user->id, $step->user_id);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['time_aim'], $step->time_aim);
        $this->assertEquals($data['step_number'], $step->step_number);

        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }

    /**
     * @test
     * STEPとサブSTEPを更新する機能（正常系 サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像はDBに登録されている状態から、今回は更新しない
     *
     */
    public function should_update_step_and_substep_true_not_update_image() :void
    {

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => null,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //削除したいサブSTEPが削除されていることを確認
        $deleteSubStep = Substep::where('id',$this->substep1->id)->count();
        $this->assertEquals(0, $deleteSubStep);

        //更新・追加したサブSTEPを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($subStepData[0]['title'], $updateSubStep[0]['title']);
        $this->assertEquals($subStepData[0]['content'], $updateSubStep[0]['content']);
        $this->assertEquals($subStepData[0]['time_aim'], $updateSubStep[0]['time_aim']);
        $this->assertEquals($subStepData[0]['order'], $updateSubStep[0]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[0]['user_id']);
        $this->assertEquals($subStepData[1]['title'], $updateSubStep[1]['title']);
        $this->assertEquals($subStepData[1]['content'], $updateSubStep[1]['content']);
        $this->assertEquals($subStepData[1]['time_aim'], $updateSubStep[1]['time_aim']);
        $this->assertEquals($subStepData[1]['order'], $updateSubStep[1]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[1]['user_id']);
        $this->assertEquals($subStepData[2]['title'], $updateSubStep[2]['title']);
        $this->assertEquals($subStepData[2]['content'], $updateSubStep[2]['content']);
        $this->assertEquals($subStepData[2]['time_aim'], $updateSubStep[2]['time_aim']);
        $this->assertEquals($subStepData[2]['order'], $updateSubStep[2]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[2]['user_id']);
        $this->assertEquals($subStepData[3]['title'], $updateSubStep[3]['title']);
        $this->assertEquals($subStepData[3]['content'], $updateSubStep[3]['content']);
        $this->assertEquals($subStepData[3]['time_aim'], $updateSubStep[3]['time_aim']);
        $this->assertEquals($subStepData[3]['order'], $updateSubStep[3]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[3]['user_id']);


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($this->user->id, $step->user_id);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['time_aim'], $step->time_aim);
        $this->assertEquals($data['step_number'], $step->step_number);
        $this->assertEquals($data['imageName'], $step->image_path);


        //ストレージにDBに保存されているもともとのファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$this->step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（正常系 サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像はDBに登録されている状態から、未登録状態に戻す
     *
     */
    public function should_update_step_and_substep_true_reset_image() :void
    {

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => null,
            'imageName' => null,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが200であることを確認
        $response->assertStatus(200);

        //削除したいサブSTEPが削除されていることを確認
        $deleteSubStep = Substep::where('id',$this->substep1->id)->count();
        $this->assertEquals(0, $deleteSubStep);

        //更新・追加したサブSTEPを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($subStepData[0]['title'], $updateSubStep[0]['title']);
        $this->assertEquals($subStepData[0]['content'], $updateSubStep[0]['content']);
        $this->assertEquals($subStepData[0]['time_aim'], $updateSubStep[0]['time_aim']);
        $this->assertEquals($subStepData[0]['order'], $updateSubStep[0]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[0]['user_id']);
        $this->assertEquals($subStepData[1]['title'], $updateSubStep[1]['title']);
        $this->assertEquals($subStepData[1]['content'], $updateSubStep[1]['content']);
        $this->assertEquals($subStepData[1]['time_aim'], $updateSubStep[1]['time_aim']);
        $this->assertEquals($subStepData[1]['order'], $updateSubStep[1]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[1]['user_id']);
        $this->assertEquals($subStepData[2]['title'], $updateSubStep[2]['title']);
        $this->assertEquals($subStepData[2]['content'], $updateSubStep[2]['content']);
        $this->assertEquals($subStepData[2]['time_aim'], $updateSubStep[2]['time_aim']);
        $this->assertEquals($subStepData[2]['order'], $updateSubStep[2]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[2]['user_id']);
        $this->assertEquals($subStepData[3]['title'], $updateSubStep[3]['title']);
        $this->assertEquals($subStepData[3]['content'], $updateSubStep[3]['content']);
        $this->assertEquals($subStepData[3]['time_aim'], $updateSubStep[3]['time_aim']);
        $this->assertEquals($subStepData[3]['order'], $updateSubStep[3]['order']);
        $this->assertEquals($this->user->id, $updateSubStep[3]['user_id']);


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($this->user->id, $step->user_id);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['time_aim'], $step->time_aim);
        $this->assertEquals($data['step_number'], $step->step_number);
        $this->assertEquals($data['imageName'], $step->image_path);

    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのタイトルが空白
     */
    public function should_update_step_and_substep_false_main_step_title_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => null,
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのタイトルが255文字を超過している
     */
    public function should_update_step_and_substep_false_main_step_title_over_255_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => str_repeat('a',256),
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPの内容(content)が500文字を超過している
     */
    public function should_update_step_and_substep_false_main_step_content_over_500_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => str_repeat('a',501),
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 メインカテゴリー未選択
     */
    public function should_update_step_and_substep_false_main_step_main_category_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => null,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 メインカテゴリーに数値以外が入力されている
     */
    public function should_update_step_and_substep_false_main_step_main_category_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 'a',
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブカテゴリーが空白
     */
    public function should_update_step_and_substep_false_main_step_sub_category_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => null,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブカテゴリーに数値以外が入力されている
     */
    public function should_update_step_and_substep_false_main_step_sub_category_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 'a',
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 画像サイズの超過
     */
    public function should_update_step_and_substep_false_main_step_image_over_size() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg')->size(10241);

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 画像ではないタイプが選択されている(pdfなど)
     */
    public function should_update_step_and_substep_false_main_step_image_not_image_type() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('update_image.pdf');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
        /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのtime_aimがnull
     */
    public function should_update_step_and_substep_false_main_step_time_aim_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => null,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのtime_aimが数字以外
     */
    public function should_update_step_and_substep_false_main_step_time_aim_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 'a',
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのtime_aimが0以上ではない
     */
    public function should_update_step_and_substep_false_main_step_time_aim_not_positive_number() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => -30,
            'step_number' => '4',
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのstep_numberが空白
     */
    public function should_update_step_and_substep_false_main_step_step_number_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => null,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのstep_numberが数字以外
     */
    public function should_update_step_and_substep_false_main_step_step_number_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 'a',
            'step_number' => null,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 親STEPのstep_numberが0以上ではない
     */
    public function should_update_step_and_substep_false_main_step_step_not_positive_number() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => -1,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPのタイトルが空白
     */
    public function should_update_step_and_substep_false_sub_step_title_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => null,
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPのタイトルが255文字を超過
     */
    public function should_update_step_and_substep_false_sub_step_title_over_255_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => str_repeat('a', 256),
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPの内容(content)が500文字を超過
     */
    public function should_update_step_and_substep_false_sub_step_content_over_500_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => str_repeat('a', 501),
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPの達成時間が未選択（空白）
     */
    public function should_update_step_and_substep_false_sub_step_time_aim_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => null,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPの達成時間が数値ではない
     */
    public function should_update_step_and_substep_false_sub_step_time_aim_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 'a',
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPの達成時間が負の数
     */
    public function should_update_step_and_substep_false_sub_step_time_aim_not_positive_number() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => -1,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPのorderが空白
     */
    public function should_update_step_and_substep_false_sub_step_order_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => null,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPのorderが数値ではない
     */
    public function should_update_step_and_substep_false_sub_step_order_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 'a',
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 サブSTEPのorderが0以上ではない
     */
    public function should_update_step_and_substep_false_sub_step_order_not_positive_number() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => -1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 更新したいメインSTEPがDBに存在しない
     */
    public function should_update_step_and_substep_false_main_step_not_exist() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => 100,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[{$this->substep1->id}]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが404であることを確認
        $response->assertStatus(404);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
    /**
     * @test
     * STEPとサブSTEPを更新する機能（サブSTEPはorder1のデータを削除し、新たに二つデータを追加する）
     * また、画像も新たに更新する
     * 異常系 削除したいサブSTEPがDBに存在しない
     */
    public function should_update_step_and_substep_false_will_delete_sub_step_not_exist() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('update_image.jpg');

        $subStepData = [
            0 =>
            [
                'id' => $this->substep2->id,
                'user_id' => $this->substep2->user_id,
                'step_id' => $this->substep2->step_id,
                'title' => 'update title 2',
                'content' => 'update content 2',
                'time_aim' => 180,
                'order' => 1,
            ],
            1 =>
            [
                'id' => $this->substep3->id,
                'user_id' => $this->substep3->user_id,
                'step_id' => $this->substep3->step_id,
                'title' => 'update title 3',
                'content' => 'update content 3',
                'time_aim' => 60,
                'order' => 2,
            ],
            2 =>
            [
                'id' => null,
                'title' => 'update title 4',
                'content' => 'update content 4',
                'time_aim' => 60,
                'order' => 3,
            ],
            3 =>
            [
                'id' => null,
                'title' => 'update title 5',
                'content' => 'update content 5',
                'time_aim' => 60,
                'order' => 4,
            ]
        ];
        $jsonData = json_encode($subStepData);


        $data = [
            'id' => $this->step->id,
            'user_id' => $this->step->user_id,
            'title' => 'update title',
            'category_main' => 2,
            'category_sub' => 2,
            'content' => 'update content',
            'time_aim' => 360,
            'step_number' => 4,
            'image' => $file,
            'imageName' => $this->step->image_path,
            'subStepForm' => $jsonData,
            'deletedSubStep' => "[100,200]",
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('edit'), $data);
        //レスポンスステータスが500であることを確認
        $response->assertStatus(500);

        //サブSTEPが更新されていないことを確認
        $updateSubStep = Substep::where('step_id', $this->step->id)->get();
        $this->assertEquals($this->substep1->title, $updateSubStep[0]['title']);
        $this->assertEquals($this->substep1->content, $updateSubStep[0]['content']);
        $this->assertEquals($this->substep1->time_aim, $updateSubStep[0]['time_aim']);
        $this->assertEquals($this->substep1->order, $updateSubStep[0]['order']);
        $this->assertEquals($this->substep1->user_id, $updateSubStep[0]['user_id']);
        $this->assertEquals($this->substep2->title, $updateSubStep[1]['title']);
        $this->assertEquals($this->substep2->content, $updateSubStep[1]['content']);
        $this->assertEquals($this->substep2->time_aim, $updateSubStep[1]['time_aim']);
        $this->assertEquals($this->substep2->order, $updateSubStep[1]['order']);
        $this->assertEquals($this->substep2->user_id, $updateSubStep[1]['user_id']);
        $this->assertEquals($this->substep3->title, $updateSubStep[2]['title']);
        $this->assertEquals($this->substep3->content, $updateSubStep[2]['content']);
        $this->assertEquals($this->substep3->time_aim, $updateSubStep[2]['time_aim']);
        $this->assertEquals($this->substep3->order, $updateSubStep[2]['order']);
        $this->assertEquals($this->substep3->user_id, $updateSubStep[2]['user_id']);

        //サブSTEPの件数が元の数のままであることを確認
        $this->assertEquals(3, count($updateSubStep));


        //親のSTEPを確認
        $step = Step::first();
        $this->assertEquals($this->step->title, $step->title);
        $this->assertEquals($this->step->user_id, $step->user_id);
        $this->assertEquals($this->step->category_main, $step->category_main);
        $this->assertEquals($this->step->category_sub, $step->category_sub);
        $this->assertEquals($this->step->content, $step->content);
        $this->assertEquals($this->step->time_aim, $step->time_aim);
        $this->assertEquals($this->step->step_number, $step->step_number);
        $this->assertEquals($this->step->image_path, $step->image_path);
    }
}
