<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGithubRedirectRouteRedirectsToGithub(): void
    {
        $response = $this->get(route('api.v1.auth.redirect-to-github'));

        $response->assertRedirect();
        $this->assertNotNull($response->headers->get('Location'));
        $this->assertStringContainsString('github.com', $response->headers->get('Location'));
    }
}
