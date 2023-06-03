<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use League\Flysystem\ConnectionRuntimeException;

class UpdateUserApiTest extends TestCase
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
     * ユーザー情報更新機能テスト(正常系)
     */
    public function should_update_user_true() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 画像を登録後、別の画像で登録できる)
     */
    public function should_update_user_true_icon_update() :void
    {
        //一度、画像を含めて$userを更新
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);


        //画像だけ再度更新する
        $anotherFile = UploadedFile::fake()->image('icon.jpg');
        $anotherData = [
            'email' => $updateUser->email,
            'icon' => $anotherFile,
            'introduction' => $updateUser->introduction,
        ];
        $response = $this->actingAs($updateUser)
                         ->json('POST', route('updateUser'), $anotherData);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $latestUser = User::first();
        //Email・自己紹介は更新されていないことを確認
        $this->assertEquals($latestUser->email, $updateUser->email);
        $this->assertEquals($latestUser->introduction, $updateUser->introduction);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $latestUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$latestUser->icon);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 画像なしで更新)
     */
    public function should_update_user_true_no_icon() :void
    {
        $data = [
            'email' => 'dummy@gmail.com',
            'introduction' => 'samplesamplesamplesample',
            'icon' => $this->user->icon,
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $updateUser = User::first();
        $response->assertStatus(200);
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($updateUser->icon, $this->user->icon);
        $this->assertEquals($data['email'], $updateUser->email);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 画像を登録後、画像を空で登録する)
     */
    public function should_update_user_true_icon_update_blank() :void
    {
        //一度、画像を含めて$userを更新
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);
        //ストレージに保存されているデータが1つであることを確認
        $this->assertEquals(1, count(Storage::disk('local')->files('public')));


        //画像だけ再度更新する
        $anotherData = [
            'email' => $updateUser->email,
            'icon' => '',
            'introduction' => $updateUser->introduction,
        ];
        $response = $this->actingAs($updateUser)
                         ->json('POST', route('updateUser'), $anotherData);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $latestUser = User::first();
        //Email・自己紹介は更新されていないことを確認
        $this->assertEquals($latestUser->email, $updateUser->email);
        $this->assertEquals($latestUser->introduction, $updateUser->introduction);

        $this->assertEquals($latestUser->icon, $anotherData['icon']);

        //ストレージに保存されているデータが1つであることを確認(最初に登録した画像のみ)
        $this->assertEquals(1, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 自己紹介なしで更新)
     */
    public function should_update_user_true_no_introduction() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => $this->user->introduction,
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($this->user->introduction, $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 自己紹介最大文字数500文字で更新)
     */
    public function should_update_user_true_introduction_500_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => str_repeat('a',500),
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(正常系 email最大文字数255文字で更新)
     */
    public function should_update_user_true_email_255_words() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => str_repeat('a',245).'@gmail.com',//aが245文字+それ以降10文字で256文字
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが200OKであることを確認
        $response->assertStatus(200);
        $updateUser = User::first();
        $this->assertEquals($data['introduction'], $updateUser->introduction);
        $this->assertEquals($data['email'], $updateUser->email);

        //保存したアイコン名が12桁のランダムな文字列であることを確認
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z-_]{12}.jpg$/', $updateUser->icon);

        //ストレージにDBに保存したファイル名のファイルが保存されていることを確認
        Storage::disk('local')->assertExists('public/'.$updateUser->icon);
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 画像形式ではないためエラー)
     */
    public function should_update_user_false_icon_invalid_type() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('icon.pdf');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 画像サイズの超過エラー)
     */
    public function should_update_user_false_icon_size_over() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg')->size(10241);
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 メールアドレス空白エラー)
     */
    public function should_update_user_false_email_blank() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => '',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 自己紹介最大文字数超過エラー)
     */
    public function should_update_user_false_introduction_over_maximum_500word() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => str_repeat('a',501),
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 Email重複エラー)
     */
    public function should_update_user_false_email_over_maximum_255word() :void
    {
        $anotherUser = User::factory()->create();//2番目のデータとして別のユーザーを作成

        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => $anotherUser->email,
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 Email欄256文字による文字数超過バリデーションエラー)
     */
    public function should_update_user_false_email_not_unique() :void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => str_repeat('a',246).'@gmail.com',//aが246文字+それ以降10文字で256文字
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが422であることを確認
        $response->assertStatus(422);

        //データが更新されていないことを確認
        $dbUser = User::first();
        $this->assertEquals($dbUser->email, $this->user->email);
        $this->assertEquals($dbUser->introduction, $this->user->introduction);
        $this->assertEquals($dbUser->icon, $this->user->icon);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 画像が保存できなかった場合)
     */
    public function should_update_user_false_file_save_error() :void
    {
        Storage::shouldReceive('putFileAs')
            ->once()
            ->andThrow(new ConnectionRuntimeException('接続失敗'));

        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが500エラーであることを確認
        $response->assertStatus(500);

        $dbUser = User::first();
        $this->assertEquals($dbUser->icon, "");
    }
    /**
     * @test
     * ユーザー情報更新機能テスト(異常系 データベースエラー)
     */
    public function should_update_user_false_database_error() :void
    {
        //DBの全テーブルを削除して、DBエラーを発生させる
        Schema::drop('challenges');
        Schema::drop('substeps');
        Schema::drop('steps');
        Schema::drop('users');

        Storage::fake('local');
        $file = UploadedFile::fake()->image('icon.jpg');
        $data = [
            'email' => 'dummy@gmail.com',
            'icon' => $file,
            'introduction' => 'samplesamplesamplesample',
        ];
        $response = $this->actingAs($this->user)
                         ->json('POST', route('updateUser'), $data);

        //レスポンスステータスが500エラーであることを確認
        $response->assertStatus(500);

        //ストレージにファイルが保存されていないことを確認
        $this->assertEquals(0, count(Storage::disk('local')->files('public')));

        //削除したデータベースを再度マイグレーション
        $this->artisan('migrate:fresh');
    }

}
