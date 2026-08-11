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
            // slug includes a short unique suffix to avoid collisions when seeding many products
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraphs(rand(1,3), true),
            // price in cents
            'price' => $this->faker->numberBetween(500, 20000),
            'stock' => $this->faker->numberBetween(0, 100),
            'image_path' => null,
        ];
    }

    /**
     * Ensure the product has at least one item in stock.
     * Useful when generating "available" products for storefronts.
     */
    public function available(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'stock' => $this->faker->numberBetween(1, 100),
            ];
        });
    }

    /**
     * Create a product that is out of stock.
     */
    public function outOfStock(): self
    {
        return $this->state([
            'stock' => 0,
        ]);
    }

    /**
     * Attach a realistic image path for the product. This doesn't store any files —
     * it only sets a plausible path which you can use with your storage/seed logic.
     */
    public function withImage(): self
    {
        return $this->state(function (array $attributes) {
            $baseName = isset($attributes['name']) ? Str::slug($attributes['name']) : Str::slug($this->faker->word());
            $filename = sprintf('products/%s-%s.jpg', $baseName, $this->faker->unique()->numberBetween(1000, 9999));

            return [
                'image_path' => $filename,
            ];
        });
    }

    /**
     * Set a price in the provided range (in cents).
     * Example: pricedBetween(1000, 5000) produces prices between $10.00 and $50.00.
     */
    public function pricedBetween(int $minCents = 500, int $maxCents = 20000): self
    {
        return $this->state(function () use ($minCents, $maxCents) {
            return [
                'price' => $this->faker->numberBetween($minCents, $maxCents),
            ];
        });
    }

    /**
     * Configure factory callbacks to ensure certain invariants when making/creating models.
     * We use afterMaking to guarantee a slug exists (useful for factories created via make()).
     */
    public function configure()
    {
        return $this->afterMaking(function (Product $product) {
            if (empty($product->slug) && ! empty($product->name)) {
                $product->slug = Str::slug($product->name) . '-' . $this->faker->unique()->numberBetween(1000, 9999);
            }
        });
    }
}
