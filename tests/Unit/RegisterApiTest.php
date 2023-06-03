<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;


class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * ユーザー登録機能（正常系）
     */
     public function sholud_register_true()
     {
        $data = [
            'email' => 'dummy@gmail.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];

        $response = $this->json('POST', route('register'), $data);

        $user = User::first();
        $this->assertEquals($data['email'], $user->email);

        $response
            ->assertStatus(201)
            ->assertJson(['email' => $user->email]);
     }

     /**
     * @test
     * ユーザー登録機能（異常系:Email欄空白によるバリデーションエラー）
     */
     public function sholud_register_false_email_blank()
     {
        $data = [
            'email' => '',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];

        $response = $this->json('POST', route('register'), $data);

        $response->assertStatus(422);
     }
     /**
     * @test
     * ユーザー登録機能（異常系:Email欄256文字による文字数超過バリデーションエラー）
     */
     public function sholud_register_false_email_over_maximum_255word()
     {
        $data = [
            'email' => str_repeat('a',246).'@gmail.com',//aが246文字+それ以降10文字で256文字
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ];

        $response = $this->json('POST', route('register'), $data);

        $response->assertStatus(422);
     }
     /**
     * @test
     * ユーザー登録機能（異常系:Email形式ではないためのバリデーションエラー）
     */
    public function sholud_register_false_email_not_email_type()
    {
       $data = [
           'email' => 'aaaaaaaaaa',
           'password' => 'test1234',
           'password_confirmation' => 'test1234',
       ];

       $response = $this->json('POST', route('register'), $data);

       $response->assertStatus(422);
    }
    /**
     * @test
     * ユーザー登録機能（異常系:Email重複バリデーションエラー）
     */
    public function sholud_register_false_email_not_unique()
    {
        $user = User::factory()->create();

       $data = [
        'email' => $user->email,
        'password' => 'test5678',
        'password_confirmation' => 'test5678',
       ];
       $response = $this->json('POST', route('register'), $data);
       $response->assertStatus(422);
    }
    /**
     * @test
     * ユーザー登録機能（異常系:パスワード欄空白によるバリデーションエラー）
     */
    public function sholud_register_false_password_blank()
    {
       $data = [
           'email' => 'dummy@gmail.com',
           'password' => '',
           'password_confirmation' => 'test1234',
       ];

       $response = $this->json('POST', route('register'), $data);

       $response->assertStatus(422);
    }
    /**
     * @test
     * ユーザー登録機能（異常系:パスワード再入力欄空白によるバリデーションエラー）
     */
    public function sholud_register_false_password_confirmation_blank()
    {
       $data = [
           'email' => 'dummy@gmail.com',
           'password' => 'test1234',
           'password_confirmation' => '',
       ];

       $response = $this->json('POST', route('register'), $data);

       $response->assertStatus(422);
    }
    /**
     * @test
     * ユーザー登録機能（異常系:パスワード・パスワード再入力欄不一致によるバリデーションエラー）
     */
    public function sholud_register_false_password_not_equal_confirmation()
    {
       $data = [
           'email' => 'dummy@gmail.com',
           'password' => 'test1234',
           'password_confirmation' => 'test1235',
       ];

       $response = $this->json('POST', route('register'), $data);

       $response->assertStatus(422);
    }
    /**
     * @test
     * ユーザー登録機能（異常系:パスワード7文字のための最小文字数バリデーションエラー）
     */
    public function sholud_register_false_password_less_min_8words()
    {
       $data = [
           'email' => 'dummy@gmail.com',
           'password' => 'test123',
           'password_confirmation' => 'test123',
       ];

       $response = $this->json('POST', route('register'), $data);

       $response->assertStatus(422);
    }

}
