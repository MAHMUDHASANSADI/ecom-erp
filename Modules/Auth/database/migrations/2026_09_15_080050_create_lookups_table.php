<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookups', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50);
            $table->string('code', 50);
            $table->string('label', 100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'code']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookups');
    }
};
