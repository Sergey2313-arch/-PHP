<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['software' => 'Программное обеспечение', 'hardware' => 'Оборудование', 'network' => 'Сеть и интернет', 'accounts' => 'Учётные записи', 'other' => 'Другое'] as $slug => $name) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
