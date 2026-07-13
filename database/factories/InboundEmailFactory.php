<?php

namespace Database\Factories;

use App\Models\InboundEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InboundEmail>
 */
class InboundEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_name' => fake()->name(),
            'from_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(6),
            'body' => fake()->paragraphs(2, true),
        ];
    }

    /**
     * Mark the email as already triaged.
     */
    public function triaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => fake()->randomElement(['billing', 'shipping', 'refunds', 'technical', 'other']),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'summary' => fake()->sentence(12),
            'triaged_at' => now(),
        ]);
    }
}
