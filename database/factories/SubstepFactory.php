<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Step;
use Illuminate\Support\Str;

class SubstepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::random(10),
            'content' => Str::random(100),
            'time_aim' => 15,
            'order' => 1,
            'user_id' => function() {
                return User::factory()->create()->id;
            },
            'step_id' => function() {
                return Step::factory()->create()->id;
            },
        ];
    }
}
