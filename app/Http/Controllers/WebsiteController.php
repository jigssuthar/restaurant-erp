<?php

namespace App\Http\Controllers;

class WebsiteController extends Controller
{
    public function home()
    {
        return view('website.home', [
            'businessTypes' => [
                ['icon' => 'bi-shop', 'name' => 'Restaurants'],
                ['icon' => 'bi-cup-hot', 'name' => 'Cafés'],
                ['icon' => 'bi-bag', 'name' => 'Cloud kitchens'],
                ['icon' => 'bi-buildings', 'name' => 'Restaurant groups'],
            ],
            'features' => [
                ['icon' => 'bi-receipt', 'title' => 'Fast POS billing', 'description' => 'Build accurate bills and keep every payment organized.'],
                ['icon' => 'bi-cart-check', 'title' => 'Order management', 'description' => 'Bring dine-in, takeaway, delivery, and QR orders together.'],
                ['icon' => 'bi-fire', 'title' => 'Kitchen workflow', 'description' => 'Keep order preparation moving with clear kitchen statuses.'],
                ['icon' => 'bi-grid-3x3', 'title' => 'Table management', 'description' => 'See table availability and serve dine-in guests smoothly.'],
                ['icon' => 'bi-menu-button-wide', 'title' => 'Menu management', 'description' => 'Organize categories, products, prices, and availability.'],
                ['icon' => 'bi-buildings', 'title' => 'Multi-branch control', 'description' => 'Manage locations and teams with role-based access.'],
            ],
            'solutions' => [
                ['icon' => 'bi-shop', 'title' => 'Restaurants', 'description' => 'Coordinate tables, menus, orders, and service in one place.', 'route' => 'solutions.restaurants'],
                ['icon' => 'bi-cup-hot', 'title' => 'Cafés', 'description' => 'Keep counter service, beverages, and takeaway moving.', 'route' => 'solutions.cafes'],
                ['icon' => 'bi-bag', 'title' => 'Cloud kitchens', 'description' => 'Organize incoming orders and kitchen preparation.', 'route' => 'solutions.cloud-kitchens'],
            ],
            'steps' => [
                ['number' => '01', 'title' => 'Set up your workspace', 'description' => 'Create your organization and add your restaurant branches.'],
                ['number' => '02', 'title' => 'Build your operations', 'description' => 'Add your menu, tables, team members, and settings.'],
                ['number' => '03', 'title' => 'Serve with clarity', 'description' => 'Manage orders and follow business activity from your dashboard.'],
            ],
        ]);
    }

