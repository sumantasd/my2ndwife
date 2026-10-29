<?php

namespace Tests\Feature;

use App\Models\HomepageBanner;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the homepage returns a successful response.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('is_accepting_orders', true);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
