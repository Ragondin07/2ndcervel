<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_guest_is_redirected_from_private_pages(): void
    {
        foreach (['/dashboard', '/projects', '/notes', '/quick-add', '/search', '/decisions', '/actions', '/files', '/inbox'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }
}
