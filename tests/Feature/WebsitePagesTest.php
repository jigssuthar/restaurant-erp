<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
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
            ->assertSee(asset('images/restaurant-service.svg'), false)
            ->assertSee(asset('images/restaurant-interior.jpg'), false)
            ->assertSee(asset('images/cafe-interior.jpg'), false)
            ->assertSee(asset('images/kitchen-team.jpg'), false)
            ->assertSee('Admin Dashboard')
            ->assertSee('Kitchen Display')
            ->assertSee('Table QR Ordering')
            ->assertSee('Waiter Service App')
            ->assertSee(asset('images/kitchen-display.svg'), false)
            ->assertSee(asset('images/service-app.svg'), false)
            ->assertSee(asset('images/qr-ordering.svg'), false);
    }

    public function test_authenticated_user_sees_dashboard_link_instead_of_login_and_registration(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('admin.dashboard'), false)
            ->assertDontSee('Login')
            ->assertDontSee('Get Started');
    }

    public function test_all_marketing_routes_render_their_page_content(): void
    {
        $pages = [
            'about' => 'A practical workspace for your food business',
            'features' => 'One platform for every restaurant team',
            'solutions.restaurants' => 'Keep your dining room and orders in sync',
            'solutions.cafes' => 'A smoother flow for your café team',
            'solutions.cloud-kitchens' => 'Keep incoming orders organized',
            'products.pos' => 'A clear order workflow from entry to payment',
            'products.inventory' => 'Keep stock levels and movements visible',
            'products.multi-branch' => 'Keep multiple locations organized',
            'faq' => 'Questions about getting started?',
            'privacy' => 'Privacy information',
            'terms' => 'Terms of use',
            'pricing' => 'Simple Pricing For Your Business',
            'contact' => 'Let’s talk about your business.',
        ];
        $pageImages = [
            'about' => 'restaurant-interior.jpg',
            'features' => 'restaurant-dashboard.svg',
            'solutions.restaurants' => 'restaurant-service.svg',
            'solutions.cafes' => 'cafe-interior.jpg',
            'solutions.cloud-kitchens' => 'kitchen-team.jpg',
            'products.pos' => 'menu-food.jpg',
            'products.inventory' => 'chef-kitchen.jpg',
            'products.multi-branch' => 'kitchen-service.jpg',
            'faq' => 'restaurant-team.jpg',
            'privacy' => 'website-privacy.svg',
            'terms' => 'website-terms.svg',
        ];

        foreach ($pages as $routeName => $expectedHeading) {
            $response = $this->get(route($routeName))
                ->assertOk()
                ->assertSee($expectedHeading);

            if (isset($pageImages[$routeName])) {
                $response->assertSee(asset('images/'.$pageImages[$routeName]), false)
                    ->assertSee('HOW IT WORKS');
            }
        }

        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee(asset('images/menu-food.jpg'), false)
            ->assertSee('PLANS FOR YOUR OPERATION');
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee(asset('images/restaurant-team.jpg'), false)
            ->assertSee('Let’s talk about your business.');
    }

    public function test_feature_page_shows_illustrated_cards_for_all_current_modules(): void
    {
        $response = $this->get(route('features'))
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('POS & Billing')
            ->assertSee('Table QR Ordering')
            ->assertSee('Kitchen Display')
            ->assertSee('Waiter Service App')
            ->assertSee('Tables & Reservations')
            ->assertSee('Menu & Products')
            ->assertSee('Inventory & Purchasing')
            ->assertSee('Calendar & Events')
            ->assertSee('Reports & Analytics')
            ->assertSee('Staff & Branch Management')
            ->assertSee('SaaS Plans & Billing');

        foreach ([
            'restaurant-dashboard.svg',
            'menu-food.jpg',
            'qr-ordering.svg',
            'kitchen-display.svg',
            'service-app.svg',
            'restaurant-interior.jpg',
            'cafe-interior.jpg',
            'chef-kitchen.jpg',
            'restaurant-team.jpg',
            'restaurant-service.svg',
        ] as $image) {
            $response->assertSee(asset('images/'.$image), false);
            $this->assertFileExists(public_path('images/'.$image));
        }

        $response->assertSee('id="module-admin-dashboard"', false)
            ->assertSee('id="module-kitchen-display"', false)
            ->assertSee('id="module-qr-ordering"', false)
            ->assertSee('id="module-service-app"', false);
    }
}
