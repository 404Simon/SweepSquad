<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cleaning_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('cleaning_items')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('cleaning_frequency_hours')->nullable();
            $table->integer('base_coin_reward')->default(0);
            $table->timestamp('last_cleaned_at')->nullable();
            $table->foreignId('last_cleaned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('group_id');
            $table->index('parent_id');
            $table->index('last_cleaned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_items');
    }
};
