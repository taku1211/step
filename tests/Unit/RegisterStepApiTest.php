<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Step;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class RegisterStepApiTest extends TestCase
{
    use RefreshDatabase;
    private $user;

    public function setUp() :void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    /**
     * @test
     * STEP登録機能テスト(正常系)
     */
    public function should_register_step_true() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample step',
            'category_main' => 1,
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが201であることを確認
        $response->assertStatus(201);
        $step = Step::first();
        //データが正常に登録されているかを確認
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($step->time_aim, 0);
        $this->assertEquals($step->step_number, 0);
        $this->assertEquals($step->user_id, $this->user->id);


        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }
    /**
     * @test
     * STEP登録機能テスト(正常系 内容(content)が空白)
     */
    public function should_register_step_true_content_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample step',
            'category_main' => 1,
            'category_sub' => 1,
            'content' => null,
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが201であることを確認
        $response->assertStatus(201);
        $step = Step::first();
        //データが正常に登録されているかを確認
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($step->time_aim, 0);
        $this->assertEquals($step->step_number, 0);
        $this->assertEquals($step->user_id, $this->user->id);

        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }
    /**
     * @test
     * STEP登録機能テスト(正常系 画像未登録)
     */
    public function should_register_step_true_image_blank() :void
    {
        Storage::fake('local');

        $data = [
            'title' => 'sample step',
            'category_main' => 1,
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => null
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが201であることを確認
        $response->assertStatus(201);
        $step = Step::first();
        //データが正常に登録されているかを確認
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($data['image'], $step->image_path);
        $this->assertEquals($step->time_aim, 0);
        $this->assertEquals($step->step_number, 0);
        $this->assertEquals($step->user_id, $this->user->id);


        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(正常系 タイトル・内容が文字数最大 かつ画像サイズも最大)
     */
    public function should_register_step_true_maximum_title_content_image() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg')->size(10240);

        $data = [
            'title' => str_repeat('a',255),
            'category_main' => 1,
            'category_sub' => 1,
            'content' => str_repeat('a',500),
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが201であることを確認
        $response->assertStatus(201);
        $step = Step::first();
        //データが正常に登録されているかを確認
        $this->assertEquals($data['title'], $step->title);
        $this->assertEquals($data['category_main'], $step->category_main);
        $this->assertEquals($data['category_sub'], $step->category_sub);
        $this->assertEquals($data['content'], $step->content);
        $this->assertEquals($step->time_aim, 0);
        $this->assertEquals($step->step_number, 0);
        $this->assertEquals($step->user_id, $this->user->id);

        //保存したイメージ画像が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $step->image_path);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$step->image_path);
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 タイトル空白によるエラー)
     */
    public function should_register_step_false_title_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => str_repeat('a',256),
            'category_main' => 1,
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 タイトル空白によるエラー)
     */
    public function should_register_step_false_title_over_255words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => '',
            'category_main' => 1,
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 メインカテゴリー空白エラー)
     */
    public function should_register_step_false_category_main_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample title',
            'category_main' => null,
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 メインカテゴリー数字以外が入力されたためエラー)
     */
    public function should_register_step_false_category_main_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample title',
            'category_main' => 'a',
            'category_sub' => 1,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 サブカテゴリー空白エラー)
     */
    public function should_register_step_false_category_sub_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample title',
            'category_main' => 1,
            'category_sub' => null,
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 サブカテゴリー数字以外が入力されたためエラー)
     */
    public function should_register_step_false_category_sub_not_int() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample title',
            'category_main' => 1,
            'category_sub' => 'a',
            'content' => 'samplesamplesample',
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 内容(content)の最大文字数超過エラー)
     */
    public function should_register_step_false_content_over_255words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [
            'title' => 'sample title',
            'category_main' => 1,
            'category_sub' => 'a',
            'content' => str_repeat('a',501),
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 画像サイズ超過エラー)
     */
    public function should_register_step_false_image_over_size() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg')->size(10241);

        $data = [
            'title' => 'sample title',
            'category_main' => 1,
            'category_sub' => 'a',
            'content' => str_repeat('a',501),
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * STEP登録機能テスト(異常系 ファイル形式が違うためエラー)
     */
    public function should_register_step_false_image_invalid_type() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('image.pdf');

        $data = [
            'title' => 'sample title',
            'category_main' => 1,
            'category_sub' => 'a',
            'content' => str_repeat('a',501),
            'image' => $file
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('create'), $data);
        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);
        $countStep = Step::count();
        //データが正常に登録されているかを確認
        $this->assertEquals(0, $countStep);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
}
