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
     * パスワード再更新機能テスト(正常系 メールアドレスとトークンで認証成功)
     */
    public function should_reset_password_true()
     {
        $this->assertGuest('web');
          Notification::fake();
          $user = $this->user;
          $token = $this->passwordRequest($user);

          $this->json('POST', route('sendResetLinkEmail'),[
              'email' => $user->email,
          ]);


          $newPassword = "reset1234";

          $response = $this->json('POST', route('resetPassword'),[
              'email' => $user->email,
              'password' => $newPassword,
              'password_confirmation' => $newPassword,
              'token' => $token,
          ]);

          $response
              ->assertStatus(200)
              ->assertJson(['message' => 'パスワードの再設定が完了しました。', 'status' => true]);
     }
    /**
     * @test
     * パスワード再更新機能テスト(異常系 トークンがDBに保存されているデータと一致しない)
     */
    public function should_reset_password_false_for_invalid_token()
     {
        $this->assertGuest('web');
          Notification::fake();
          $user = $this->user;
          $token = $this->passwordRequest($user);

          $this->json('POST', route('sendResetLinkEmail'),[
              'email' => $user->email,
          ]);


          $newPassword = "reset1234";

          $response = $this->json('POST', route('resetPassword'),[
              'email' => $user->email,
              'password' => $newPassword,
              'password_confirmation' => $newPassword,
              'token' => $newPassword,
          ]);

          $response
              ->assertStatus(401)
              ->assertJson(['message' => 'パスワードの再設定に失敗しました。再度、パスワード再発行メールを送信してください。', 'status' => false]);
     }
     /**
     * @test
     * パスワード再更新機能テスト(異常系 メールアドレスがDBに保存されているデータと一致しない)
     */
    public function should_reset_password_false_for_invalid_email()
    {
       $this->assertGuest('web');
         Notification::fake();
         $user = $this->user;
         $token = $this->passwordRequest($user);

         $this->json('POST', route('sendResetLinkEmail'),[
             'email' => $user->email,
         ]);


         $newPassword = "reset1234";

         $response = $this->json('POST', route('resetPassword'),[
             'email' => 'no.register.email@gmail.com',
             'password' => $newPassword,
             'password_confirmation' => $newPassword,
             'token' => $token,
         ]);

         $response
             ->assertStatus(404)
             ->assertJson(['message' => '予期せぬエラーが発生しました。ご迷惑をおかけしますが、しばらく時間を置いてから再度実施してください。', 'status' => false]);
    }
     /**
     * @test
     * パスワード再更新機能テスト(異常系 メールアドレスがメールアドレス形式ではない)
     */
    public function should_reset_password_false_not_match_email_type()
    {
       $this->assertGuest('web');
         Notification::fake();
         $user = $this->user;
         $token = $this->passwordRequest($user);

         $this->json('POST', route('sendResetLinkEmail'),[
             'email' => $user->email,
         ]);


         $newPassword = "reset1234";

         $response = $this->json('POST', route('resetPassword'),[
             'email' => str_repeat('a',255),
             'password' => $newPassword,
             'password_confirmation' => $newPassword,
             'token' => $token,
         ]);

         $response->assertStatus(422);
    }
     /**
     * @test
     * パスワード再更新機能テスト(異常系 メールアドレスが256文字以上)
     */
    public function should_reset_password_false_email_over_255_words()
    {
       $this->assertGuest('web');
         Notification::fake();
         $user = $this->user;
         $token = $this->passwordRequest($user);

         $this->json('POST', route('sendResetLinkEmail'),[
             'email' => $user->email,
         ]);


         $newPassword = "reset1234";

         $response = $this->json('POST', route('resetPassword'),[
             'email' => str_repeat('a',246).'@gmail.com',//aが246文字+それ以降10文字で256文字
             'password' => $newPassword,
             'password_confirmation' => $newPassword,
             'token' => $token,
         ]);

         $response->assertStatus(422);
    }
    /**
     * @test
     * パスワード再更新機能テスト(異常系 パスワードのバリデーションエラー(文字数が7文字以下))
     */
    public function should_reset_password_false_for_invalid_new_password()
    {
       $this->assertGuest('web');
         Notification::fake();
         $user = $this->user;
         $token = $this->passwordRequest($user);

         $this->json('POST', route('sendResetLinkEmail'),[
             'email' => $user->email,
         ]);


         $newPassword = "reset12";

         $response = $this->json('POST', route('resetPassword'),[
             'email' => $user->email,
             'password' => $newPassword,
             'password_confirmation' => $newPassword,
             'token' => $token,
         ]);

         $response->assertStatus(422);
    }
    /**
     * @test
     * パスワード再更新機能テスト(異常系 パスワードのバリデーションエラー(パスワード確認と一致しない))
     */
    public function should_reset_password_false_not_maych_password_confirmation()
    {
       $this->assertGuest('web');
         Notification::fake();
         $user = $this->user;
         $token = $this->passwordRequest($user);

         $this->json('POST', route('sendResetLinkEmail'),[
             'email' => $user->email,
         ]);


         $newPassword = "reset1234";

         $response = $this->json('POST', route('resetPassword'),[
             'email' => $user->email,
             'password' => $newPassword,
             'password_confirmation' => "reset123",
             'token' => $token,
         ]);

         $response->assertStatus(422);
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
