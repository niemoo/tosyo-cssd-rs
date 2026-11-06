<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The app has no public landing page — `/` always redirects into the dashboard,
     * which itself requires auth. This replaces the default Laravel starter assertion
     * (assertStatus 200), which doesn't match this app's routing.
     */
    public function test_the_root_url_redirects_towards_the_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
