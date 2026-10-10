<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('jalali-events.database.connection');
        $table      = config('jalali-events.database.days_table', 'jalali_calendar_days');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('jalali_year');
            $table->unsignedTinyInteger('jalali_month');
            $table->unsignedTinyInteger('jalali_day');
            $table->string('jalali_date', 10)->unique();
            $table->date('gregorian_date')->unique();
            $table->unsignedTinyInteger('weekday');
        $table->boolean('is_holiday')->default(false)->index();
            $table->boolean('is_weekend')->default(false)->index();
            $table->string('source', 50)->default('time.ir');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['jalali_year', 'jalali_month']);
        });
    }

    public function down(): void
    {
        $connection = config('jalali-events.database.connection');
        $table      = config('jalali-events.database.days_table', 'jalali_calendar_days');

        Schema::connection($connection)->dropIfExists($table);
    }
};
