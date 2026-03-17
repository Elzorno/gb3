<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupCompatibilityRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_route_redirects_to_legacy_setup_path(): void
    {
        $res = $this->get('/setup');
        $res->assertRedirect('/admin/setup.php');
    }
}
