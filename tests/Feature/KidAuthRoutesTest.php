<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidAuthRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_reachable(): void
    {
        $res = $this->get('/kid/login');

        $res->assertOk();
        $res->assertSee('Kid Login');
    }

    public function test_successful_stub_login_sets_session_and_redirects_home(): void
    {
        $res = $this->post('/kid/login', [
            'kid_id' => 1,
            'pin' => '123456',
        ]);

        $res->assertRedirect('/');
        $this->followRedirects($res)
            ->assertSee('Kid session started.')
            ->assertSee('Current kid session id:')
            ->assertSee('1');
    }

    public function test_invalid_pin_rejected(): void
    {
        $res = $this->from('/kid/login')->post('/kid/login', [
            'kid_id' => 1,
            'pin' => '999999',
        ]);

        $res->assertRedirect('/kid/login');
        $this->followRedirects($res)->assertSee('PIN did not match.');
    }

    public function test_rate_limit_lockout_blocks_even_correct_pin(): void
    {
        $kidId = 9;
        $ip = '10.0.0.1';
        $ua = 'PHPUnit-Auth-Test';
        $key = hash('sha256', $kidId . '|' . $ip . '|' . $ua);
        $lockUntil = time() + 600;

        $res = $this
            ->withServerVariables([
                'REMOTE_ADDR' => $ip,
                'HTTP_USER_AGENT' => $ua,
            ])
            ->withSession([
                'gb2_pin_rate' => [
                    $key => [
                        'window_started' => time(),
                        'attempts' => 5,
                        'lock_until' => $lockUntil,
                    ],
                ],
            ])
            ->from('/kid/login')
            ->post('/kid/login', [
                'kid_id' => $kidId,
                'pin' => '123456',
            ]);

        $res->assertRedirect('/kid/login');
        $res->assertSessionHasErrors('pin');
    }
}
