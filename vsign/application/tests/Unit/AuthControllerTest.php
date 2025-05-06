<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\V1\AuthController;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleGithubCallbackCreatesTokenAndRedirects(): void
    {
        $user = User::factory()->create(['github_id' => null]);

        $mockedGithubUser = $this->mockGithubUser($user);

        Socialite::shouldReceive('driver->stateless->user')->andReturn($mockedGithubUser);

        $controller = new AuthController();
        $response = $controller->handleGithubCallback();

        $token = $user->tokens()->first()->token;
        $hashedUrlToken = $this->getHashedUrlToken($response);
        $this->assertNotEmpty($token);
        $this->assertEquals($hashedUrlToken, $token);
        $this->assertRedirectToCallbackWithToken($response, $token);
    }

    public function testHandleGithubCallbackHandlesAuthenticationFailure(): void
    {
        Socialite::shouldReceive('driver->stateless->user')->andThrow(new Exception('Authentication failed'));

        $controller = new AuthController();
        $response = $controller->handleGithubCallback();

        $this->assertEquals(config('app.url').'/login', $response->getTargetUrl());

        /** @var Store $errors */
        $errors = session('errors');
        $this->assertContains('Github auth error.', $errors->all());
    }

    private function mockGithubUser(User $user): \Laravel\Socialite\Two\User
    {
        $mockedUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $mockedUser->shouldReceive('getId')->andReturn($user->github_id);
        $mockedUser->shouldReceive('getName')->andReturn($user->name);
        $mockedUser->shouldReceive('getEmail')->andReturn($user->email);
        $mockedUser->shouldReceive('getToken')->andReturn('fake-token');

        return $mockedUser;
    }

    private function assertRedirectToCallbackWithToken(RedirectResponse $response, string $token): void
    {
        $this->assertStringStartsWith(config('app.url').'/auth/callback?token=', $response->getTargetUrl());
    }

    private function getHashedUrlToken(RedirectResponse $response): string
    {
        $redirectUrl = $response->getTargetUrl();

        $urlParts = parse_url($redirectUrl);
        parse_str($urlParts['query'], $queryParams);

        $urlToken = $queryParams['token'] ?? null;

        $cleanedToken = preg_replace('/^\d+\|/', '', $urlToken);

        return hash('sha256', $cleanedToken);
    }
}
