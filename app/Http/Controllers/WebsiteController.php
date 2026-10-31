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
            'modules' => $this->productModules(),
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
                'heading' => 'One platform for every restaurant team',
                'description' => 'Explore visual previews of the admin dashboard, POS billing, QR ordering, Kitchen Display, waiter Service App, inventory, purchasing, reservations, and more.',
                'intro_title' => 'Useful tools, organized in one place',
                'intro' => 'RestaurantOS connects restaurant administration, guest ordering, kitchen preparation, waiter service, and daily business operations.',
                'highlights' => ['Separate admin, kitchen, and waiter workflows', 'Photo-led table QR ordering with request support', 'Inventory, purchasing, reservations, reports, and SaaS billing'],
                'features' => $this->productModules(),
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
                'label' => 'RESTAURANT INVENTORY',
                'title' => 'Restaurant Inventory and Stock Management',
                'heading' => 'Keep stock levels and movements visible',
                'description' => 'Track branch-level ingredients and supplies, record stock changes, and review low-stock items before they disrupt service.',
                'intro_title' => 'A reliable record of stock on hand',
                'intro' => 'Set opening quantities and reorder levels, record incoming stock or usage, and keep a movement history for each inventory item.',
                'highlights' => ['Branch-level stock quantities and units', 'Manual stock in, out, and adjustment entries', 'Low-stock watchlist and movement history'],
                'features' => [
                    $this->feature('bi-box-seam', 'Stock items', 'Record ingredient or supply names, units, opening quantities, and reorder levels.'),
                    $this->feature('bi-arrow-left-right', 'Stock movements', 'Log received stock, usage, wastage, or an adjustment with a movement reason.'),
                    $this->feature('bi-exclamation-triangle', 'Low-stock watchlist', 'Review items at or below their reorder level in inventory and reports.'),
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

        $pageDetails = [
            'about' => [
                'image' => 'restaurant-interior.jpg',
                'image_alt' => 'A warmly lit restaurant dining room ready for service',
                'image_caption' => 'A calmer service starts with a clear view of the day.',
                'section_title' => 'One workspace for the work behind every service',
                'section_text' => 'Set up your restaurant once, then keep the everyday building blocks—branches, menus, tables, orders, and team access—organized together.',
                'highlight_title' => 'A practical starting point',
                'feature_heading' => 'The everyday restaurant work, connected.',
                'workflow_title' => 'From setup to a smoother shift',
                'workflow' => [
                    ['title' => 'Set up your restaurant', 'description' => 'Create your workspace, add a branch, and configure the business details your team needs.'],
                    ['title' => 'Prepare the service', 'description' => 'Add menu items and tables, then give staff the access that matches their role.'],
                    ['title' => 'Keep work moving', 'description' => 'Create orders, follow status changes, and review activity from the dashboard.'],
                ],
                'cta_title' => 'Bring your restaurant’s daily tools together.',
                'cta_text' => 'Start with the workflows your team uses most, then grow your setup as the business expands.',
            ],
            'features' => [
                'image' => 'restaurant-dashboard.svg',
                'image_alt' => 'RestaurantOS admin dashboard preview with order and sales summaries',
                'image_caption' => 'A visual tour of the restaurant modules, from the admin dashboard to kitchen and guest ordering.',
                'section_title' => 'See how the pieces fit together',
                'section_text' => 'The admin dashboard brings business setup, sales and order operations together. Separate kitchen and service screens keep work focused, while a table QR menu lets guests send orders and request help.',
                'highlight_title' => 'Connected workflows include',
                'feature_heading' => 'Explore every module with a visual preview.',
                'workflow_title' => 'A simple restaurant workflow',
                'workflow' => [
                    ['title' => 'Configure', 'description' => 'Set up branches, categories, products, prices, tables, and service charges.'],
                    ['title' => 'Take orders', 'description' => 'Enter staff orders or let guests send a dine-in order from their table QR code.'],
                    ['title' => 'Follow up', 'description' => 'Update order statuses, record payments, and review reports for the selected period.'],
                ],
                'cta_title' => 'Explore the workflows that fit your restaurant.',
                'cta_text' => 'Create a workspace and review the tools with your team before adding live business data.',
            ],
            'restaurants' => [
                'image' => 'restaurant-service.svg',
                'image_alt' => 'Illustration of staff serving guests in a restaurant dining room',
                'image_caption' => 'Keep table service and order details in step.',
                'section_title' => 'Connect a guest’s table to their order',
                'section_text' => 'Set up dining tables, print a unique QR code for each one, and let guests browse the branch menu and submit a dine-in order. Staff can also enter orders directly.',
                'highlight_title' => 'A table-service workflow',
                'feature_heading' => 'Useful controls for restaurant service.',
                'workflow_title' => 'From the dining room to the order screen',
                'workflow' => [
                    ['title' => 'Prepare tables', 'description' => 'Add tables to a branch and print the matching QR code for each table.'],
                    ['title' => 'Take the order', 'description' => 'Guests scan and choose menu items, or staff create a dine-in order from the admin area.'],
                    ['title' => 'Serve and settle', 'description' => 'Team members move the order through its permitted statuses and record payment at the restaurant.'],
                ],
                'cta_title' => 'Make table orders easier to follow.',
                'cta_text' => 'Set up a sample table and walk through the QR ordering experience before opening it to guests.',
            ],
            'cafes' => [
                'image' => 'cafe-interior.jpg',
                'image_alt' => 'Bright café interior with a counter and seating area',
                'image_caption' => 'Keep counter orders and the menu easy to manage.',
                'section_title' => 'Keep the menu ready for a busy counter',
                'section_text' => 'Group drinks and food into categories, update prices, and mark items available or unavailable as the day changes. Staff can record takeaway and dine-in orders in the same workspace.',
                'highlight_title' => 'Made for café routines',
                'feature_heading' => 'A tidy menu and a straightforward counter flow.',
                'workflow_title' => 'A clear counter-service routine',
                'workflow' => [
                    ['title' => 'Organize the menu', 'description' => 'Create categories for coffee, drinks, food, and other items you serve.'],
                    ['title' => 'Update availability', 'description' => 'Keep sold-out products from appearing as available to the team or QR guests.'],
                    ['title' => 'Record each order', 'description' => 'Choose dine-in or takeaway, add items, and record payment when received.'],
                ],
                'cta_title' => 'Give your café team a simpler order routine.',
                'cta_text' => 'Build a sample drinks menu and see how it looks in the order workflow.',
            ],
            'cloud-kitchens' => [
                'image' => 'kitchen-team.jpg',
                'image_alt' => 'Chef preparing a plated dish in a professional kitchen',
                'image_caption' => 'Keep incoming order details together for the kitchen team.',
                'section_title' => 'Keep delivery details alongside each order',
                'section_text' => 'Create delivery and takeaway orders with their item list, customer contact, and delivery address. Staff can follow preparation statuses in the admin order workflow.',
                'highlight_title' => 'Order details at a glance',
                'feature_heading' => 'Keep the incoming order queue understandable.',
                'workflow_title' => 'An order flow for kitchen-first teams',
                'workflow' => [
                    ['title' => 'Keep items current', 'description' => 'Maintain branch products and availability so staff can select the right menu items.'],
                    ['title' => 'Capture fulfillment', 'description' => 'Choose takeaway or delivery and record customer details when a delivery needs them.'],
                    ['title' => 'Update progress', 'description' => 'Move the order through pending, preparing, ready, and completion as work progresses.'],
                ],
                'cta_title' => 'Organize the order details your kitchen relies on.',
                'cta_text' => 'Try the takeaway and delivery workflow with sample menu items before service.',
            ],
            'pos' => [
                'image' => 'menu-food.jpg',
                'image_alt' => 'Prepared restaurant dishes arranged for service',
                'image_caption' => 'Build a bill from menu items, then keep every tender with the order.',
                'section_title' => 'A bill that stays connected to its order',
                'section_text' => 'Use server-priced menu items, apply a fixed or percentage discount, and record full, partial, or split tender payments. An invoice number is created when payment is first recorded.',
                'highlight_title' => 'Billing controls in the current POS',
                'feature_heading' => 'A practical bill from item entry to payment.',
                'workflow_title' => 'A traceable billing sequence',
                'workflow' => [
                    ['title' => 'Build the order', 'description' => 'Choose a branch, order type, and menu items; the server calculates the authoritative prices and totals.'],
                    ['title' => 'Apply billing actions', 'description' => 'Add a discount or hold the unpaid order to resume later. Tax and service charge use the discounted amount.'],
                    ['title' => 'Record and reconcile', 'description' => 'Store payment and refund entries in the order ledger; reconcile cash against the register at close.'],
                ],
                'cta_title' => 'Walk through the complete bill workflow.',
                'cta_text' => 'Test discounts, split tenders, invoice output, and register close with sample data.',
            ],
            'inventory' => [
                'image' => 'chef-kitchen.jpg',
                'image_alt' => 'Chef preparing fresh ingredients on a cutting board',
                'image_caption' => 'Keep a record of stock changes as supplies arrive and are used.',
                'section_title' => 'Know what is on hand and what changed',
                'section_text' => 'Create branch inventory items, set units and reorder levels, and record stock adjustments. Receive a purchase order to increase stock, then review the movement history when quantities change.',
                'highlight_title' => 'Stock tools available today',
                'feature_heading' => 'Practical stock tracking for daily operations.',
                'workflow_title' => 'From stock entry to a low-stock check',
                'workflow' => [
                    ['title' => 'Set up stock items', 'description' => 'Add an ingredient or supply, choose its unit, and enter opening quantity and reorder level.'],
                    ['title' => 'Receive or adjust stock', 'description' => 'Receive a purchase order or log usage, wastage, and manual adjustments with a reason.'],
                    ['title' => 'Review the watchlist', 'description' => 'Use inventory views and reports to spot items at or below their reorder level.'],
                ],
                'cta_title' => 'Start with a clear stock list.',
                'cta_text' => 'Add a few commonly used supplies and record a test stock movement to see the history.',
            ],
            'multi-branch' => [
                'image' => 'kitchen-service.jpg',
                'image_alt' => 'Outdoor restaurant seating overlooking the water',
                'image_caption' => 'Keep each location organized under one restaurant group.',
                'section_title' => 'Separate branch work, keep one organization',
                'section_text' => 'Create restaurant branches under an organization. Products, tables, and orders are attached to a branch, while staff roles and assignments control which records a user can access.',
                'highlight_title' => 'Branch-aware operations',
                'feature_heading' => 'Expand location by location with clear access.',
                'workflow_title' => 'A repeatable branch setup',
                'workflow' => [
                    ['title' => 'Add a location', 'description' => 'Create a branch with its own name and unique branch code within the organization.'],
                    ['title' => 'Prepare local service', 'description' => 'Set up branch products and tables; configure organization-level currency, tax, and charges.'],
                    ['title' => 'Assign the team', 'description' => 'Give staff the appropriate role and branch assignment so daily work stays in scope.'],
                ],
                'cta_title' => 'Organize your locations in one workspace.',
                'cta_text' => 'Start with your main branch, then add additional locations as your rollout requires.',
            ],
            'faq' => [
                'image' => 'restaurant-team.jpg',
                'image_alt' => 'Two cooks preparing food together in a kitchen',
                'image_caption' => 'Straight answers about the workflows your team can use.',
                'section_title' => 'Understand what happens before launch',
                'section_text' => 'RestaurantOS supports branch-based order, menu, table, inventory, and reporting workflows. Review the current scope and payment behavior before planning a rollout.',
                'highlight_title' => 'Popular questions',
                'feature_heading' => 'Answers about ordering, payments, and access.',
                'workflow_title' => 'A quick readiness check',
                'workflow' => [
                    ['title' => 'Confirm your setup', 'description' => 'Check branch details, menu items, tables, taxes, and service charges.'],
                    ['title' => 'Try a real workflow', 'description' => 'Place a staff order and a table QR order, then inspect the order summary.'],
                    ['title' => 'Prepare the team', 'description' => 'Confirm staff access, payment recording, and the difference between subscription and food-order payment.'],
                ],
                'cta_title' => 'Need help deciding where to start?',
                'cta_text' => 'Send your business type and questions to the team for a product walkthrough.',
            ],
            'privacy' => [
                'image' => 'website-privacy.svg',
                'image_alt' => 'Illustration of protected account and restaurant information',
                'image_caption' => 'Use account access and role assignments responsibly.',
                'section_title' => 'Business information should stay in the right hands',
                'section_text' => 'The platform stores information entered during account, restaurant, menu, table, order, and contact workflows. Access depends on sign-in and the user’s role and branch scope.',
                'highlight_title' => 'Privacy considerations',
                'feature_heading' => 'How account and restaurant information is used.',
                'workflow_title' => 'Good practices for account owners',
                'workflow' => [
                    ['title' => 'Limit account access', 'description' => 'Create individual team accounts and assign only the role and branch access they need.'],
                    ['title' => 'Keep credentials private', 'description' => 'Use strong passwords and do not share administrator credentials with clients or staff.'],
                    ['title' => 'Plan data handling', 'description' => 'Confirm retention, backups, support access, and customer privacy requirements before launch.'],
                ],
                'cta_title' => 'Have a question about information handling?',
                'cta_text' => 'Contact the platform operator to confirm deployment-specific privacy and retention practices.',
                'is_legal' => true,
            ],
            'terms' => [
                'image' => 'website-terms.svg',
                'image_alt' => 'Illustration of reviewed service terms and account responsibilities',
                'image_caption' => 'Confirm business and payment policies before going live.',
                'section_title' => 'Agree on responsibilities before the first order',
                'section_text' => 'Account owners manage team access and are responsible for reviewing the restaurant details, menu prices, tax configuration, payment records, and service policies used by their workspace.',
                'highlight_title' => 'Before using the service',
                'feature_heading' => 'Clear expectations for every account.',
                'workflow_title' => 'A responsible launch checklist',
                'workflow' => [
                    ['title' => 'Review business settings', 'description' => 'Confirm organization, branch, menu, currency, taxes, and service charges are accurate.'],
                    ['title' => 'Authorize users', 'description' => 'Give access only to people working with the business and review access as roles change.'],
                    ['title' => 'Agree commercial terms', 'description' => 'Confirm subscription, cancellation, refund, support, and data-handling policies with the operator.'],
                ],
                'cta_title' => 'Need clarification about these terms?',
                'cta_text' => 'Contact the platform operator before using the workspace for live business operations.',
                'is_legal' => true,
            ],
        ];

        return view('website.inner-page', [
            'page' => array_merge($pages[$slug], $pageDetails[$slug]),
        ]);
    }

    private function feature(string $icon, string $title, string $description): array
    {
        return compact('icon', 'title', 'description');
    }

    private function productModules(): array
    {
        return [
            [
                'id' => 'admin-dashboard',
                'icon' => 'bi-speedometer2',
                'title' => 'Admin Dashboard',
                'description' => 'Review sales summaries, order statuses, recent activity, reservations, and low-stock alerts from your authorized workspace.',
                'image' => 'restaurant-dashboard.svg',
                'image_alt' => 'Restaurant admin dashboard showing sales, orders, tables, and order status summaries',
            ],
            [
                'id' => 'pos-billing',
                'icon' => 'bi-receipt',
                'title' => 'POS & Billing',
                'description' => 'Create dine-in, takeaway, and delivery orders; manage discounts, held orders, split payments, invoices, refunds, and cash-register closing.',
                'image' => 'menu-food.jpg',
                'image_alt' => 'Restaurant meal representing point-of-sale and menu orders',
            ],
            [
                'id' => 'qr-ordering',
                'icon' => 'bi-qr-code-scan',
                'title' => 'Table QR Ordering',
                'description' => 'Guests scan their table QR, browse menu photos, search categories, manage a live cart, place an order, and see the full confirmation.',
                'image' => 'qr-ordering.svg',
                'image_alt' => 'Mobile QR ordering preview with food photos, category filters, and a live cart',
            ],
            [
                'id' => 'kitchen-display',
                'icon' => 'bi-display',
                'title' => 'Kitchen Display',
                'description' => 'Kitchen staff use a standalone live board for new, preparing, ready-to-serve, and served-today tickets.',
                'image' => 'kitchen-display.svg',
                'image_alt' => 'Kitchen display board showing new, preparing, and ready order tickets',
            ],
            [
                'id' => 'service-app',
                'icon' => 'bi-phone',
                'title' => 'Waiter Service App',
                'description' => 'Waiters see tables, orders ready to serve, bill balances, and guest requests such as call waiter, request bill, or request water.',
                'image' => 'service-app.svg',
                'image_alt' => 'Waiter service app preview showing occupied tables, guest requests, and bills',
            ],
            [
                'id' => 'tables-reservations',
                'icon' => 'bi-table',
                'title' => 'Tables & Reservations',
                'description' => 'Manage table availability and QR codes, schedule guest reservations, and prevent overlapping bookings for the same table.',
                'image' => 'restaurant-interior.jpg',
                'image_alt' => 'Restaurant dining area with guest tables',
            ],
            [
                'id' => 'menu-management',
                'icon' => 'bi-menu-button-wide',
                'title' => 'Menu & Products',
                'description' => 'Organize categories and products, add photos and descriptions, set prices, and control availability on the guest menu.',
                'image' => 'cafe-interior.jpg',
                'image_alt' => 'Cafe counter and dining area representing menu operations',
            ],
            [
                'id' => 'inventory-purchasing',
                'icon' => 'bi-box-seam',
                'title' => 'Inventory & Purchasing',
                'description' => 'Track branch stock, adjustments and low-stock items, manage suppliers, and receive purchase orders into inventory.',
                'image' => 'chef-kitchen.jpg',
                'image_alt' => 'Chef working in a professional kitchen representing ingredient and stock operations',
            ],
            [
                'id' => 'calendar-events',
                'icon' => 'bi-calendar-event',
                'title' => 'Calendar & Events',
                'description' => 'Plan restaurant events and operational dates alongside the reservation workflow.',
                'image' => 'restaurant-interior.jpg',
                'image_alt' => 'Restaurant dining room prepared for scheduled events',
            ],
            [
                'id' => 'reports-analytics',
                'icon' => 'bi-bar-chart-line',
                'title' => 'Reports & Analytics',
                'description' => 'Review date-filtered sales, payment and order summaries, top-selling products, and low-stock watchlists.',
                'image' => 'restaurant-dashboard.svg',
                'image_alt' => 'Restaurant analytics dashboard preview with sales and operational summaries',
            ],
            [
                'id' => 'staff-branches',
                'icon' => 'bi-people',
                'title' => 'Staff & Branch Management',
                'description' => 'Manage restaurant teams with branch assignments and role-appropriate access for owners, managers, waiters, and kitchen staff.',
                'image' => 'restaurant-team.jpg',
                'image_alt' => 'Restaurant team collaborating during service',
            ],
            [
                'id' => 'saas-subscriptions',
                'icon' => 'bi-buildings',
                'title' => 'SaaS Plans & Billing',
                'description' => 'Operate separate restaurant workspaces, configure subscription plans and usage limits, and manage the platform billing workflow.',
                'image' => 'restaurant-service.svg',
                'image_alt' => 'Restaurant operations illustration representing connected SaaS workspaces',
            ],
        ];
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
