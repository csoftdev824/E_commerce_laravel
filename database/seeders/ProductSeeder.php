<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Laptop', 'price' => 999.99, 'stock_quantity' => 15],
            ['name' => 'Mouse', 'price' => 29.99, 'stock_quantity' => 50],
            ['name' => 'Keyboard', 'price' => 79.99, 'stock_quantity' => 30],
            ['name' => 'Monitor', 'price' => 299.99, 'stock_quantity' => 20],
            ['name' => 'Headphones', 'price' => 149.99, 'stock_quantity' => 25],
            ['name' => 'Webcam', 'price' => 89.99, 'stock_quantity' => 8],
            ['name' => 'USB Cable', 'price' => 9.99, 'stock_quantity' => 100],
            ['name' => 'Laptop Stand', 'price' => 49.99, 'stock_quantity' => 12],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
