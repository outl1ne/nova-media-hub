<?php

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(MediaHub::getTableName(), function (Blueprint $table) {
            // Primary keys
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable()->unique();

            // Core data
            $table->string('collection_name');

            // File info
            $table->string('disk');
            $table->string('file_name');
            $table->unsignedBigInteger('size');
            $table->string('file_hash');
            $table->string('mime_type')->nullable();

            // Data
            $table->json('data');

            // Conversions
            $table->json('conversions');
            $table->string('conversions_disk')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(MediaHub::getTableName());
    }
};
