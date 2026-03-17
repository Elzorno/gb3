<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidAuthRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_reachable(): void
    {
        $res = $this->get('/app/login');

        $res->assertOk();
        $res->assertSee('Who are you?');
    }

    public function test_successful_stub_login_sets_session_and_redirects_home(): void
    {
        Kid::query()->create([
            'id' => 1,
            'display_name' => 'Kid One',
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
            'sort_order' => 0,
        ]);

        $res = $this->post('/app/login', [
            'kid_id' => 1,
            'pin' => '123456',
        ]);

        $res->assertRedirect(route('app.today'));
        $res->assertSessionHas('gb2_kid_id', 1);
    }

    public function test_invalid_pin_rejected(): void
    {
        Kid::query()->create([
            'id' => 1,
            'display_name' => 'Kid One',
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
            'sort_order' => 0,
        ]);

        $res = $this->from('/app/login')->post('/app/login', [
            'kid_id' => 1,
            'pin' => '999999',
        ]);

        $res->assertRedirect('/app/login');
        $this->followRedirects($res)->assertSee('PIN did not match.');
    }

    public function test_rate_limit_lockout_blocks_even_correct_pin(): void
    {
        $kidId = 9;
        Kid::query()->create([
            'id' => $kidId,
            'display_name' => 'Kid Nine',
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
            'sort_order' => 0,
        ]);

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
            ->from('/app/login')
            ->post('/app/login', [
                'kid_id' => $kidId,
                'pin' => '123456',
            ]);

        $res->assertRedirect('/app/login');
        $res->assertSessionHasErrors('pin');
    }
}
