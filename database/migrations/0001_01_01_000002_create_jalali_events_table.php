<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $connection  = config('jalali-events.database.connection');
        $daysTable   = config('jalali-events.database.days_table', 'jalali_calendar_days');
        $eventsTable = config('jalali-events.database.events_table', 'jalali_events');

        Schema::connection($connection)->create($eventsTable, function (Blueprint $table) use ($daysTable) {
            $table->id();
            $table->unsignedBigInteger('calendar_day_id');
            $table->string('title', 500);
            $table->string('category', 100)->nullable();
            $table->boolean('is_holiday')->default(false)->index();
            $table->timestamps();
            $table->foreign('calendar_day_id')
                ->references('id')
                ->on($daysTable)
                ->cascadeOnDelete();
            $table->index('calendar_day_id');
        });
    }

    public function down(): void
    {
        $connection = config('jalali-events.database.connection');
        $table      = config('jalali-events.database.events_table', 'jalali_events');

        Schema::connection($connection)->dropIfExists($table);
    }
};
