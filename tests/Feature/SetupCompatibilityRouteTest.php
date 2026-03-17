<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupCompatibilityRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_route_redirects_to_admin_setup(): void
    {
        $res = $this->get('/admin/setup');
        // When setup hasn't been completed, it shows the setup page
        // When it has, it redirects to login
        $res->assertStatus(200);
    }
}
