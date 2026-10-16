<?php

namespace Database\Seeders;

use App\Models\Bhajan;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BhajanSeeder extends Seeder
{
    public function run(): void
    {
        // remove the earlier sample data
        Bhajan::where('title', 'like', 'Sample Bhajan%')->delete();
        Category::whereIn('slug', ['krishna', 'shiva', 'devi', 'god-angel-bani'])->delete();

        $names = [
            'ISHOR MAHIMA YUKTA BHAJAN',
            'GURU MAHIMA YUKTA BHAJAN',
            'UTSAHIT BHAJAN',
        ];

        foreach ($names as $name) {
            $cat = Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);

            $b = Bhajan::firstOrCreate(
                ['title' => "Sample - $name"],
                [
                    'lyrics' => "Sample lyrics line one\nSample lyrics line two\n\nSample lyrics line three",
                    'status' => 'published',
                ]
            );
            $b->categories()->syncWithoutDetaching([$cat->id]);
        }
    }
}