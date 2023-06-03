<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;


class AuthenticationApiTest extends TestCase
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
     * ログイン機能テスト(正常系)
     */
    public function should_login_true() :void
    {

        $response = $this->json('POST', route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['email' => $this->user->email]);

        $this->assertAuthenticatedAs($this->user);
    }
    /**
     * @test
     * ログイン機能テスト(異常系：メールアドレス不一致による認証エラー)
     */
    public function should_login_false_email_invalid()
    {

        $response = $this->json('POST', route('login'), [
            'email' => 'sample@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }
    /**
     * @test
     * ログイン機能テスト(異常系：パスワード不一致による認証エラー)
     */
    public function should_login_false_password_invalid() :void
    {

        $response = $this->json('POST', route('login'), [
            'email' => $this->user->email,
            'password' => 'aaaaaaaa',
        ]);

        $response->assertStatus(422);
    }
     /**
     * @test
     * ログイン中のユーザーの認証機能(ログイン中)
     */
    public function should_return_login_user_true()
    {
        $response = $this->actingAs($this->user)->json('GET', route('user'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'email' => $this->user->email,
            ]);
    }
    /**
     * @test
     * ログイン中のユーザーの認証機能（未ログイン時)
     */
    public function should_return_login_user_false()
    {
        $response = $this->json('GET', route('user'));

        $response
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
    }
    /**
     * @test
     * ログアウト機能テスト
     */
    public function should_logout_true() :void
    {
        $response = $this->actingAs($this->user)
                         ->json('POST', route('logout'));

        $response->assertStatus(200);
        $this->assertGuest();
    }
}
