<?php

namespace Tests\Feature;

use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_returns_a_successful_response()
    {
        $response = $this->get(route('site.fr.home'));

        $response->assertOk();
    }
}
