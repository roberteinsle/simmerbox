<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_groceries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('frequency_type', 30);
            $table->string('frequency_day', 20)->nullable();
            $table->integer('frequency_interval')->default(1);
            $table->string('raw_input', 500);
            $table->date('next_occurrence')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('household_id');
            $table->index('next_occurrence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_groceries');
    }
};
