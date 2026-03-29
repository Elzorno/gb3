<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class DesignLayoutsPageTest extends TestCase
{
    public function test_design_layouts_page_is_available(): void
    {
        $response = $this->get(route('design.layouts'));

        $response
            ->assertOk()
            ->assertSee('Gentle Focus')
            ->assertSee('Circle of Support')
            ->assertSee('Pathways Board');
    }
}
