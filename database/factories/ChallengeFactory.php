<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Step;
use App\Models\Substep;

class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => function() {
                return User::factory()->create()->id;
            },
            'step_id' => function() {
                return Step::factory()->create()->id;
            },
            'substep_id' => function() {
                return Substep::factory()->create()->id;
            },
            'time' => 0,
            'clear_flg' => 0,
            'challenge_flg' => 0,
            'order' => 0,
        ];
    }
}
