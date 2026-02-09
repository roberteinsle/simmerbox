<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grocery_list_id')->constrained('grocery_lists')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->boolean('is_checked')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->text('source_recipe_ids')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('grocery_list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grocery_items');
    }
};
