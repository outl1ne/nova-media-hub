<?php

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table(MediaHub::getTableName(), function (Blueprint $table) {
                $table->index('collection_name');
            });
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (str_contains($msg, 'already exists') || str_contains($msg, 'Duplicate key name')) {
                // Ignore as user has already added the same key
            } else {
                throw $e;
            }
        }

        try {
            Schema::table(MediaHub::getTableName(), function (Blueprint $table) {
                $table->index('original_file_hash');
            });
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (str_contains($msg, 'already exists') || str_contains($msg, 'Duplicate key name')) {
                // Ignore as user has already added the same key
            } else {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        Schema::table(MediaHub::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['collection_name']);
            $table->dropIndex(['original_file_hash']);
        });
    }
};
