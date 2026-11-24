<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 255);
            $table->string('name');
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX languages_code_unique ON languages (lower(code))');

        Schema::create('markets', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->jsonb('display_name')->default('{}');
            $table->timestamps();
        });

        Schema::create('catalogs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained()->restrictOnDelete();
            $table->foreignId('default_language_id')->index()->constrained('languages')->restrictOnDelete();
            $table->string('key');
            $table->string('name');
            $table->jsonb('display_name')->default('{}');
            $table->text('reference_description')->nullable();
            $table->jsonb('description')->default('{}');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['game_id', 'key']);
        });

        Schema::create('series', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->restrictOnDelete();
            $table->string('key');
            $table->string('name');
            $table->jsonb('display_name')->default('{}');
            $table->jsonb('description')->default('{}');
            $table->string('type');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['catalog_id', 'key']);
            $table->unique(['id', 'catalog_id']);
        });

        Schema::create('series_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
            $table->foreignId('language_id')->index()->constrained()->restrictOnDelete();
            $table->string('image');
            $table->timestamps();
            $table->unique(['series_id', 'language_id']);
        });

        Schema::create('expansions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->restrictOnDelete();
            $table->foreignId('series_id')->nullable()->index();
            $table->string('key');
            $table->string('name');
            $table->jsonb('display_name')->default('{}');
            $table->jsonb('description')->default('{}');
            $table->string('type');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['catalog_id', 'key']);
            $table->foreign(['series_id', 'catalog_id'])->references(['id', 'catalog_id'])->on('series')->restrictOnDelete();
        });

        Schema::create('expansion_editions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expansion_id')->constrained()->restrictOnDelete();
            $table->foreignId('language_id')->index()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('official_code')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->string('status');
            $table->timestamps();
            $table->unique(['expansion_id', 'language_id']);
        });

        Schema::create('expansion_releases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('edition_id')->constrained('expansion_editions')->cascadeOnDelete();
            $table->foreignId('market_id')->index()->constrained()->restrictOnDelete();
            $table->string('channel');
            $table->date('released_on')->nullable();
            $table->string('reference_announced_period')->nullable();
            $table->jsonb('announced_period')->default('{}');
            $table->string('status');
            $table->text('source_url')->nullable();
            $table->timestamps();
            $table->unique(['edition_id', 'market_id', 'channel']);
        });

        Schema::create('expansion_relations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_expansion_id')->constrained('expansions')->restrictOnDelete();
            $table->foreignId('target_expansion_id')->index()->constrained('expansions')->restrictOnDelete();
            $table->string('type');
            $table->text('notes');
            $table->text('source_url');
            $table->timestamps();
            $table->unique(['source_expansion_id', 'target_expansion_id', 'type']);
        });

        DB::statement("ALTER TABLE expansion_relations ADD CONSTRAINT expansion_relations_pair_check
            CHECK (source_expansion_id <> target_expansion_id
                AND (type <> 'related_to' OR source_expansion_id < target_expansion_id))");
    }

    public function down(): void
    {
        Schema::dropIfExists('expansion_relations');
        Schema::dropIfExists('expansion_releases');
        Schema::dropIfExists('expansion_editions');
        Schema::dropIfExists('expansions');
        Schema::dropIfExists('series_images');
        Schema::dropIfExists('series');
        Schema::dropIfExists('catalogs');
        Schema::dropIfExists('markets');
        Schema::dropIfExists('languages');
    }
};
