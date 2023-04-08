<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        $author =  User::inRandomOrder()->first();
        $file = File::inRandomOrder()->first();
        $metadata = [
            "image" => ($file) ? $file->uuid : null,
            "author" => $author->first_name. ' '. $author->last_name
        ];
        $metadata = json_encode($metadata);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraph(4),
            'metadata' => $metadata,
        ];
    }
}
