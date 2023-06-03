<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordResetNotification;


class ResetPasswordApiTest extends TestCase
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
     * パスワードリセット用のメール送信機能テスト(正常系)
     */
    public function should_send_reset_password_mail_true() :void
    {
        $this->assertGuest();

        $response = $this->json('POST', route('sendResetLinkEmail'),[
            'email' => $this->user->email,
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(['message' => 'パスワード再設定メールを送信しました', 'status' => true]);
    }
    /**
     * @test
     * パスワードリセット用のメール送信機能テスト(異常系：データベースのメールアドレスと一致しないエラー)
     */
    public function should_send_reset_password_mail_false() :void
    {
        $this->assertGuest();

        $response = $this->json('POST', route('sendResetLinkEmail'),[
            'email' => 'no.register.email@gmail.com',
        ]);
        $response
            ->assertStatus(401)
            ->assertJson(['message' => 'パスワード再設定メールを送信できませんでした。入力したメールアドレスを確認してください。', 'status' => false]);
    }
    /**
     * @test
     * パスワード再更新機能テスト(正常系)
     */
    //public function should_reset_password_true()
    // {
    //     Notification::fake();
    //     $user = $this->user;

    //     $this->json('POST', route('sendResetLinkEmail'),[
    //         'email' => $user->email,
    //     ]);

    //     $notification = $this->expectsNotification($user, PasswordResetNotification::class);

    //     $newPassword = "reset1234";
    //     $token = $notification;

    //     $response = $this->json('POST', route('resetPassword'),[
    //         'email' => $user->email,
    //         'password' => $newPassword,
    //         'password_confirmation' => $newPassword,
    //         'token' => $token,
    //     ]);

    //     $response
    //         ->assertStatus(200)
    //         ->assertJson(['message' => 'パスワードの再設定が完了しました。', 'status' => true]);
    // }


}
