<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->string('sale_number', 30)->unique();
            $table->string('channel', 20)->comment('lookups.code where type=sale_channel');
            $table->decimal('total', 10, 2);
            $table->string('payment_method', 20)->comment('lookups.code where type=payment_method');
            $table->string('status', 20)->comment('lookups.code where type=sale_status');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('created_by');
            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
