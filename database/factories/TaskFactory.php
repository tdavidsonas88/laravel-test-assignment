<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'type' => $this->faker->randomElement(['basic', 'advanced', 'expert']),
            'status' => $this->faker->randomElement(['todo', 'closed', 'hold']),
            'owner' => $this->faker->numberBetween(1, 5),
        ];
    }
}