    public function page(string $slug)
    {
        $pages = [
            'about' => [
                'label' => 'ABOUT RESTAURANTOS',
                'title' => 'Restaurant operations, made clearer',
                'heading' => 'A practical workspace for your food business',
                'description' => 'RestaurantOS brings everyday restaurant workflows into one organized platform, so teams can focus on great service.',
                'intro_title' => 'Built around the rhythm of restaurant service',
                'intro' => 'From setting up a menu to checking order progress, keep essential information accessible to the people who need it.',
                'highlights' => ['One workspace for essential operations', 'Branch and staff access controls', 'Order and table workflows'],
                'features' => [
                    $this->feature('bi-grid-1x2', 'Connected workflows', 'Keep menu, tables, orders, and branch information together.'),
                    $this->feature('bi-people', 'Role-based access', 'Give administrators, managers, and staff access suited to their work.'),
                    $this->feature('bi-phone', 'Ready for service', 'Use a responsive interface across desktop, tablet, and mobile.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'features' => [
                'label' => 'PLATFORM FEATURES',
                'title' => 'RestaurantOS Features',
                'heading' => 'The essentials for your daily restaurant operations',
                'description' => 'Manage menus, orders, tables, staff, settings, and branches through one connected dashboard.',
                'intro_title' => 'Useful tools, organized in one place',
                'intro' => 'RestaurantOS brings the day-to-day building blocks of restaurant management into a single, approachable workspace.',
                'highlights' => ['Order and payment workflows', 'Products, categories, and tables', 'Branch-aware roles and settings'],
                'features' => [
                    $this->feature('bi-receipt', 'POS order management', 'Create dine-in, takeaway, and delivery orders with clear payment tracking.'),
                    $this->feature('bi-qr-code-scan', 'Table QR ordering', 'Let guests scan a table code, browse your menu, and send an order to that table.'),
                    $this->feature('bi-menu-button-wide', 'Products and categories', 'Keep your branch menus organized and control item availability.'),
                    $this->feature('bi-people', 'Staff and access roles', 'Assign admin, manager, and staff access with appropriate data scope.'),
                    $this->feature('bi-gear', 'Organization settings', 'Configure currency, taxes, service charges, and receipt information.'),
                    $this->feature('bi-bar-chart', 'Dashboard overview', 'See current module counts, order status summaries, and recent activity.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'restaurants' => [
                'label' => 'RESTAURANT SOLUTION',
                'title' => 'Restaurant Management Software',
                'heading' => 'Keep your dining room and orders in sync',
                'description' => 'Manage table service, menus, order progress, and branch teams with one practical restaurant workspace.',
                'intro_title' => 'More visibility from table to kitchen',
                'intro' => 'Give your team a consistent way to manage dine-in service while keeping takeaway and delivery orders organized too.',
                'highlights' => ['Table-linked dine-in orders', 'QR menu and guest ordering', 'Shared menu and order status workflows'],
                'features' => [
                    $this->feature('bi-grid-3x3', 'Table operations', 'Manage seating, table status, and orders tied to a table.'),
                    $this->feature('bi-qr-code', 'QR menu ordering', 'Offer customers a direct way to open the menu at their table.'),
                    $this->feature('bi-cart-check', 'Order tracking', 'Follow orders from pending through preparation and completion.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'cafes' => [
                'label' => 'CAFÉ SOLUTION',
                'title' => 'Cafe Management Software',
                'heading' => 'A smoother flow for your café team',
                'description' => 'Organize products, counter orders, staff access, and daily activity in a responsive workspace.',
                'intro_title' => 'Keep the counter moving',
                'intro' => 'Make your menu easy to manage and keep order information clear during busy café service.',
                'highlights' => ['Organized product categories', 'Quick takeaway workflows', 'Branch and staff management'],
                'features' => [
                    $this->feature('bi-cup-hot', 'A tidy menu', 'Keep drinks, food, and product availability up to date.'),
                    $this->feature('bi-bag-check', 'Counter orders', 'Record takeaway and dine-in orders in the same workflow.'),
                    $this->feature('bi-graph-up', 'Daily visibility', 'Review order activity and key dashboard summaries.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'cloud-kitchens' => [
                'label' => 'CLOUD KITCHEN SOLUTION',
                'title' => 'Cloud Kitchen Management Software',
                'heading' => 'Keep incoming orders organized',
                'description' => 'Give your kitchen team a clear view of order details and preparation progress across your operation.',
                'intro_title' => 'Order clarity for a kitchen-first business',
                'intro' => 'Organize products, delivery details, order statuses, and team access without the overhead of a dining room workflow.',
                'highlights' => ['Delivery and takeaway orders', 'Clear preparation statuses', 'Branch-level product management'],
                'features' => [
                    $this->feature('bi-bag-check', 'Delivery workflows', 'Record customer contact and delivery details alongside orders.'),
                    $this->feature('bi-fire', 'Preparation visibility', 'Follow order statuses as the kitchen works through incoming tickets.'),
                    $this->feature('bi-box-seam', 'Menu availability', 'Keep product details and availability maintained by branch.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'pos' => [
                'label' => 'RESTAURANT POS',
                'title' => 'Restaurant POS and Order Management',
                'heading' => 'A clear order workflow from entry to payment',
                'description' => 'Manage dine-in, takeaway, and delivery orders with item-level pricing snapshots and payment status tracking.',
                'intro_title' => 'Order handling that stays connected',
                'intro' => 'Record customer and table details, follow order preparation, and keep payment information alongside each order.',
                'highlights' => ['Dine-in, takeaway, and delivery types', 'Payment status and method tracking', 'Saved item prices and totals'],
                'features' => [
                    $this->feature('bi-receipt', 'Flexible order types', 'Handle dine-in, takeaway, and delivery orders from your workspace.'),
                    $this->feature('bi-cash-coin', 'Payment tracking', 'Record pending or paid status and the payment method.'),
                    $this->feature('bi-card-checklist', 'Order details', 'Keep customer details, notes, items, and totals together.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'inventory' => [
                'label' => 'MENU AND PRODUCT MANAGEMENT',
                'title' => 'Restaurant Product and Menu Management',
                'heading' => 'Keep your menu information organized',
                'description' => 'Manage product details, categories, pricing, and availability for each restaurant branch.',
                'intro_title' => 'A more manageable menu workspace',
                'intro' => 'Maintain consistent product information and quickly control which items are available to customers.',
                'highlights' => ['Branch-specific product lists', 'Category-based menus', 'Availability controls'],
                'features' => [
                    $this->feature('bi-menu-button-wide', 'Product catalog', 'Manage names, descriptions, prices, and product types.'),
                    $this->feature('bi-tags', 'Categories', 'Group menu items into useful categories for staff and guests.'),
                    $this->feature('bi-toggle-on', 'Availability controls', 'Make products available or unavailable as menus change.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'multi-branch' => [
                'label' => 'MULTI-BRANCH MANAGEMENT',
                'title' => 'Multi-Branch Restaurant Management',
                'heading' => 'Keep multiple locations organized',
                'description' => 'Manage restaurant branches, branch-level operations, and role-based teams from a shared platform.',
                'intro_title' => 'A connected view with branch-aware access',
                'intro' => 'Organize locations and give managers and staff access scoped to their assigned organization or branch.',
                'highlights' => ['Central organization settings', 'Branch-specific products and tables', 'Manager and staff access roles'],
                'features' => [
                    $this->feature('bi-buildings', 'Branch management', 'Maintain locations and their contact details in one place.'),
                    $this->feature('bi-shield-check', 'Scoped permissions', 'Keep access aligned with organization and branch responsibilities.'),
                    $this->feature('bi-grid', 'Branch-level operations', 'Manage products, tables, and orders for each location.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'faq' => [
                'label' => 'HELP AND ANSWERS',
                'title' => 'RestaurantOS Frequently Asked Questions',
                'heading' => 'Questions about getting started?',
                'description' => 'Find quick answers about the RestaurantOS platform and its restaurant workflows.',
                'intro_title' => 'A few things to know',
                'intro' => 'RestaurantOS is a restaurant management application with organization, branch, menu, table, order, and staff workflows.',
                'highlights' => ['Create and manage restaurant branches', 'Accept table QR orders with payment at the restaurant', 'Configure currency, taxes, and service charges'],
                'features' => [
                    $this->feature('bi-question-circle', 'Can guests order by QR?', 'Yes. Each table can have a unique QR code that opens its menu and sends orders to that table.'),
                    $this->feature('bi-credit-card', 'Are QR orders paid online?', 'No. QR orders are submitted as unpaid dine-in orders for payment at the restaurant.'),
                    $this->feature('bi-person-gear', 'Can teams have different access?', 'Yes. Admin, manager, and staff roles have different permissions and data scopes.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'privacy' => [
                'label' => 'LEGAL',
                'title' => 'Privacy Policy',
                'heading' => 'Privacy information',
                'description' => 'This page describes the information RestaurantOS workflows may store and how account access is managed.',
                'intro_title' => 'Information handled by the platform',
                'intro' => 'RestaurantOS stores account and restaurant information entered by its users, including organization, branch, product, table, and order records. Contact form submissions are stored so the team can respond to enquiries.',
                'highlights' => ['Account and business records support application features', 'Role-based permissions control access to business data', 'Contact information is used to respond to submitted enquiries'],
                'features' => [
                    $this->feature('bi-person-lock', 'Account access', 'Users sign in to access the organization and branch data allowed by their assigned role.'),
                    $this->feature('bi-database-check', 'Business records', 'Restaurant information is stored to support menus, orders, tables, and reporting.'),
                    $this->feature('bi-envelope', 'Contact enquiries', 'Information sent through the contact form is retained for follow-up.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
            'terms' => [
                'label' => 'LEGAL',
                'title' => 'Terms and Conditions',
                'heading' => 'Terms of use',
                'description' => 'Use RestaurantOS responsibly and keep your account and business information accurate.',
                'intro_title' => 'Using RestaurantOS',
                'intro' => 'Users are responsible for maintaining the confidentiality of their account credentials, managing access for their teams, and ensuring that order, menu, tax, and payment information is entered accurately.',
                'highlights' => ['Keep account credentials secure', 'Review configured prices, tax, and fees', 'Use role permissions for authorized team members'],
                'features' => [
                    $this->feature('bi-person-check', 'Account responsibility', 'Account holders are responsible for actions performed through their accounts.'),
                    $this->feature('bi-clipboard-check', 'Accurate information', 'Review your restaurant, menu, payment, and tax settings regularly.'),
                    $this->feature('bi-shield-lock', 'Authorized access', 'Only provide access to people authorized to work with your business records.'),
                ],
                'benefits' => $this->standardBenefits(),
                'steps' => $this->standardSteps(),
            ],
        ];

        abort_unless(isset($pages[$slug]), 404);

        return view('website.inner-page', ['page' => $pages[$slug]]);
    }

    private function feature(string $icon, string $title, string $description): array
    {
        return compact('icon', 'title', 'description');
    }

    private function standardBenefits(): array
    {
        return [
            ['title' => 'Keep information together', 'description' => 'Find everyday restaurant details in a shared workspace.'],
            ['title' => 'Make access intentional', 'description' => 'Use roles and branch assignments to keep team access appropriate.'],
            ['title' => 'Work across screen sizes', 'description' => 'Use a responsive interface on desktop, tablet, or mobile.'],
        ];
    }

    private function standardSteps(): array
    {
        return [
            ['title' => 'Create your organization', 'description' => 'Set up the restaurant organization and its locations.'],
            ['title' => 'Add your daily essentials', 'description' => 'Configure branches, categories, products, tables, and staff.'],
            ['title' => 'Manage service', 'description' => 'Use orders and dashboard summaries to follow daily operations.'],
        ];
    }
}
