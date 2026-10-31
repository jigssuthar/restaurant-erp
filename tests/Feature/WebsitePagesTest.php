<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_dynamic_features_and_local_illustrations(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Fast POS billing')
            ->assertSee('Restaurants')
            ->assertSee(asset('images/restaurant-dashboard.svg'), false)
            ->assertSee(asset('images/restaurant-service.svg'), false);
    }

    public function test_all_marketing_routes_render_their_page_content(): void
    {
        $pages = [
            'about' => 'A practical workspace for your food business',
            'features' => 'The essentials for your daily restaurant operations',
            'solutions.restaurants' => 'Keep your dining room and orders in sync',
            'solutions.cafes' => 'A smoother flow for your café team',
            'solutions.cloud-kitchens' => 'Keep incoming orders organized',
            'products.pos' => 'A clear order workflow from entry to payment',
            'products.inventory' => 'Keep your menu information organized',
            'products.multi-branch' => 'Keep multiple locations organized',
            'faq' => 'Questions about getting started?',
            'privacy' => 'Privacy information',
            'terms' => 'Terms of use',
            'pricing' => 'Simple Pricing For Your Business',
            'contact' => 'Let’s talk about your business.',
        ];

        foreach ($pages as $routeName => $expectedHeading) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($expectedHeading);
        }
    }
}
