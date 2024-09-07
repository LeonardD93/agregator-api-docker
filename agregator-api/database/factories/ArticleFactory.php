<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title'       => $this->faker->sentence,
            'author'      => $this->faker->name,
            'content'     => $this->faker->paragraph,
            'category'    => $this->faker->word,
            'published_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'url'         => $this->faker->url,
            'source_name' => $this->faker->company,
        ];
    }
}
