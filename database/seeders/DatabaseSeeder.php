<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        // Demonstration accounts only. Existing credentials are not overwritten.
        $admin = User::firstOrCreate(['email' => 'admin@example.test'], [
            'name' => 'Администратор', 'password' => Hash::make('Admin12345!'),
        ]);
        $admin->role = 'admin';
        $admin->save();
        $titles = ['Не запускается почтовый клиент', 'Принтер не печатает документы', 'Пропало подключение к интернету', 'Нужен доступ к учебной системе', 'Ошибка при установке программы', 'Не работает второй монитор'];
        $categories = Category::all();
        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate(['email' => "user{$i}@example.test"], [
                'name' => "Пользователь {$i}", 'password' => Hash::make('User12345!'),
            ]);
            if ($user->tickets()->withTrashed()->exists()) {
                continue;
            }
            foreach ($titles as $index => $title) {
                Ticket::factory()->create([
                    'user_id' => $user->id,
                    'category_id' => $categories[$index % $categories->count()]->id,
                    'title' => $title,
                    'description' => 'Проблема возникла сегодня утром. Перезапуск приложения и компьютера не помог. Прошу проверить настройки и помочь восстановить работу.',
                    'status' => array_keys(Ticket::STATUSES)[$index % count(Ticket::STATUSES)],
                    'priority' => array_keys(Ticket::PRIORITIES)[$index % 4],
                ]);
            }
        }
    }
}
