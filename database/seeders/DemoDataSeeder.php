<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $organization = Organization::updateOrCreate(
                ['slug' => 'demo-spice-garden'],
                [
                    'name' => 'Spice Garden Demo',
                    'email' => 'hello@spice-garden.example',
                    'phone' => '+91 98765 43210',
                    'address' => '45 Market Road, Ahmedabad, Gujarat',
                    'gst_number' => '24ABCDE1234F1Z5',
                    'status' => 'active',
                ]
            );

            OrganizationSetting::updateOrCreate(
                ['organization_id' => $organization->id],
                [
                    'currency_code' => 'INR',
                    'tax_label' => 'GST',
                    'tax_rate' => 5,
                    'service_charge_rate' => 2,
                    'delivery_fee' => 40,
                    'receipt_header' => 'Spice Garden',
                    'receipt_footer' => 'Thank you for dining with us!',
                ]
            );

            $branchData = [
                [
                    'name' => 'City Centre',
                    'branch_code' => 'CITY',
                    'email' => 'city@spice-garden.example',
                    'phone' => '+91 98765 43211',
                    'address' => '12 CG Road',
                    'city' => 'Ahmedabad',
                    'state' => 'Gujarat',
                    'pincode' => '380009',
                ],
                [
                    'name' => 'Riverside',
                    'branch_code' => 'RIVER',
                    'email' => 'riverside@spice-garden.example',
                    'phone' => '+91 98765 43212',
                    'address' => '8 Riverfront Lane',
                    'city' => 'Ahmedabad',
                    'state' => 'Gujarat',
                    'pincode' => '380001',
                ],
            ];

            foreach ($branchData as $data) {
                $branch = Branch::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'branch_code' => $data['branch_code'],
                    ],
                    [...$data, 'status' => 'active']
                );

                $this->seedBranch($branch);
            }

            $this->seedEnquiries();
        });
    }

    private function seedBranch(Branch $branch): void
    {
        $demoPassword = env('DEMO_USER_PASSWORD');
        $manager = User::firstOrCreate(
            ['email' => strtolower($branch->branch_code).'.manager@spice-garden.example'],
            [
                'name' => $branch->name.' Manager',
                'password' => Hash::make($demoPassword ?: Str::random(48)),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );
        $manager->update([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'role' => 'manager',
        ]);
        if ($demoPassword) {
            $manager->update(['password' => $demoPassword]);
        }

        $staff = User::firstOrCreate(
            ['email' => strtolower($branch->branch_code).'.staff@spice-garden.example'],
            [
                'name' => $branch->name.' Staff',
                'password' => Hash::make($demoPassword ?: Str::random(48)),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );
        $staff->update([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'role' => 'staff',
        ]);
        if ($demoPassword) {
            $staff->update(['password' => $demoPassword]);
        }

        $categoryData = [
            ['name' => 'Starters', 'description' => 'Small plates and appetizers.'],
            ['name' => 'Main Course', 'description' => 'Freshly prepared signature meals.'],
            ['name' => 'Beverages', 'description' => 'Hot and cold drinks.'],
            ['name' => 'Desserts', 'description' => 'A sweet finish to your meal.'],
        ];
        $categories = [];

        foreach ($categoryData as $data) {
            $categories[$data['name']] = Category::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'slug' => Str::slug($data['name']),
                ],
                [...$data, 'is_active' => true]
            );
        }

        $productData = [
            ['category' => 'Starters', 'name' => 'Paneer Tikka', 'sku' => 'PANEER-TIKKA', 'description' => 'Char-grilled paneer with house spices.', 'price' => 220, 'cost_price' => 110, 'type' => 'food'],
            ['category' => 'Starters', 'name' => 'Masala Papad', 'sku' => 'MASALA-PAPAD', 'description' => 'Crispy papad topped with fresh masala.', 'price' => 80, 'cost_price' => 25, 'type' => 'food'],
            ['category' => 'Starters', 'name' => 'Veg Spring Rolls', 'sku' => 'VEG-SPRING-ROLLS', 'description' => 'Crisp rolls filled with seasoned vegetables.', 'price' => 160, 'cost_price' => 70, 'type' => 'food'],
            ['category' => 'Main Course', 'name' => 'Gujarati Thali', 'sku' => 'GUJARATI-THALI', 'description' => 'A wholesome traditional Gujarati meal.', 'price' => 320, 'cost_price' => 165, 'type' => 'food'],
            ['category' => 'Main Course', 'name' => 'Paneer Butter Masala', 'sku' => 'PANEER-BUTTER-MASALA', 'description' => 'Paneer in a creamy tomato gravy.', 'price' => 280, 'cost_price' => 140, 'type' => 'food'],
            ['category' => 'Main Course', 'name' => 'Jeera Rice', 'sku' => 'JEERA-RICE', 'description' => 'Basmati rice tempered with cumin.', 'price' => 150, 'cost_price' => 60, 'type' => 'food'],
            ['category' => 'Beverages', 'name' => 'Masala Chai', 'sku' => 'MASALA-CHAI', 'description' => 'Indian tea brewed with aromatic spices.', 'price' => 45, 'cost_price' => 12, 'type' => 'beverage'],
            ['category' => 'Beverages', 'name' => 'Mango Lassi', 'sku' => 'MANGO-LASSI', 'description' => 'Chilled yogurt drink blended with mango.', 'price' => 110, 'cost_price' => 45, 'type' => 'beverage'],
            ['category' => 'Beverages', 'name' => 'Fresh Lime Soda', 'sku' => 'FRESH-LIME-SODA', 'description' => 'Fresh lime with sparkling soda.', 'price' => 75, 'cost_price' => 22, 'type' => 'beverage'],
            ['category' => 'Desserts', 'name' => 'Gulab Jamun', 'sku' => 'GULAB-JAMUN', 'description' => 'Warm milk dumplings in rose syrup.', 'price' => 90, 'cost_price' => 35, 'type' => 'food'],
            ['category' => 'Desserts', 'name' => 'Kulfi', 'sku' => 'KULFI', 'description' => 'Traditional slow-frozen Indian ice cream.', 'price' => 100, 'cost_price' => 40, 'type' => 'food'],
        ];
        $products = [];

        foreach ($productData as $data) {
            $categoryName = $data['category'];
            unset($data['category']);
            $products[$data['sku']] = Product::updateOrCreate(
                ['branch_id' => $branch->id, 'sku' => $data['sku']],
                [...$data, 'category_id' => $categories[$categoryName]->id, 'is_available' => true]
            );
        }

        $tables = [];
        foreach (range(1, 6) as $number) {
            $tableNumber = sprintf('T-%02d', $number);
            $tables[$number] = DiningTable::updateOrCreate(
                ['branch_id' => $branch->id, 'table_number' => $tableNumber],
                ['capacity' => $number % 2 === 0 ? 4 : 2, 'status' => 'available']
            );
        }

        $setting = OrganizationSetting::where('organization_id', $branch->organization_id)->firstOrFail();
        $this->seedOrder(
            $branch,
            $tables[1],
            $staff,
            'DEMO-'.$branch->branch_code.'-001',
            'dine_in',
            'pending',
            'pending',
            null,
            null,
            ['PANEER-TIKKA' => 1, 'MANGO-LASSI' => 2],
            $products,
            $setting
        );
        $this->seedOrder(
            $branch,
            $tables[2],
            $staff,
            'DEMO-'.$branch->branch_code.'-002',
            'dine_in',
            'preparing',
            'pending',
            null,
            null,
            ['GUJARATI-THALI' => 2, 'MASALA-CHAI' => 2],
            $products,
            $setting
        );
        $this->seedOrder(
            $branch,
            null,
            $staff,
            'DEMO-'.$branch->branch_code.'-003',
            'takeaway',
            'completed',
            'paid',
            'cash',
            null,
            ['PANEER-BUTTER-MASALA' => 1, 'JEERA-RICE' => 2, 'GULAB-JAMUN' => 2],
            $products,
            $setting
        );
        $this->seedOrder(
            $branch,
            null,
            $manager,
            'DEMO-'.$branch->branch_code.'-004',
            'delivery',
            'ready',
            'paid',
            'upi',
            '24 Garden Residency, Ahmedabad',
            ['VEG-SPRING-ROLLS' => 1, 'PANEER-BUTTER-MASALA' => 1, 'KULFI' => 1],
            $products,
            $setting
        );

        $tables[1]->update(['status' => 'occupied']);
        $tables[2]->update(['status' => 'occupied']);
    }

    /**
     * @param array<string, int> $quantities
     * @param array<string, Product> $products
     */
    private function seedOrder(
        Branch $branch,
        ?DiningTable $table,
        User $user,
        string $orderNumber,
        string $orderType,
        string $status,
        string $paymentStatus,
        ?string $paymentMethod,
        ?string $deliveryAddress,
        array $quantities,
        array $products,
        OrganizationSetting $setting,
    ): void {
        $subtotal = 0;
        $items = [];

        foreach ($quantities as $sku => $quantity) {
            $product = $products[$sku];
            $lineTotal = round((float) $product->price * $quantity, 2);
            $subtotal += $lineTotal;
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'line_total' => $lineTotal,
            ];
        }

        $taxAmount = round($subtotal * (float) $setting->tax_rate / 100, 2);
        $serviceCharge = round($subtotal * (float) $setting->service_charge_rate / 100, 2);
        $deliveryFee = $orderType === 'delivery' ? (float) $setting->delivery_fee : 0;
        $order = Order::updateOrCreate(
            ['branch_id' => $branch->id, 'order_number' => $orderNumber],
            [
                'dining_table_id' => $table?->id,
                'user_id' => $user->id,
                'order_type' => $orderType,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'customer_name' => $orderType === 'delivery' ? 'Aarav Patel' : 'Demo Customer',
                'customer_phone' => '+91 98765 43000',
                'delivery_address' => $deliveryAddress,
                'subtotal' => $subtotal,
                'currency_code' => $setting->currency_code,
                'tax_label' => $setting->tax_label,
                'tax_rate' => $setting->tax_rate,
                'tax_amount' => $taxAmount,
                'service_charge_rate' => $setting->service_charge_rate,
                'service_charge' => $serviceCharge,
                'delivery_fee' => $deliveryFee,
                'total' => round($subtotal + $taxAmount + $serviceCharge + $deliveryFee, 2),
                'notes' => 'Sample order for dashboard demonstration.',
                'paid_at' => $paymentStatus === 'paid' ? now()->subMinutes(20) : null,
            ]
        );

        if (! $order->items()->exists()) {
            $order->items()->createMany($items);
        }
    }

    private function seedEnquiries(): void
    {
        $enquiries = [
            [
                'email' => 'neha.shah@example.test',
                'name' => 'Neha Shah',
                'phone' => '+91 98765 43001',
                'business_type' => 'Restaurant',
                'subject' => 'POS setup for a new restaurant',
                'message' => 'We are opening a 40-seat restaurant and would like a product walkthrough.',
                'status' => 'new',
            ],
            [
                'email' => 'rohan.mehta@example.test',
                'name' => 'Rohan Mehta',
                'phone' => '+91 98765 43002',
                'business_type' => 'Cafe',
                'subject' => 'Managing multiple cafe branches',
                'message' => 'Please share how branch-level menus and reporting work.',
                'status' => 'contacted',
            ],
        ];

        foreach ($enquiries as $enquiry) {
            ContactEnquiry::updateOrCreate(
                ['email' => $enquiry['email'], 'subject' => $enquiry['subject']],
                $enquiry
            );
        }
    }
}
