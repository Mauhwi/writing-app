<?php

namespace Database\Factories;

use App\Models\CommentMessage;
use App\Models\CommentThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentMessage>
 */
class CommentMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'thread_id' => CommentThread::factory(),
        'user_id' => User::factory(),
        'body' => $this->faker->paragraph(),
        ];
    }
}
