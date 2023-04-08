<?php

namespace Database\Factories;

use App\Models\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        $file = File::inRandomOrder()->first();
        $validFrom = Carbon::yesterday()->subWeeks(rand(2, 8));
        $metadata = [
            "image" => ($file) ? $file->uuid : null,
            "valid_from" => $validFrom->toDateString(),
            "valid_to" => $validFrom->addWeeks(rand(1,10))->toDateString(),
        ];
        $metadata = json_encode($metadata);

        return [
            'title' => $title,
            'content' => $this->faker->paragraph(2),
            'metadata' => $metadata,
        ];
    }
}
