<?php

namespace Database\Factories;

use App\Models\Brand;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected string $title;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function definition(): array
    {
        $this->title = fake()->text(random_int(5, 10));
        return [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
        ];
    }
}
