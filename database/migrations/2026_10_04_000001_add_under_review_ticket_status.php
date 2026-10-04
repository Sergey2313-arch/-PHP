<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Расширяем допустимые значения, не удаляя таблицу и существующие заявки.
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['new', 'under_review', 'in_progress', 'resolved', 'closed'])->default('new')->change();
        });
    }

    public function down(): void
    {
        // При откате новый статус переводим в «Новая», иначе старый ENUM его отклонит.
        DB::table('tickets')->where('status', 'under_review')->update(['status' => 'new']);

        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new')->change();
        });
    }
};
