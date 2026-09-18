<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_change')->comment('Positive = stock in, negative = stock out');
            $table->string('reason', 50)->comment('Value from lookups where type = stock_reason');
            $table->string('reference_type', 100)->nullable()->comment('e.g. Sale, ManualAdjustment');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of the referenced record');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — append-only, rows are never modified

            // Fast current-stock and history queries
            $table->index(['product_id', 'created_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
