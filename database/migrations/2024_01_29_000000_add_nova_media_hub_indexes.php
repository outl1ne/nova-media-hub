<?php

use Outl1ne\NovaMediaHub\MediaHub;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(MediaHub::getTableName(), function (Blueprint $table) {
            $table->index('collection_name');
            $table->index('original_file_hash');
        });
    }

    public function down(): void
    {
        Schema::table(MediaHub::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['collection_name']);
            $table->dropIndex(['original_file_hash']);
        });
    }
};
