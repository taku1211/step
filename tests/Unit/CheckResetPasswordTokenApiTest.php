<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordResetNotification;

class CheckResetPasswordTokenApiTest extends TestCase
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
     * パスワードリセットページ表示機能テスト(正常系)
     */
    public function check_password_reset_page_show_true() :void
    {
        $this->assertGuest('web');
        Notification::fake();
        $user = $this->user;
        $token = $this->passwordRequest($user);

        $this->json('POST', route('sendResetLinkEmail'),[
            'email' => $user->email,
        ]);

        //発光されたトークンを所有した状態で、トークンの確認を行う
        $response = $this->json('POST', route('checkToken'),[
            'token' => $token,
        ]);

        //トークンを所有しているため200OKが返却されることを確認
        $response->assertStatus(200);
    }
    /**
     * @test
     * パスワードリセットページ表示機能テスト(異常系 トークンが一致しない)
     */
    public function check_password_reset_page_show_false() :void
    {
        $this->assertGuest('web');
        Notification::fake();
        $user = $this->user;
        $token = $this->passwordRequest($user);

        $this->json('POST', route('sendResetLinkEmail'),[
            'email' => $user->email,
        ]);

        //発光されたトークンを所有した状態で、トークンの確認を行う
        $response = $this->json('POST', route('checkToken'),[
            'token' => str_repeat('token',100),
        ]);

        //トークンを所有しているため200OKが返却されることを確認
        $response->assertStatus(401)
        ->assertJson(['message' => 'URLの有効期限が切れています。再度、パスワード再発行メールを送信してください。', 'status' => false]);

    }
    private function passwordRequest(User $user)
    {
        // パスワードリセットをリクエスト（トークンを作成・取得するため）
        $this->json('POST', route('sendResetLinkEmail'),[
            'email' => $user->email,
        ]);

        // トークンを取得する
        $token = '';

        Notification::assertSentTo(
            $user,
            PasswordResetNotification::class,
            function ($notification, $channels) use ($user, &$token) {
                $token = $notification->token;
                return true;
            }
        );
        return $token;
    }

}
