<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $name = $this->faker->words(rand(2,4), true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraphs(rand(1,3), true),
            'price' => $this->faker->numberBetween(500, 20000), // cents
            'stock' => $this->faker->numberBetween(0, 100),
            'image_path' => null,
        ];
    }
}
