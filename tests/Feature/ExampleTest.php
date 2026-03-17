<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_redirects_to_kid_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('app.login'));
    }
}
