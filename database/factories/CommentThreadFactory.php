<?php

namespace Database\Factories;

use App\Models\CommentThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentThread>
 */
class CommentThreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chapter_id' => \App\Models\Chapter::factory(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
