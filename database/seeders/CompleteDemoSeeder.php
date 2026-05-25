<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CustomerProfile;
use App\Domains\Checkout\Models\Address;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Models\Payment;
use App\Domains\Order\Models\OrderStatusHistory;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Models\Coupon;
use App\Domains\Catalog\Models\Review;
use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Models\TicketMessage;
use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Customer\Enums\CustomerGroup;
use App\Domains\Customer\Enums\NewsletterStatus;
use App\Domains\Customer\Enums\UserStatus;
use App\Domains\Promotion\Enums\PromotionTarget;
use App\Domains\Promotion\Enums\PromotionType;
use App\Domains\Order\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class CompleteDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles and permissions
        $this->createRolesAndPermissions();

        // Create admin user
        $admin = $this->createAdminUser();

        // Create staff users
        $staffUsers = $this->createStaffUsers();

        // Create customers
        $customers = $this->createCustomers();

        // Create suspended customer
        $this->createSuspendedCustomer();

        // Create new customer
        $this->createNewCustomer();

        // Create categories
        $categories = $this->createCategories();

        // Create products
        $products = $this->createProducts($categories);

        // Create promotions
        $promotions = $this->createPromotions();

        // Create orders for some customers
        $this->createOrders($customers, $products, $promotions);

        // Create reviews
        $this->createReviews($products, $customers);

        // Create support tickets
        $this->createSupportTickets($customers);

        $this->command->info('✅ Complete demo seeding completed successfully!');
    }

    /**
     * Create roles and permissions
     */
    private function createRolesAndPermissions(): void
    {
        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Define permissions
        $permissions = [
            'products.view',
            'products.edit',
            'products.create',
            'products.delete',
            'orders.view',
            'orders.update_status',
            'customers.view',
            'customers.edit',
            'support.manage',
            'rma.manage',
            'promotions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->syncPermissions(Permission::all());

        $staffRole = Role::where('name', 'staff')->first();
        $staffRole->syncPermissions([
            'products.view',
            'products.edit',
            'orders.view',
            'orders.update_status',
            'customers.view',
            'support.manage',
            'rma.manage',
        ]);
    }

    /**
     * Create admin user
     */
    private function createAdminUser(): User
    {
        $user = User::firstOrCreate([
            'email' => 'glorygandigbe2@gmail.com',
        ], [
            'name' => 'Glory Gandigbe',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);

        $user->assignRole('admin');

        // Create customer profile for admin
        CustomerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'group' => CustomerGroup::STANDARD,
                'newsletter_status' => NewsletterStatus::SUBSCRIBED,
            ]
        );

        $this->command->info('✓ Admin user created: ' . $user->email);
        return $user;
    }

    /**
     * Create staff users
     */
    private function createStaffUsers(): array
    {
        $staffUsers = [];

        $staffData = [
            ['name' => 'Staff Alpha', 'email' => 'glorygandigbe2+staff1@gmail.com'],
            ['name' => 'Staff Beta', 'email' => 'glorygandigbe2+staff2@gmail.com'],
        ];

        foreach ($staffData as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email'],
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
            ]);

            $user->assignRole('staff');

            CustomerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'group' => CustomerGroup::STANDARD,
                    'newsletter_status' => NewsletterStatus::UNSUBSCRIBED,
                ]
            );

            $staffUsers[] = $user;
            $this->command->info('✓ Staff user created: ' . $user->email);
        }

        return $staffUsers;
    }

    /**
     * Create regular customers
     */
    private function createCustomers(): array
    {
        $customers = [];

        $customerData = [
            [
                'name' => 'Alice Dupont',
                'email' => 'glorygandigbe2+1@gmail.com',
                'group' => CustomerGroup::STANDARD,
                'loyalty_tier' => 'bronze',
                'loyalty_points' => 0,
            ],
            [
                'name' => 'Bob Martin',
                'email' => 'glorygandigbe2+2@gmail.com',
                'group' => CustomerGroup::STANDARD,
                'loyalty_tier' => 'silver',
                'loyalty_points' => 600,
            ],
            [
                'name' => 'Claire Bernard',
                'email' => 'glorygandigbe2+3@gmail.com',
                'group' => CustomerGroup::VIP,
                'loyalty_tier' => 'gold',
                'loyalty_points' => 2000,
            ],
            [
                'name' => 'David Petit',
                'email' => 'glorygandigbe2+4@gmail.com',
                'group' => CustomerGroup::STANDARD,
                'loyalty_tier' => 'bronze',
                'loyalty_points' => 0,
            ],
            [
                'name' => 'Emma Richard',
                'email' => 'glorygandigbe2+5@gmail.com',
                'group' => CustomerGroup::VIP,
                'loyalty_tier' => 'platinum',
                'loyalty_points' => 6000,
            ],
        ];

        foreach ($customerData as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email'],
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
            ]);

            $user->assignRole('customer');

            CustomerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'group' => $data['group'],
                    'newsletter_status' => NewsletterStatus::SUBSCRIBED,
                    'loyalty_points' => $data['loyalty_points'],
                ]
            );

            // Create loyalty account
            LoyaltyAccount::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'points_balance' => $data['loyalty_points'],
                    'total_earned' => $data['loyalty_points'],
                    'current_tier' => $data['loyalty_tier'],
                ]
            );

            // Create customer addresses
            for ($i = 1; $i <= rand(2, 3); $i++) {
                $parts = explode(' ', $data['name']);
                Address::firstOrCreate(
                    [
                        'addressable_type' => User::class,
                        'addressable_id' => $user->id,
                        'street_address' => fake()->streetAddress(),
                    ],
                    [
                        'type' => $i === 1 ? 'shipping' : ($i === 2 ? 'billing' : 'shipping'),
                        'first_name' => $parts[0] ?? $data['name'],
                        'last_name' => $parts[1] ?? 'Dupont',
                        'email' => $data['email'],
                        'phone' => fake()->phoneNumber(),
                        'city' => fake()->city(),
                        'postal_code' => fake()->postcode(),
                        'country' => 'CA',
                        'is_default' => $i === 1,
                    ]
                );
            }

            $customers[] = $user;
            $this->command->info('✓ Customer created: ' . $user->email);
        }

        return $customers;
    }

    /**
     * Create suspended customer
     */
    private function createSuspendedCustomer(): void
    {
        $user = User::firstOrCreate([
            'email' => 'glorygandigbe2+banned@gmail.com',
        ], [
            'name' => 'Suspended User',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => UserStatus::SUSPENDED,
        ]);

        $user->assignRole('customer');

        CustomerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'group' => CustomerGroup::STANDARD,
                'newsletter_status' => NewsletterStatus::UNSUBSCRIBED,
            ]
        );

        $this->command->info('✓ Suspended user created: ' . $user->email);
    }

    /**
     * Create new customer (no orders)
     */
    private function createNewCustomer(): User
    {
        $user = User::firstOrCreate([
            'email' => 'glorygandigbe2+new@gmail.com',
        ], [
            'name' => 'New Customer',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);

        $user->assignRole('customer');

        CustomerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'group' => CustomerGroup::STANDARD,
                'newsletter_status' => NewsletterStatus::SUBSCRIBED,
            ]
        );

        // Create one address
        $parts = explode(' ', $user->name);
        Address::firstOrCreate(
            ['addressable_type' => User::class, 'addressable_id' => $user->id, 'street_address' => fake()->streetAddress()],
            [
                'type' => 'shipping',
                'first_name' => $parts[0] ?? $user->name,
                'last_name' => $parts[1] ?? 'Customer',
                'email' => $user->email,
                'phone' => fake()->phoneNumber(),
                'city' => fake()->city(),
                'postal_code' => fake()->postcode(),
                'country' => 'CA',
                'is_default' => true,
            ]
        );

        $this->command->info('✓ New customer created: ' . $user->email);
        return $user;
    }

    /**
     * Create product categories
     */
    private function createCategories(): array
    {
        $categories = [];

        $categoryData = [
            [
                'name' => 'Électronique',
                'children' => ['Téléphones', 'Ordinateurs'],
            ],
            [
                'name' => 'Vêtements',
                'children' => ['Hommes', 'Femmes'],
            ],
            [
                'name' => 'Maison',
                'children' => ['Meubles', 'Décoration'],
            ],
            [
                'name' => 'Sport',
                'children' => ['Équipement', 'Accessoires'],
            ],
            [
                'name' => 'Livres',
                'children' => ['Fiction', 'Non-fiction'],
            ],
        ];

        foreach ($categoryData as $data) {
            $parent = Category::firstOrCreate(
                ['slug' => str()->slug($data['name'])],
                [
                    'name' => $data['name'],
                    'is_active' => true,
                    'description' => 'Catégorie: ' . $data['name'],
                ]
            );

            foreach ($data['children'] as $childName) {
                $child = Category::firstOrCreate(
                    ['slug' => str()->slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'description' => $childName . ' - sous-catégorie',
                    ]
                );
                $categories[] = $child;
            }

            $categories[] = $parent;
            $this->command->info('✓ Category created: ' . $data['name'] . ' with subcategories');
        }

        return $categories;
    }

    /**
     * Create products
     */
    private function createProducts(array $categories): array
    {
        $products = [];

        $productData = [
            ['name' => 'iPhone 15 Pro', 'price' => 129999, 'sku' => 'IP15P'],
            ['name' => 'Samsung Galaxy S24', 'price' => 109999, 'sku' => 'SGS24'],
            ['name' => 'MacBook Pro 14"', 'price' => 219999, 'sku' => 'MBP14'],
            ['name' => 'Dell XPS 13', 'price' => 149999, 'sku' => 'DELXPS'],
            ['name' => 'T-Shirt Coton', 'price' => 2999, 'sku' => 'TSHIRT'],
            ['name' => 'Jeans Premium', 'price' => 7999, 'sku' => 'JEANS'],
            ['name' => 'Canapé 3 places', 'price' => 89999, 'sku' => 'CANAPÉ'],
            ['name' => 'Table Basse', 'price' => 29999, 'sku' => 'TABLE'],
            ['name' => 'Raquette Tennis', 'price' => 4999, 'sku' => 'RACQUET'],
            ['name' => 'Ballon Football', 'price' => 1999, 'sku' => 'BALL'],
            ['name' => 'Clean Code - Robert Martin', 'price' => 3999, 'sku' => 'CC001'],
            ['name' => 'Design Patterns', 'price' => 4999, 'sku' => 'DP001'],
        ];

        foreach ($productData as $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $categories[array_rand($categories)]->id,
                    'name' => $data['name'],
                    'description' => 'Description détaillée pour ' . $data['name'],
                    'short_description' => 'Produit de qualité',
                    'price' => $data['price'],
                    'cost' => intval($data['price'] * 0.4),
                    'is_featured' => rand(0, 1) === 1,
                ]
            );

            // Attach to multiple categories (many-to-many)
            if ($product->wasRecentlyCreated) {
                $randomCategories = collect($categories)->random(rand(1, 2))->pluck('id')->toArray();
                $product->categories()->sync($randomCategories);
            }

            // Create variants
            for ($i = 1; $i <= rand(1, 3); $i++) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'sku' => $data['sku'] . '-V' . $i],
                    [
                        'name' => $product->name . ' (Variante ' . $i . ')',
                        'price' => $data['price'],
                        'cost' => intval($data['price'] * 0.6),
                        'stock' => rand(10, 100),
                        'reserved_stock' => rand(0, 10),
                        'weight' => rand(100, 5000),
                        'is_active' => true,
                    ]
                );
            }

            $products[] = $product;
            $this->command->info('✓ Product created: ' . $product->name);
        }

        return $products;
    }

    /**
     * Create promotions
     */
    private function createPromotions(): array
    {
        $promotions = [];

        $promotionData = [
            ['name' => 'Summer Sale', 'value' => 1500, 'type' => PromotionType::PERCENTAGE],
            ['name' => 'Winter Discount', 'value' => 2000, 'type' => PromotionType::FIXED_AMOUNT],
            ['name' => 'Flash Deal', 'value' => 1000, 'type' => PromotionType::PERCENTAGE],
        ];

        foreach ($promotionData as $data) {
            $promotion = Promotion::firstOrCreate(
                ['slug' => str()->slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => 'Promotion: ' . $data['name'],
                    'type' => $data['type']->value,
                    'target' => PromotionTarget::ORDER->value,
                    'value' => $data['value'],
                    'is_stackable' => rand(0, 1) === 1,
                    'is_active' => true,
                    'starts_at' => now()->subDays(30),
                    'ends_at' => now()->addDays(30),
                    'usage_limit' => rand(50, 200),
                    'usage_count' => rand(0, 20),
                    'priority' => rand(1, 5),
                ]
            );

            // Create coupons for this promotion
            for ($i = 1; $i <= 3; $i++) {
                Coupon::firstOrCreate(
                    ['code' => strtoupper($data['name']) . '-' . $i],
                    [
                        'promotion_id' => $promotion->id,
                        'usage_limit' => rand(10, 50),
                        'usage_count' => 0,
                        'valid_from' => now(),
                        'valid_until' => now()->addDays(rand(7, 30)),
                    ]
                );
            }

            $promotions[] = $promotion;
            $this->command->info('✓ Promotion created: ' . $promotion->name);
        }

        return $promotions;
    }

    /**
     * Create orders for customers
     */
    private function createOrders(array $customers, array $products, array $promotions): void
    {
        $orderCount = 0;

        // Skip the last customer (new customer with no orders)
        $customersWithOrders = array_slice($customers, 0, -1);

        foreach ($customersWithOrders as $customer) {
            $numberOfOrders = rand(1, 3);
            $shippingAddress = $customer->addresses()->where('type', 'shipping')->first();
            $billingAddress = $customer->addresses()->where('type', 'billing')->first() ?? $shippingAddress;

            for ($i = 0; $i < $numberOfOrders; $i++) {
                // Calculate order totals
                $numberOfItems = rand(1, 5);
                $subtotalCents = 0;
                $shippingCents = rand(500, 2000);
                $taxCents = 0;

                // Create order
                $order = Order::create([
                    'user_id' => $customer->id,
                    'order_number' => 'ORD-' . date('Ymd') . '-' . rand(1000, 9999),
                    'status' => OrderStatus::DELIVERED,
                    'subtotal_cents' => 0, // Will update after items
                    'shipping_cents' => $shippingCents,
                    'tax_cents' => 0,
                    'total_cents' => 0, // Will update after items
                    'shipping_address_id' => $shippingAddress?->id,
                    'billing_address_id' => $billingAddress?->id,
                    'notes' => 'Test order',
                    'delivered_at' => now()->subDays(rand(0, 30)),
                ]);

                // Add order items
                for ($j = 0; $j < $numberOfItems; $j++) {
                    $product = $products[array_rand($products)];
                    $variant = $product->variants->first();
                    $qty = rand(1, 3);
                    $price = $variant?->price ?? $product->price ?? 9999;
                    $itemTotal = $price * $qty;
                    $subtotalCents += $itemTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'sku' => $variant?->sku ?? $product->sku,
                        'name' => $product->name,
                        'qty' => $qty,
                        'price_cents' => $price,
                        'total_cents' => $itemTotal,
                    ]);
                }

                // Calculate tax (10% of subtotal)
                $taxCents = intval($subtotalCents * 0.10);
                $totalCents = $subtotalCents + $shippingCents + $taxCents;

                // Update order totals
                $order->update([
                    'subtotal_cents' => $subtotalCents,
                    'tax_cents' => $taxCents,
                    'total_cents' => $totalCents,
                ]);

                // Create payment
                Payment::create([
                    'payable_type' => 'App\Domains\Order\Models\Order',
                    'payable_id' => $order->id,
                    'method' => 'paypal',
                    'status' => 'completed',
                    'amount_cents' => $totalCents,
                    'currency' => 'EUR',
                    'reference' => 'REF-' . $order->order_number . '-' . rand(1000, 9999),
                    'metadata' => ['gateway' => 'paypal', 'test' => true],
                    'paid_at' => now()->subDays(rand(0, 30)),
                ]);

                // Create order status history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::PENDING_PAYMENT->value,
                    'to_status' => OrderStatus::DELIVERED->value,
                    'user_id' => $customer->id,
                    'reason' => 'Order completed',
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                $orderCount++;
            }

            $this->command->info('✓ Orders created for customer: ' . $customer->email);
        }

        $this->command->info('✓ Total orders created: ' . $orderCount);
    }

    /**
     * Create reviews
     */
    private function createReviews(array $products, array $customers): void
    {
        $reviewCount = 0;

        // Only create reviews for customers with orders (first 5 customers, not new or suspended)
        $customersWithOrders = array_slice($customers, 0, -2);

        foreach ($products as $product) {
            $numberOfReviews = rand(0, 3);

            for ($i = 0; $i < $numberOfReviews; $i++) {
                $customer = $customersWithOrders[array_rand($customersWithOrders)];
                $order = $customer->orders()->first();

                if ($order) {
                    Review::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'user_id' => $customer->id,
                            'order_id' => $order->id,
                        ],
                        [
                            'rating' => rand(3, 5),
                            'title' => fake()->sentence(),
                            'comment' => fake()->paragraph(),
                            'is_approved' => rand(0, 1) === 1,
                            'verified_purchase' => true,
                            'helpful_count' => rand(0, 10),
                        ]
                    );

                    $reviewCount++;
                }
            }
        }

        $this->command->info('✓ Total reviews created: ' . $reviewCount);
    }

    /**
     * Create support tickets
     */
    private function createSupportTickets(array $customers): void
    {
        $ticketCount = 0;

        foreach (array_slice($customers, 0, 3) as $customer) {
            $numberOfTickets = rand(0, 2);

            for ($i = 0; $i < $numberOfTickets; $i++) {
                Ticket::create([
                    'user_id' => $customer->id,
                    'assigned_to' => null,
                    'category' => fake()->randomElement(TicketCategory::cases()),
                    'priority' => fake()->randomElement(TicketPriority::cases()),
                    'status' => fake()->randomElement([TicketStatus::OPEN, TicketStatus::IN_PROGRESS, TicketStatus::RESOLVED]),
                    'subject' => fake()->sentence(),
                    'description' => fake()->paragraph(),
                ]);

                $ticketCount++;
            }
        }

        $this->command->info('✓ Total support tickets created: ' . $ticketCount);
    }
}
