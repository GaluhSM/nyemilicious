<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10000, 100000);
        $shippingFee = fake()->randomElement([0, 2000]);
        $total = $subtotal + $shippingFee;

        return [
            'order_code' => $this->generateOrderCode(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'delivery_type' => fake()->randomElement(['pickup', 'delivery']),
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'payment_status' => fake()->randomElement(['pending', 'paid', 'cancel']),
            'order_status' => fake()->randomElement(['pending', 'confirmed', 'in_progress', 'ready', 'shipped', 'completed']),
        ];
    }

    private function generateOrderCode()
    {
        $prefix = 'NYEMIL';
        $random8 = Str::random(8);
        $date = now()->format('d');
        $random3Letters = Str::random(3);
        $month = now()->format('m');
        $random2Numbers = rand(10, 99);
        $year = now()->format('Y');

        return strtoupper($prefix . '-' . $random8 . '-' . $date . $random3Letters . $month . $random2Numbers . $year);
    }
}
