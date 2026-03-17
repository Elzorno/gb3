<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreezeWritesModeTest extends TestCase
{
    use RefreshDatabase;

    private string $flagPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->flagPath = storage_path('framework/gb3_write_freeze.flag');
        @unlink($this->flagPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->flagPath);
        parent::tearDown();
    }

    public function test_reads_work_but_writes_block_when_frozen(): void
    {
        file_put_contents($this->flagPath, '{"frozen":true}');

        // GET / now redirects to app.login, which is a read
        $read = $this->get('/app/login');
        $read->assertOk();

        $write = $this->post('/app/login', [
            'kid_id' => 1,
            'pin' => '123456',
        ]);

        $write->assertStatus(503);
        $write->assertSee('Write operations are temporarily frozen for maintenance.');
    }
}
