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
            // slug uses a short generated suffix to avoid exhausting Faker's unique() pool
            'slug' => $this->generateSlug($name),
            'description' => $this->faker->paragraphs(rand(1,3), true),
            // price in cents, rounded to the nearest 50 for realistic pricing
            'price' => $this->roundedPrice($this->faker->numberBetween(500, 20000)),
            'stock' => max(0, (int) $this->faker->numberBetween(0, 100)),
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
     * Set an exact stock level.
     */
    public function withStock(int $quantity): self
    {
        return $this->state([
            'stock' => max(0, $quantity),
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
            $filename = sprintf('products/%s-%s.jpg', $baseName, $this->generateUniqueSuffix());

            return [
                'image_path' => $filename,
            ];
        });
    }

    /**
     * Set a price in the provided range (in cents). Price will be rounded to the nearest 50 cents.
     * Example: pricedBetween(1000, 5000) produces prices between $10.00 and $50.00.
     */
    public function pricedBetween(int $minCents = 500, int $maxCents = 20000): self
    {
        return $this->state(function () use ($minCents, $maxCents) {
            $price = $this->faker->numberBetween($minCents, $maxCents);

            return [
                'price' => $this->roundedPrice($price),
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
                $product->slug = $this->generateSlug($product->name);
            }

            // Ensure price is an integer and not negative
            $product->price = (int) ($product->price ?? 0);
            if ($product->price < 0) {
                $product->price = abs($product->price);
            }

            // Ensure stock is a non-negative integer
            $product->stock = max(0, (int) ($product->stock ?? 0));
        });
    }

    /**
     * Round price (in cents) to the nearest 50 cents to produce realistic pricing.
     */
    private function roundedPrice(int $cents): int
    {
        $step = 50; // 50 cents
        return (int) (round($cents / $step) * $step);
    }

    // maximum attempts to find a non-colliding slug in the database
    private $maxSlugAttempts = 10;

    /**
     * Generate a short unique suffix used for slugs and filenames.
     * Uses random_bytes when available and falls back to md5(uniqid()) if not.
     */
    private function generateUniqueSuffix(): string
    {
        try {
            return substr(bin2hex(random_bytes(4)), 0, 8);
        } catch (\Throwable $e) {
            return substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        }
    }

    /**
     * Build a slug from the product name and a short suffix. Attempts to avoid DB collisions
     * by checking existing Product slugs. Keeps attempts bounded to avoid infinite loops.
     */
    private function ensureUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';

        for ($i = 0; $i < $this->maxSlugAttempts; $i++) {
            $candidate = $base . '-' . $this->generateUniqueSuffix();

            try {
                if (! Product::where('slug', $candidate)->exists()) {
                    return $candidate;
                }
            } catch (\Throwable $e) {
                // swallow DB errors and continue; fallback will handle uniqueness
            }
        }

        // final fallback: include timestamp and short hash to guarantee uniqueness
        return $base . '-' . time() . '-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 6);
    }

    /**
     * Public slug builder used by the factory.
     */
    private function generateSlug(string $name): string
    {
        return $this->ensureUniqueSlug($name);
    }
}
