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
        $cats = collect(['Krishna', 'Shiva', 'Devi', 'God Angel Bani'])
            ->map(fn ($n) => Category::firstOrCreate(
                ['slug' => Str::slug($n)],
                ['name' => $n]
            ));

        foreach (range(1, 5) as $i) {
            $b = Bhajan::firstOrCreate(
                ['title' => "Sample Bhajan $i"],
                ['lyrics' => 'Lyrics coming soon...', 'status' => 'published']
            );
            $b->categories()->syncWithoutDetaching([$cats->random()->id]);
        }
    }
}