<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;


class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        Storage::fake('local');
        $fileName = Str::random(12);
        $file = UploadedFile::fake()->image($fileName.'.jpg');
        Storage::putFileAs('public',$file,$fileName.'.jpg');

        return [
            'title' => Str::random(10),
            'category_main' => 1,
            'category_sub' => 1,
            'content' => Str::random(100),
            'time_aim' => 0,
            'step_number' => 0,
            'image_path' => $fileName.'.jpg',
            'user_id' => function() {
                return User::factory()->create()->id;
            },
        ];
    }
}
