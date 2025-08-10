<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create default admin user
        User::factory()->create([
            'email' => 'admin@nyemil.com',
            'username' => 'Admin',
            'password' => Hash::make('admin123'),
        ]);

        // Create additional users
        User::factory(3)->create();

        // Create products
        $products = [
            [
                'name' => 'Keripik Pisang Original',
                'description' => 'Keripik pisang renyah dengan rasa original yang autentik',
                'price' => 15000,
                'image' => 'products/keripik-pisang-original.jpg',
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Keripik Pisang Balado',
                'description' => 'Keripik pisang dengan bumbu balado pedas yang menggugah selera',
                'price' => 17000,
                'image' => 'products/keripik-pisang-balado.jpg',
                'stock' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Keripik Singkong',
                'description' => 'Keripik singkong renyah dengan berbagai varian rasa',
                'price' => 12000,
                'image' => 'products/keripik-singkong.jpg',
                'stock' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Kacang Atom',
                'description' => 'Kacang goreng dengan bumbu atom yang pedas dan gurih',
                'price' => 10000,
                'image' => 'products/kacang-atom.jpg',
                'stock' => 70,
                'is_active' => true,
            ],
            [
                'name' => 'Rempeyek Kacang',
                'description' => 'Rempeyek kacang tanah yang renyah dan gurih',
                'price' => 8000,
                'image' => 'products/rempeyek-kacang.jpg',
                'stock' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Kerupuk Udang',
                'description' => 'Kerupuk udang segar dengan rasa laut yang autentik',
                'price' => 20000,
                'image' => 'products/kerupuk-udang.jpg',
                'stock' => 35,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create sample orders
        $orders = Order::factory(10)->create();

        // Create order items for each order
        foreach ($orders as $order) {
            $itemCount = rand(1, 3);
            $products = Product::inRandomOrder()->take($itemCount)->get();
            
            foreach ($products as $product) {
                $quantity = rand(1, 5);
                $total = $product->price * $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $total,
                ]);
            }
        }
    }
}
