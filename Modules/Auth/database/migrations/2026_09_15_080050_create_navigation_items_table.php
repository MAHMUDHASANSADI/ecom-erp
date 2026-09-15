<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->nullOnDelete();
            $table->string('label', 100);
            $table->string('route_name', 150)->nullable();
            $table->string('icon', 100)->nullable()->comment('Font Awesome class, e.g. fas fa-tachometer-alt');
            $table->string('permission_required', 100)->nullable()->comment('Spatie permission name');
            $table->string('module', 50)->nullable()->comment('Module this item belongs to');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
