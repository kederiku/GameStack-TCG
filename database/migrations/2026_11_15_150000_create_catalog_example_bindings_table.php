<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_example_bindings', function (Blueprint $table): void {
            $table->string('manifest_key')->primary();
            $table->string('record_type');
            // This points to several tables. Keep the binding when its target is deleted.
            $table->unsignedBigInteger('record_id');
            $table->unique(['record_type', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_example_bindings');
    }
};
