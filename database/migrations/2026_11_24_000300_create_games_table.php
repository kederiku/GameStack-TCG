<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->jsonb('display_name')->default('{}');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->smallInteger('start_year')->nullable();
            $table->smallInteger('end_year')->nullable();
            $table->string('publisher')->nullable();
            $table->text('reference_description')->nullable();
            $table->jsonb('description')->default('{}');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
