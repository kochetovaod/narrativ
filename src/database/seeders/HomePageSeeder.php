<?php

namespace Database\Seeders;

use App\Domains\Content\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HomePageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Главная',
                'layout' => 'default',
                'blocks' => [],
                'is_published' => false,
            ],
        );
    }
}
